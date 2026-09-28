<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceCouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_merchant_can_create_invoice_coupon(): void
    {
        $merchant = User::factory()->create();

        $response = $this->actingAs($merchant)->post(route('merchant.coupons.store'), [
            'code' => 'PROMO15',
            'name' => '15% Off Consulting',
            'discount_type' => 'percentage',
            'discount_value' => '15.00',
            'min_spend' => '1000.00',
            'max_discount' => '500.00',
            'max_uses' => '50',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('merchant.coupons.index'));
        $this->assertDatabaseHas('coupons', [
            'user_id' => $merchant->id,
            'code' => 'PROMO15',
            'discount_type' => 'percentage',
            'discount_value' => 15.00,
            'applies_to' => 'invoices',
        ]);
    }

    public function test_merchant_can_toggle_coupon_status_and_delete(): void
    {
        $merchant = User::factory()->create();
        $coupon = Coupon::create([
            'user_id' => $merchant->id,
            'code' => 'TOGGLE10',
            'discount_type' => 'percentage',
            'discount_value' => 10.00,
            'applies_to' => 'invoices',
            'is_active' => true,
        ]);

        $response = $this->actingAs($merchant)->patch(route('merchant.coupons.toggle', $coupon));
        $response->assertSessionHas('success');
        $this->assertFalse($coupon->fresh()->is_active);

        $deleteResponse = $this->actingAs($merchant)->delete(route('merchant.coupons.destroy', $coupon));
        $deleteResponse->assertRedirect(route('merchant.coupons.index'));
        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }

    public function test_client_can_apply_coupon_to_public_invoice(): void
    {
        $merchant = User::factory()->create();
        $client = Client::create([
            'user_id' => $merchant->id,
            'name' => 'Coupon Test Client',
        ]);

        $coupon = Coupon::create([
            'user_id' => $merchant->id,
            'code' => 'SAVE200',
            'discount_type' => 'fixed',
            'discount_value' => 200.00,
            'applies_to' => 'invoices',
            'is_active' => true,
        ]);

        $invoice = Invoice::create([
            'user_id' => $merchant->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-2026-COUPON',
            'invoice_date' => now(),
            'due_date' => now()->addDays(7),
            'status' => 'sent',
            'currency' => 'PKR',
            'subtotal' => 1000.00,
            'total' => 1000.00,
            'balance_due' => 1000.00,
        ]);

        $response = $this->post(route('invoices.public.coupon.apply', $invoice->public_token), [
            'code' => 'SAVE200',
        ]);

        $response->assertSessionHas('success');
        $invoice->refresh();

        $this->assertSame($coupon->id, $invoice->coupon_id);
        $this->assertSame('SAVE200', $invoice->coupon_code);
        $this->assertEquals(200.00, (float) $invoice->discount_amount);
        $this->assertEquals(800.00, (float) $invoice->total);
        $this->assertEquals(800.00, (float) $invoice->balance_due);
        $this->assertSame(1, $coupon->fresh()->times_used);
    }

    public function test_expired_coupon_cannot_be_applied(): void
    {
        $merchant = User::factory()->create();
        $client = Client::create([
            'user_id' => $merchant->id,
            'name' => 'Expired Test Client',
        ]);

        $coupon = Coupon::create([
            'user_id' => $merchant->id,
            'code' => 'EXPIRED50',
            'discount_type' => 'percentage',
            'discount_value' => 50.00,
            'applies_to' => 'invoices',
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $invoice = Invoice::create([
            'user_id' => $merchant->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-2026-EXP',
            'invoice_date' => now(),
            'due_date' => now()->addDays(7),
            'status' => 'sent',
            'currency' => 'PKR',
            'subtotal' => 1000.00,
            'total' => 1000.00,
            'balance_due' => 1000.00,
        ]);

        $response = $this->post(route('invoices.public.coupon.apply', $invoice->public_token), [
            'code' => 'EXPIRED50',
        ]);

        $response->assertSessionHas('error');
        $invoice->refresh();
        $this->assertNull($invoice->coupon_id);
        $this->assertEquals(1000.00, (float) $invoice->total);
    }

    public function test_client_can_remove_applied_coupon(): void
    {
        $merchant = User::factory()->create();
        $client = Client::create([
            'user_id' => $merchant->id,
            'name' => 'Remove Coupon Client',
        ]);

        $coupon = Coupon::create([
            'user_id' => $merchant->id,
            'code' => 'REMOVE20',
            'discount_type' => 'percentage',
            'discount_value' => 20.00,
            'applies_to' => 'invoices',
            'is_active' => true,
        ]);

        $invoice = Invoice::create([
            'user_id' => $merchant->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-2026-REM',
            'invoice_date' => now(),
            'due_date' => now()->addDays(7),
            'status' => 'sent',
            'currency' => 'PKR',
            'subtotal' => 1000.00,
            'coupon_id' => $coupon->id,
            'coupon_code' => 'REMOVE20',
            'discount_amount' => 200.00,
            'total' => 800.00,
            'balance_due' => 800.00,
        ]);

        $response = $this->post(route('invoices.public.coupon.remove', $invoice->public_token));

        $response->assertSessionHas('success');
        $invoice->refresh();

        $this->assertNull($invoice->coupon_id);
        $this->assertNull($invoice->coupon_code);
        $this->assertEquals(0.00, (float) $invoice->discount_amount);
        $this->assertEquals(1000.00, (float) $invoice->total);
        $this->assertEquals(1000.00, (float) $invoice->balance_due);
    }
}
