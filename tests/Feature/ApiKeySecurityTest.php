<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiKeySecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_query_parameter_api_key_is_rejected_and_bearer_is_required(): void
    {
        $user = User::factory()->create();
        ['key' => $plainToken, 'model' => $apiKey] = ApiKey::generate($user, 'Test Key', ['*']);

        // Query parameter should be rejected with 401
        $response = $this->getJson("/api/v1/invoices?api_key={$plainToken}");
        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized',
                'message' => 'Missing API key. Provide via Bearer token in the Authorization header.',
            ]);

        // Bearer header should succeed
        $response = $this->withHeader('Authorization', "Bearer {$plainToken}")
            ->getJson('/api/v1/invoices');

        $response->assertStatus(200);
    }

    public function test_api_key_abilities_are_enforced_strictly(): void
    {
        $user = User::factory()->create();

        // Read-only API key
        ['key' => $readOnlyToken, 'model' => $readOnlyKey] = ApiKey::generate($user, 'Read Only Key', ['invoices:read']);

        // GET should succeed
        $response = $this->withHeader('Authorization', "Bearer {$readOnlyToken}")
            ->getJson('/api/v1/invoices');
        $response->assertStatus(200);

        // POST should be rejected with 403
        $response = $this->withHeader('Authorization', "Bearer {$readOnlyToken}")
            ->postJson('/api/v1/invoices', [
                'client_name' => 'Acme Corp',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
                'items' => [
                    ['description' => 'Test item', 'quantity' => 1, 'unit_price' => 100],
                ],
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Forbidden',
                'message' => 'This API key does not have the required ability: invoices:write',
            ]);
    }

    public function test_api_key_cannot_read_another_users_invoice(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        ['key' => $tokenA, 'model' => $keyA] = ApiKey::generate($userA, 'User A Key', ['invoices:read']);

        $clientB = Client::create([
            'user_id' => $userB->id,
            'name' => 'Client B',
            'country' => 'Pakistan',
        ]);

        $invoiceB = Invoice::create([
            'user_id' => $userB->id,
            'client_id' => $clientB->id,
            'invoice_number' => 'INV-TEST-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'currency' => 'PKR',
            'subtotal' => 1000,
            'total' => 1000,
            'balance_due' => 1000,
            'amount_paid' => 0,
            'status' => 'sent',
            'public_token' => Str::random(32),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$tokenA}")
            ->getJson("/api/v1/invoices/{$invoiceB->id}");

        $response->assertStatus(404);
    }
}
