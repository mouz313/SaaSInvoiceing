<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    /**
     * Handle an incoming request for the developer REST API.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $requiredAbility = null): Response
    {
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey) {
            $token = $request->bearerToken();

            if (! $token) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Missing API key. Provide via Bearer token in the Authorization header.',
                ], 401);
            }

            $apiKey = ApiKey::findToken($token);

            if (! $apiKey) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Invalid or expired API key.',
                ], 401);
            }

            $apiKey->touchLastUsed();

            // Bind user to request
            $request->setUserResolver(fn () => $apiKey->user);
            $request->attributes->set('api_key', $apiKey);
        }

        if ($requiredAbility && ! $apiKey->can($requiredAbility)) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => "This API key does not have the required ability: {$requiredAbility}",
            ], 403);
        }

        return $next($request);
    }
}
