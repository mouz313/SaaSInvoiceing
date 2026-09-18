<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StripeCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_simulated_checkout_activates_package_and_upgrades_credits(): void
    {
        $user = User::create([
            'name' => 'Buyer User',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'invoice_credits' => 5,
        ]);

        $package = Package::create([
            'name' => 'Professional',
            'slug' => 'professional',
            'price' => 29.00,
            'billing_period' => 'monthly',
            'invoice_limit' => 100,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get("/checkout/simulate/{$package->id}");

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals($package->id, $user->package_id);
        $this->assertEquals(105, $user->invoice_credits); // 5 + 100

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'package_id' => $package->id,
            'status' => 'completed',
        ]);
    }
}
