<?php

namespace App\Services;

use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookDispatcher
{
    /**
     * Dispatch an event payload to all subscribed webhooks for a user.
     *
     * @param  array<string, mixed>  $data
     */
    public static function dispatch(User $user, string $event, array $data): void
    {
        $webhooks = $user->webhooks()->where('is_active', true)->get();

        foreach ($webhooks as $webhook) {
            if (! $webhook->isSubscribedTo($event)) {
                continue;
            }

            static::send($webhook, $event, $data);
        }
    }

    /**
     * Send payload to a specific webhook endpoint and record delivery log.
     *
     * @param  array<string, mixed>  $data
     */
    public static function send(Webhook $webhook, string $event, array $data): WebhookLog
    {
        $payload = [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
        ];

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $signature = $webhook->signPayload($jsonPayload);

        $statusCode = null;
        $responseBody = null;
        $success = false;

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'InvoiceHub-Webhook-Agent/1.0',
                    'X-InvoiceHub-Signature' => 'sha256='.$signature,
                    'X-InvoiceHub-Event' => $event,
                ])
                ->withBody($jsonPayload, 'application/json')
                ->post($webhook->url);

            $statusCode = $response->status();
            $responseBody = substr($response->body(), 0, 1000);
            $success = $response->successful();
        } catch (\Throwable $e) {
            $statusCode = 0;
            $responseBody = 'Connection Exception: '.$e->getMessage();
            Log::warning('Webhook delivery failed', [
                'webhook_id' => $webhook->id,
                'url' => $webhook->url,
                'error' => $e->getMessage(),
            ]);
        }

        $webhook->updateQuietly(['last_triggered_at' => now()]);

        return WebhookLog::create([
            'webhook_id' => $webhook->id,
            'event' => $event,
            'payload' => $payload,
            'response_status' => $statusCode,
            'response_body' => $responseBody,
            'successful' => $success,
        ]);
    }
}
