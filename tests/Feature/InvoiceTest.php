<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Freelance Designer',
            'email' => 'designer@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'invoice_credits' => 10,
        ]);

        $this->client = Client::create([
            'user_id' => $this->user->id,
            'name' => 'Acme Corp',
            'email' => 'billing@acme.com',
            'country' => 'United States',
        ]);
    }

    public function test_user_can_view_invoices_index(): void
    {
        $response = $this->actingAs($this->user)->get('/invoices');
        $response->assertStatus(200);
        $response->assertSee('Invoices Registry');
    }

    public function test_user_can_create_invoice_with_line_items_and_style(): void
    {
        $response = $this->actingAs($this->user)->post('/invoices', [
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-2026-TEST',
            'invoice_date' => '2026-09-15',
            'due_date' => '2026-09-29',
            'status' => 'sent',
            'style' => 'corporate',
            'currency' => 'USD',
            'tax_rate' => 10,
            'discount_rate' => 5,
            'notes' => 'Thank you for your business',
            'items' => [
                ['description' => 'Web App UI Design', 'quantity' => 2, 'unit_price' => 500.00],
                ['description' => 'API Integration', 'quantity' => 1, 'unit_price' => 800.00],
            ],
        ]);

        $response->assertRedirect(route('invoices.index'));

        $invoice = Invoice::where('invoice_number', 'INV-2026-TEST')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('corporate', $invoice->style);
        $this->assertEquals(2, $invoice->items()->count());

        // Subtotal = (2*500) + (1*800) = 1800
        // Discount 5% = 90 -> Taxable = 1710
        // Tax 10% = 171 -> Total = 1881.00
        $this->assertEquals(1800.00, (float) $invoice->subtotal);
        $this->assertEquals(90.00, (float) $invoice->discount_amount);
        $this->assertEquals(171.00, (float) $invoice->tax_amount);
        $this->assertEquals(1881.00, (float) $invoice->total);

        // Credits decremented from 10 to 9
        $this->assertEquals(9, $this->user->fresh()->invoice_credits);
    }

    public function test_user_can_update_invoice_style(): void
    {
        $invoice = Invoice::create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-2026-STYLE',
            'invoice_date' => '2026-09-15',
            'due_date' => '2026-09-29',
            'status' => 'draft',
            'style' => 'minimalist',
            'currency' => 'USD',
            'subtotal' => 100,
            'total' => 100,
        ]);

        $response = $this->actingAs($this->user)->patch("/invoices/{$invoice->id}/style", [
            'style' => 'creative',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('creative', $invoice->fresh()->style);
    }

    public function test_user_can_download_invoice_pdf(): void
    {
        $invoice = Invoice::create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-2026-PDF',
            'invoice_date' => '2026-09-15',
            'due_date' => '2026-09-29',
            'status' => 'draft',
            'style' => 'minimalist',
            'currency' => 'USD',
            'subtotal' => 500,
            'total' => 500,
        ]);
        $invoice->items()->create([
            'description' => 'Sample Service Item',
            'quantity' => 1,
            'unit_price' => 500,
            'amount' => 500,
        ]);

        $response = $this->actingAs($this->user)->get("/invoices/{$invoice->id}/pdf");
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_user_can_view_invoice_show_page_in_all_styles(): void
    {
        $invoice = Invoice::create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-2026-VIEW',
            'invoice_date' => '2026-09-15',
            'due_date' => '2026-09-29',
            'status' => 'paid',
            'style' => 'minimalist',
            'currency' => 'USD',
            'subtotal' => 500,
            'total' => 500,
        ]);
        $invoice->items()->create([
            'description' => 'Test Item Line',
            'quantity' => 1,
            'unit_price' => 500,
            'amount' => 500,
        ]);

        foreach (['minimalist', 'corporate', 'creative', 'grid'] as $style) {
            $invoice->update(['style' => $style]);
            $response = $this->actingAs($this->user)->get("/invoices/{$invoice->id}");
            $response->assertStatus(200);
            $response->assertSee('#INV-2026-VIEW');
            $response->assertSee('Test Item Line');
        }
    }
}
