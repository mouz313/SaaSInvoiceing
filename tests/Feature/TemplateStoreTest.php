<?php

namespace Tests\Feature;

use App\Models\InvoiceTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_templates(): void
    {
        $response = $this->get('/templates');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_without_ntn_can_access_templates_index(): void
    {
        $user = User::factory()->create([
            'ntn' => null,
            'strn' => null,
        ]);

        InvoiceTemplate::create([
            'name' => 'Minimalist',
            'slug' => 'minimalist',
            'category' => 'Standard',
            'description' => 'Clean minimalist invoice',
            'price' => 0.00,
            'is_free' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)->get('/templates');

        $response->assertStatus(200);
        $response->assertSee('Minimalist');
    }

    public function test_authenticated_user_with_ntn_can_access_templates_index(): void
    {
        $user = User::factory()->create([
            'ntn' => '1234567-8',
            'strn' => '9876543-2',
        ]);

        InvoiceTemplate::create([
            'name' => 'Minimalist',
            'slug' => 'minimalist',
            'category' => 'Standard',
            'description' => 'Clean minimalist invoice',
            'price' => 0.00,
            'is_free' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)->get('/templates');

        $response->assertStatus(200);
        $response->assertSee('Minimalist');
    }

    public function test_authenticated_user_can_preview_template(): void
    {
        $user = User::factory()->create();

        InvoiceTemplate::create([
            'name' => 'Minimalist',
            'slug' => 'minimalist',
            'category' => 'Standard',
            'description' => 'Clean minimalist invoice',
            'price' => 0.00,
            'is_free' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)->get('/templates/minimalist/preview');

        $response->assertStatus(200);
        $response->assertSee('INVOICE');
    }
}
