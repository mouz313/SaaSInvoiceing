<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Webhook;
use App\Services\WebhookDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebhookSsrfProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_ssrf_internal_ips_and_loopback_are_rejected(): void
    {
        $user = User::factory()->create();

        $unsafeUrls = [
            'http://127.0.0.1/webhook',
            'http://localhost/webhook',
            'http://169.254.169.254/latest/meta-data/',
            'http://0.0.0.0:8080/hook',
            'http://10.0.0.5:9000/callback',
            'http://192.168.1.100/api',
        ];

        foreach ($unsafeUrls as $url) {
            $response = $this->actingAs($user)->post('/settings/webhooks', [
                'url' => $url,
                'events' => ['invoice.created'],
            ]);

            $response->assertSessionHasErrors('url');
        }

        $this->assertDatabaseCount('webhooks', 0);
    }

    public function test_dispatcher_blocks_internal_webhook_dispatch(): void
    {
        Http::fake();

        $user = User::factory()->create();
        $webhook = Webhook::create([
            'user_id' => $user->id,
            'url' => 'http://169.254.169.254/secret',
            'events' => ['invoice.created'],
            'secret' => 'whsec_test',
            'is_active' => true,
        ]);

        $log = WebhookDispatcher::send($webhook, 'invoice.created', ['test' => true]);

        $this->assertFalse($log->successful);
        $this->assertEquals(400, $log->response_status);
        $this->assertStringContainsString('Blocked', $log->response_body);

        Http::assertNothingSent();
    }
}
