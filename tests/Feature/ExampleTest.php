<?php

namespace Tests\Feature;

use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        Package::create([
            'name' => 'Starter',
            'slug' => 'starter',
            'price' => 9.00,
            'billing_period' => 'monthly',
            'invoice_limit' => 25,
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('InvoiceHub');
    }
}
