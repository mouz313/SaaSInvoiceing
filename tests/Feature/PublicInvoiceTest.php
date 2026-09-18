<?php

namespace Tests\Feature;

use App\Mail\InvoiceSentMail;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Client $client;

    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Demo Agency',
            'company_name' => 'Demo Agency LLC',
            'email' => 'agency@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
            'invoice_credits' => 10,
        ]);

        $this->client = Client::create([
            'user_id' => $this->user->id,
            'name' => 'Acme Corp',
            'company_name' => 'Acme Industries',
            'email' => 'client@acme.com',
        ]);

        $this->invoice = Invoice::create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-2026-TEST',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'status' => 'draft',
            'style' => 'minimalist',
            'currency' => 'USD',
            'subtotal' => 1000.00,
            'total' => 1000.00,
        ]);
    }

    public function test_invoice_automatically_has_public_token(): void
    {
        $this->assertNotEmpty($this->invoice->public_token);
        $this->assertGreaterThan(20, strlen($this->invoice->public_token));
    }

    public function test_client_can_view_invoice_via_public_token(): void
    {
        $this->assertNull($this->invoice->viewed_at);

        $response = $this->get(route('invoices.public', $this->invoice->public_token));

        $response->assertStatus(200);
        $response->assertSee('INV-2026-TEST');
        $response->assertSee('Acme Corp');
        $response->assertSee('Demo Agency');

        $fresh = $this->invoice->fresh();
        $this->assertNotNull($fresh->viewed_at);
    }

    public function test_client_can_download_pdf_via_public_token(): void
    {
        $response = $this->get(route('invoices.public.pdf', $this->invoice->public_token));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_client_can_pay_invoice_online(): void
    {
        $this->assertFalse($this->invoice->isPaid());

        $response = $this->post(route('invoices.public.checkout', $this->invoice->public_token), [
            'simulate' => 1,
        ]);

        $response->assertRedirect(route('invoices.public.success', $this->invoice->public_token));

        $fresh = $this->invoice->fresh();
        $this->assertTrue($fresh->isPaid());
        $this->assertEquals('paid', $fresh->status);
        $this->assertNotNull($fresh->paid_at);
    }

    public function test_user_can_send_invoice_via_email(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->user)->post(route('invoices.send-email', $this->invoice), [
            'recipient_email' => 'client@acme.com',
            'custom_message' => 'Please find attached invoice for your review.',
            'attach_pdf' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        Mail::assertSent(InvoiceSentMail::class, function ($mail) {
            return $mail->hasTo('client@acme.com')
                && $mail->invoice->id === $this->invoice->id
                && $mail->customMessage === 'Please find attached invoice for your review.';
        });

        $fresh = $this->invoice->fresh();
        $this->assertEquals('sent', $fresh->status);
    }
}
