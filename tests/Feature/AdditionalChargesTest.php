<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdditionalChargesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Store Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'invoice_credits' => 10,
        ]);

        $this->client = Client::create([
            'user_id' => $this->user->id,
            'name' => 'Acme Buyer',
            'email' => 'buyer@acme.com',
            'country' => 'United States',
        ]);
    }

    public function test_user_can_create_invoice_with_additional_charges(): void
    {
        $response = $this->actingAs($this->user)->post(route('invoices.store'), [
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-CHG-001',
            'invoice_date' => '2026-09-18',
            'due_date' => '2026-09-30',
            'status' => 'draft',
            'style' => 'minimalist',
            'currency' => 'USD',
            'tax_rate' => 10,
            'discount_rate' => 10,
            'items' => [
                ['description' => 'Item 1', 'quantity' => 1, 'unit_price' => 50.00],
                ['description' => 'Item 2', 'quantity' => 1, 'unit_price' => 50.00],
            ],
            'additional_charges' => [
                ['name' => 'Express Shipping', 'type' => 'fixed', 'value' => 15.00],
                ['name' => 'Service Fee', 'type' => 'percentage', 'value' => 5],
            ],
        ]);

        $response->assertRedirect(route('invoices.index'));

        $invoice = Invoice::where('invoice_number', 'INV-CHG-001')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(100.00, (float) $invoice->subtotal);
        $this->assertEquals(10.00, (float) $invoice->discount_amount);
        $this->assertEquals(9.00, (float) $invoice->tax_amount);
        // Additional charges: 15.00 + (90 * 0.05 = 4.50) = 19.50
        $this->assertEquals(19.50, (float) $invoice->additional_charges_total);
        // Total: 90 + 9 + 19.50 = 118.50
        $this->assertEquals(118.50, (float) $invoice->total);
        $this->assertCount(2, $invoice->additional_charges);
    }

    public function test_user_can_update_invoice_with_additional_charges(): void
    {
        $invoice = $this->user->invoices()->create([
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-CHG-002',
            'invoice_date' => '2026-09-18',
            'due_date' => '2026-09-30',
            'status' => 'draft',
            'style' => 'corporate',
            'currency' => 'USD',
            'subtotal' => 100.00,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'discount_rate' => 0,
            'discount_amount' => 0,
            'total' => 100.00,
        ]);

        $invoice->items()->create([
            'description' => 'Consulting',
            'quantity' => 1,
            'unit_price' => 100.00,
            'amount' => 100.00,
        ]);

        $response = $this->actingAs($this->user)->put(route('invoices.update', $invoice), [
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-CHG-002',
            'invoice_date' => '2026-09-18',
            'due_date' => '2026-09-30',
            'status' => 'sent',
            'style' => 'creative',
            'currency' => 'USD',
            'tax_rate' => 0,
            'discount_rate' => 0,
            'items' => [
                ['description' => 'Consulting', 'quantity' => 1, 'unit_price' => 100.00],
            ],
            'additional_charges' => [
                ['name' => 'Packaging Fee', 'type' => 'fixed', 'value' => 20.00],
            ],
        ]);

        $response->assertRedirect(route('invoices.show', $invoice));

        $invoice->refresh();
        $this->assertEquals(20.00, (float) $invoice->additional_charges_total);
        $this->assertEquals(120.00, (float) $invoice->total);
        $this->assertEquals('Packaging Fee', $invoice->additional_charges[0]['name']);
    }

    public function test_additional_charges_rendered_across_all_templates(): void
    {
        $charges = [
            ['name' => 'Provincial Tax', 'type' => 'fixed', 'value' => 12.00, 'amount' => 12.00],
        ];

        foreach (['minimalist', 'corporate', 'creative', 'grid'] as $style) {
            $invoice = $this->user->invoices()->create([
                'client_id' => $this->client->id,
                'invoice_number' => 'INV-TMPL-'.$style,
                'invoice_date' => '2026-09-18',
                'due_date' => '2026-09-30',
                'status' => 'sent',
                'style' => $style,
                'currency' => 'USD',
                'subtotal' => 100.00,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'discount_rate' => 0,
                'discount_amount' => 0,
                'additional_charges' => $charges,
                'additional_charges_total' => 12.00,
                'total' => 112.00,
            ]);

            $invoice->items()->create([
                'description' => 'Test Item',
                'quantity' => 1,
                'unit_price' => 100.00,
                'amount' => 100.00,
            ]);

            // Web View
            $webResponse = $this->actingAs($this->user)->get(route('invoices.show', $invoice));
            $webResponse->assertStatus(200);
            $webResponse->assertSee('Provincial Tax');

            // Public View
            $publicResponse = $this->get(route('invoices.public', $invoice->public_token));
            $publicResponse->assertStatus(200);
            $publicResponse->assertSee('Provincial Tax');

            // PDF Download
            $pdfResponse = $this->actingAs($this->user)->get(route('invoices.pdf', $invoice));
            $pdfResponse->assertStatus(200);
            $pdfResponse->assertHeader('content-type', 'application/pdf');
        }
    }
}
