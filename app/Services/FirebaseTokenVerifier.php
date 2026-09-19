<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseTokenVerifier
{
    /**
     * Optional fake callback for automated testing.
     *
     * @var (callable(string): ?array)|null
     */
    protected static $fakeHandler = null;

    /**
     * Fake the verifier response for testing.
     *
     * @param  (callable(string): ?array)|array|null  $handlerOrResult
     */
    public static function fake(mixed $handlerOrResult = null): void
    {
        if (is_callable($handlerOrResult)) {
            static::$fakeHandler = $handlerOrResult;
        } elseif (is_array($handlerOrResult)) {
            static::$fakeHandler = fn () => $handlerOrResult;
        } elseif ($handlerOrResult === null) {
            static::$fakeHandler = fn () => null; // Simulate invalid/unverified token
        }
    }

    /**
     * Reset any test fakes.
     */
    public static function resetFake(): void
    {
        static::$fakeHandler = null;
    }

    /**
     * Verify a Firebase ID token and return verified user payload, or null if invalid.
     *
     * @return array{uid: string, email: string, name: ?string, avatar_url: ?string}|null
     */
    public function verify(string $idToken): ?array
    {
        if (static::$fakeHandler !== null) {
            return (static::$fakeHandler)($idToken);
        }

        if (empty(trim($idToken))) {
            return null;
        }

        // Basic structural JWT sanity check
        $parts = explode('.', $idToken);
        if (count($parts) !== 3) {
            return null;
        }

        // Pre-check expiration from JWT payload if decodable
        $payloadJson = base64_decode(strtr($parts[1], '-_', '+/'), true);
        if ($payloadJson) {
            $payload = json_decode($payloadJson, true);
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                Log::warning('Firebase ID token is expired.');

                return null;
            }
        }

        $apiKey = setting('firebase_api_key') ?: config('services.firebase.api_key');

        if (empty($apiKey) || str_starts_with($apiKey, 'placeholder')) {
            Log::warning('Firebase Web API Key not configured; unable to verify ID token against Google Identity Toolkit.');

            return null;
        }

        try {
            $response = Http::timeout(8)->post(
                "https://identitytoolkit.googleapis.com/v1/accounts:lookup?key={$apiKey}",
                ['idToken' => $idToken]
            );

            if (! $response->successful()) {
                Log::warning('Firebase token verification rejected by Google API: '.$response->body());

                return null;
            }

            $users = $response->json('users');
            if (empty($users) || ! isset($users[0]['localId'], $users[0]['email'])) {
                return null;
            }

            $user = $users[0];

            return [
                'uid' => $user['localId'],
                'email' => $user['email'],
                'name' => $user['displayName'] ?? null,
                'avatar_url' => $user['photoUrl'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::error('Firebase token verification exception: '.$e->getMessage());

            return null;
        }
    }
}
