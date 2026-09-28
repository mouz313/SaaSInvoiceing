<?php

namespace Tests\Feature;

use App\Jobs\SyncInvoiceWithFbrJob;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Services\Fbr\FbrApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FbrIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_merchant_can_configure_fbr_pos_settings(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('profile.fbr'), [
            'fbr_enabled' => '1',
            'fbr_environment' => 'sandbox',
            'fbr_pos_id' => '102938',
            'fbr_pos_usin' => 'POS-DEV-01',
            'fbr_bearer_token' => 'fbr-secret-token-12345',
        ]);

        $response->assertRedirect(route('profile.edit', ['tab' => 'fbr']));
        $user->refresh();

        $this->assertTrue($user->fbr_enabled);
        $this->assertSame('sandbox', $user->fbr_environment);
        $this->assertSame('102938', $user->fbr_pos_id);
        $this->assertSame('POS-DEV-01', $user->fbr_pos_usin);
        $this->assertSame('fbr-secret-token-12345', $user->fbr_bearer_token);
    }

    public function test_fbr_service_builds_compliant_payload_and_simulation(): void
    {
        $user = User::factory()->create([
            'fbr_enabled' => true,
            'fbr_environment' => 'sandbox',
            'fbr_pos_id' => '102938',
            'fbr_pos_usin' => 'POS-MAIN',
        ]);

        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Acme Corp',
            'ntn' => '1234567-8',
            'cnic' => '3520112345671',
        ]);

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-2026-0001',
            'invoice_date' => now(),
            'due_date' => now()->addDays(7),
            'status' => 'sent',
            'currency' => 'PKR',
            'subtotal' => 1000.00,
            'tax_rate' => 18.00,
            'tax_amount' => 180.00,
            'total' => 1180.00,
            'pct_code' => '9801.0000',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Web Architecture Consulting',
            'quantity' => 1,
            'unit_price' => 1000.00,
            'amount' => 1000.00,
        ]);

        $service = new FbrApiService;
        $payload = $service->buildPayload($invoice, $user);

        $this->assertSame('INV-2026-0001', $payload['InvoiceNumber']);
        $this->assertSame(102938, $payload['POSID']);
        $this->assertSame('1234567-8', $payload['BuyerNTN']);
        $this->assertSame(1000.0, $payload['TotalSaleValue']);
        $this->assertSame(1180.0, $payload['TotalBillAmount']);

        $syncResult = $service->syncInvoice($invoice);
        $this->assertTrue($syncResult['success']);
        $this->assertNotEmpty($syncResult['fbr_invoice_number']);
        $this->assertStringStartsWith('FBR:', $syncResult['qr_data']);
    }

    public function test_merchant_can_manually_sync_invoice_with_fbr(): void
    {
        $user = User::factory()->create([
            'fbr_enabled' => true,
            'fbr_environment' => 'sandbox',
            'fbr_pos_id' => '102938',
        ]);

        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Apex Logistics',
        ]);

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-2026-0002',
            'invoice_date' => now(),
            'due_date' => now()->addDays(7),
            'status' => 'sent',
            'currency' => 'PKR',
            'subtotal' => 5000.00,
            'tax_rate' => 18.00,
            'tax_amount' => 900.00,
            'total' => 5900.00,
        ]);

        $response = $this->actingAs($user)->post(route('invoices.fbr-sync', $invoice));

        $response->assertSessionHas('success');
        $invoice->refresh();

        $this->assertSame('synced', $invoice->fbr_status);
        $this->assertNotNull($invoice->fbr_invoice_number);
        $this->assertNotNull($invoice->fbr_qr_code_data);
        $this->assertNotNull($invoice->fbr_synced_at);
    }

    public function test_sync_invoice_job_fiscalizes_invoice(): void
    {
        $user = User::factory()->create([
            'fbr_enabled' => true,
            'fbr_environment' => 'sandbox',
            'fbr_pos_id' => '998877',
        ]);

        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Global Traders',
        ]);

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-2026-0003',
            'invoice_date' => now(),
            'due_date' => now()->addDays(5),
            'status' => 'sent',
            'currency' => 'PKR',
            'subtotal' => 2000.00,
            'tax_rate' => 18.00,
            'tax_amount' => 360.00,
            'total' => 2360.00,
        ]);

        $job = new SyncInvoiceWithFbrJob($invoice);
        $job->handle(new FbrApiService);

        $invoice->refresh();
        $this->assertSame('synced', $invoice->fbr_status);
        $this->assertTrue($invoice->isFbrSynced());
    }
}
