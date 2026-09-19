<?php

namespace Tests\Feature;

use App\Mail\InvoiceSentMail;
use App\Mail\PaymentReceiptMail;
use App\Mail\WelcomeUserMail;
use App\Models\Client;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\CmsAndSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PhaseATest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CmsAndSettingsSeeder::class);

        $this->user = User::create([
            'name' => 'John Entrepreneur',
            'email' => 'john@entrepreneur.test',
            'password' => Hash::make('secret123'),
            'role' => 'user',
            'invoice_credits' => 5,
        ]);

        $this->client = Client::create([
            'user_id' => $this->user->id,
            'name' => 'Acme Corporation',
            'email' => 'billing@acmecorp.test',
            'phone' => '+15551234567',
            'company_name' => 'Acme Corp',
        ]);
    }

    public function test_welcome_email_is_sent_on_registration(): void
    {
        Mail::fake();

        $response = $this->post('/register', [
            'name' => 'Alice Founder',
            'email' => 'alice@founder.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard'));

        Mail::assertSent(WelcomeUserMail::class, function ($mail) {
            return $mail->hasTo('alice@founder.test') && $mail->user->invoice_credits === 5;
        });
    }

    public function test_user_can_create_and_view_estimate(): void
    {
        $response = $this->actingAs($this->user)->post('/estimates', [
            'client_id' => $this->client->id,
            'estimate_number' => 'EST-2026-0001',
            'estimate_date' => now()->toDateString(),
            'expiry_date' => now()->addDays(14)->toDateString(),
            'status' => 'draft',
            'style' => 'minimalist',
            'currency' => 'USD',
            'tax_rate' => 10,
            'discount_rate' => 5,
            'items' => [
                [
                    'description' => 'Brand Identity & Logo Design',
                    'quantity' => 1,
                    'unit_price' => 1000.00,
                ],
            ],
            'additional_charges' => [
                [
                    'name' => 'Expedited Fee',
                    'type' => 'fixed',
                    'value' => 150,
                ],
            ],
        ]);

        $response->assertRedirect(route('estimates.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('estimates', [
            'user_id' => $this->user->id,
            'estimate_number' => 'EST-2026-0001',
            'client_id' => $this->client->id,
            'status' => 'draft',
        ]);

        $estimate = Estimate::where('estimate_number', 'EST-2026-0001')->first();
        $this->assertNotNull($estimate);
        $this->assertEquals(1, $estimate->items()->count());

        // subtotal 1000 - 50 (5% discount) = 950 + 95 (10% tax) + 150 (fee) = 1195
        $this->assertEquals(1195.00, (float) $estimate->total);

        // View show page
        $showResponse = $this->actingAs($this->user)->get("/estimates/{$estimate->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee('EST-2026-0001');
        $showResponse->assertSee('Brand Identity & Logo Design');
    }

    public function test_client_can_view_and_accept_estimate_publicly(): void
    {
        $estimate = Estimate::create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'estimate_number' => 'EST-2026-0002',
            'estimate_date' => now()->toDateString(),
            'expiry_date' => now()->addDays(7)->toDateString(),
            'status' => 'sent',
            'style' => 'minimalist',
            'currency' => 'USD',
            'subtotal' => 500,
            'total' => 500,
        ]);

        $estimate->items()->create([
            'description' => 'SEO Consultation',
            'quantity' => 1,
            'unit_price' => 500,
            'amount' => 500,
        ]);

        // Client views public estimate
        $response = $this->get("/estimate/{$estimate->public_token}");
        $response->assertStatus(200);
        $response->assertSee('SEO Consultation');
        $response->assertSee('Accept Proposal');

        $estimate->refresh();
        $this->assertNotNull($estimate->viewed_at);

        // Client accepts proposal
        $acceptResponse = $this->post("/estimate/{$estimate->public_token}/accept");
        $acceptResponse->assertSessionHas('success');

        $estimate->refresh();
        $this->assertEquals('accepted', $estimate->status);
        $this->assertNotNull($estimate->accepted_at);
    }

    public function test_client_can_decline_estimate_publicly(): void
    {
        $estimate = Estimate::create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'estimate_number' => 'EST-2026-0003',
            'estimate_date' => now()->toDateString(),
            'expiry_date' => now()->addDays(7)->toDateString(),
            'status' => 'sent',
            'style' => 'minimalist',
            'currency' => 'USD',
            'subtotal' => 300,
            'total' => 300,
        ]);

        $response = $this->post("/estimate/{$estimate->public_token}/decline", [
            'reason' => 'Budget constraints this quarter',
        ]);

        $response->assertSessionHas('info');

        $estimate->refresh();
        $this->assertEquals('declined', $estimate->status);
        $this->assertEquals('Budget constraints this quarter', $estimate->decline_reason);
    }

    public function test_user_can_convert_estimate_to_invoice(): void
    {
        $estimate = Estimate::create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'estimate_number' => 'EST-2026-0004',
            'estimate_date' => now()->toDateString(),
            'expiry_date' => now()->addDays(7)->toDateString(),
            'status' => 'accepted',
            'style' => 'minimalist',
            'currency' => 'USD',
            'subtotal' => 800,
            'total' => 800,
        ]);

        $estimate->items()->create([
            'description' => 'Mobile App Prototyping',
            'quantity' => 2,
            'unit_price' => 400,
            'amount' => 800,
        ]);

        $initialCredits = $this->user->invoice_credits;

        $response = $this->actingAs($this->user)->post("/estimates/{$estimate->id}/convert");

        $estimate->refresh();
        $this->assertEquals('invoiced', $estimate->status);
        $this->assertNotNull($estimate->converted_invoice_id);

        $invoice = Invoice::find($estimate->converted_invoice_id);
        $this->assertNotNull($invoice);
        $this->assertEquals(800, (float) $invoice->total);
        $this->assertEquals(1, $invoice->items()->count());
        $this->assertEquals('Mobile App Prototyping', $invoice->items->first()->description);

        $this->user->refresh();
        $this->assertEquals($initialCredits - 1, $this->user->invoice_credits);

        $response->assertRedirect(route('invoices.show', $invoice));
    }

    public function test_invoice_creation_with_direct_email_send(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->user)->post('/invoices', [
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-DIRECT-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'status' => 'draft',
            'style' => 'minimalist',
            'currency' => 'USD',
            'tax_rate' => 0,
            'discount_rate' => 0,
            'send_email_now' => '1',
            'items' => [
                [
                    'description' => 'Cloud Architecture Audit',
                    'quantity' => 1,
                    'unit_price' => 1500.00,
                ],
            ],
        ]);

        $response->assertRedirect(route('invoices.index'));

        Mail::assertSent(InvoiceSentMail::class, function ($mail) {
            return $mail->hasTo('billing@acmecorp.test') && $mail->invoice->invoice_number === 'INV-DIRECT-001';
        });

        $invoice = Invoice::where('invoice_number', 'INV-DIRECT-001')->first();
        $this->assertEquals('sent', $invoice->status);
    }

    public function test_payment_receipt_email_sent_when_marked_as_paid(): void
    {
        Mail::fake();

        $invoice = Invoice::create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-PAID-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'sent',
            'style' => 'minimalist',
            'currency' => 'USD',
            'subtotal' => 600,
            'total' => 600,
        ]);

        $invoice->items()->create([
            'description' => 'API Integration',
            'quantity' => 1,
            'unit_price' => 600,
            'amount' => 600,
        ]);

        $response = $this->actingAs($this->user)->patch("/invoices/{$invoice->id}/status", [
            'status' => 'paid',
        ]);

        $response->assertSessionHas('success');

        Mail::assertSent(PaymentReceiptMail::class, function ($mail) {
            return $mail->hasTo('billing@acmecorp.test') && $mail->invoice->invoice_number === 'INV-PAID-001';
        });
    }
}
