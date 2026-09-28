<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TeamRolesEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_role_is_strictly_read_only(): void
    {
        $owner = User::factory()->create(['invoice_credits' => 10]);
        $viewer = User::factory()->create();

        TeamMember::create([
            'owner_id' => $owner->id,
            'user_id' => $viewer->id,
            'role' => 'viewer',
            'status' => 'active',
        ]);

        $client = Client::create([
            'user_id' => $owner->id,
            'name' => 'Team Client',
            'country' => 'Pakistan',
        ]);

        $invoice = Invoice::create([
            'user_id' => $owner->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-TEAM-001',
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

        // Switch to owner's workspace
        $this->actingAs($viewer)->post(route('teams.switch-account'), [
            'owner_id' => $owner->id,
        ])->assertSessionHas('success');

        // 1. Viewer can view invoice list and details
        $this->actingAs($viewer)->get('/invoices')->assertStatus(200);
        $this->actingAs($viewer)->get("/invoices/{$invoice->id}")->assertStatus(200);

        // 2. Viewer cannot access invoice create page
        $this->actingAs($viewer)->get('/invoices/create')->assertStatus(403);

        // 3. Viewer cannot store invoice
        $this->actingAs($viewer)->post('/invoices', [
            'client_id' => $client->id,
            'invoice_number' => 'INV-NEW-999',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'sent',
            'style' => 'minimalist',
            'currency' => 'PKR',
            'items' => [
                ['description' => 'Test', 'quantity' => 1, 'unit_price' => 100],
            ],
        ])->assertStatus(403);

        // 4. Viewer cannot update invoice
        $this->actingAs($viewer)->put("/invoices/{$invoice->id}", [
            'client_id' => $client->id,
            'invoice_number' => 'INV-MOD-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'sent',
            'style' => 'minimalist',
            'currency' => 'PKR',
            'items' => [
                ['description' => 'Test', 'quantity' => 1, 'unit_price' => 200],
            ],
        ])->assertStatus(403);

        // 5. Viewer cannot delete invoice
        $this->actingAs($viewer)->delete("/invoices/{$invoice->id}")->assertStatus(403);

        // 6. Viewer cannot record payment
        $this->actingAs($viewer)->post("/invoices/{$invoice->id}/payments", [
            'amount' => 500,
            'payment_method' => 'bank_transfer',
            'paid_at' => now()->toDateString(),
        ])->assertStatus(403);
    }

    public function test_accountant_can_create_invoice_and_record_payment(): void
    {
        $owner = User::factory()->create(['invoice_credits' => 10]);
        $accountant = User::factory()->create();

        TeamMember::create([
            'owner_id' => $owner->id,
            'user_id' => $accountant->id,
            'role' => 'accountant',
            'status' => 'active',
        ]);

        $client = Client::create([
            'user_id' => $owner->id,
            'name' => 'Acme Corp',
            'country' => 'Pakistan',
        ]);

        // Switch to owner's workspace
        $this->actingAs($accountant)->post(route('teams.switch-account'), [
            'owner_id' => $owner->id,
        ])->assertSessionHas('success');

        // Accountant can create invoice
        $response = $this->actingAs($accountant)->post('/invoices', [
            'client_id' => $client->id,
            'invoice_number' => 'INV-ACC-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'sent',
            'style' => 'minimalist',
            'currency' => 'PKR',
            'items' => [
                ['description' => 'Accounting Service', 'quantity' => 2, 'unit_price' => 500],
            ],
        ]);

        $response->assertRedirect('/invoices');

        $createdInvoice = Invoice::where('invoice_number', 'INV-ACC-001')->first();
        $this->assertNotNull($createdInvoice);
        $this->assertEquals($owner->id, $createdInvoice->user_id);

        // Accountant can record payment
        $paymentResponse = $this->actingAs($accountant)->post("/invoices/{$createdInvoice->id}/payments", [
            'amount' => 1000,
            'payment_method' => 'bank_transfer',
            'reference_number' => 'ACC-TRX-101',
            'paid_at' => now()->toDateString(),
        ]);

        $paymentResponse->assertSessionHas('success');
        $this->assertEquals(1000, $createdInvoice->fresh()->amount_paid);
    }
}
