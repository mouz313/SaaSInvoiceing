<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentProofPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_proof_is_stored_on_private_disk_and_authorized(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $owner = User::factory()->create();
        $client = Client::create([
            'user_id' => $owner->id,
            'name' => 'Acme Client',
            'country' => 'Pakistan',
        ]);

        $invoice = Invoice::create([
            'user_id' => $owner->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-PRIV-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'currency' => 'PKR',
            'subtotal' => 5000,
            'total' => 5000,
            'balance_due' => 5000,
            'amount_paid' => 0,
            'status' => 'sent',
            'public_token' => Str::random(32),
        ]);

        $fakeFile = UploadedFile::fake()->create('bank_receipt.pdf', 500, 'application/pdf');

        // Submit proof via public route
        $response = $this->post(route('invoices.public.proof', $invoice->public_token), [
            'payment_method' => 'bank_transfer',
            'reference_number' => 'TRX-998877',
            'amount' => 5000,
            'proof_file' => $fakeFile,
        ]);

        $response->assertSessionHas('success');

        $payment = InvoicePayment::where('invoice_id', $invoice->id)->first();
        $this->assertNotNull($payment);
        $this->assertNotNull($payment->proof_file);

        // Verify stored on private disk, NOT public disk
        Storage::disk('local')->assertExists($payment->proof_file);
        Storage::disk('public')->assertMissing($payment->proof_file);

        // 1. Unauthenticated request to proof download should redirect to login
        $this->get(route('invoices.payments.proof', [$invoice, $payment]))
            ->assertRedirect('/login');

        // 2. Unauthorized stranger gets 403
        $stranger = User::factory()->create();
        $this->actingAs($stranger)
            ->get(route('invoices.payments.proof', [$invoice, $payment]))
            ->assertStatus(403);

        // 3. Authorized owner gets 200
        $this->actingAs($owner)
            ->get(route('invoices.payments.proof', [$invoice, $payment]))
            ->assertStatus(200);
    }
}
