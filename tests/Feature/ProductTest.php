<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'John Vendor',
            'email' => 'vendor@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'invoice_credits' => 10,
        ]);

        $this->otherUser = User::create([
            'name' => 'Other Vendor',
            'email' => 'other@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'invoice_credits' => 10,
        ]);
    }

    public function test_user_can_view_products_index(): void
    {
        $category = ProductCategory::create([
            'user_id' => $this->user->id,
            'name' => 'Food',
        ]);

        Product::create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'name' => 'Italian Pasta',
            'price' => 12.50,
            'unit' => 'kg',
            'sku' => 'FOOD-001',
        ]);

        $response = $this->actingAs($this->user)->get('/products');
        $response->assertStatus(200);
        $response->assertSee('Italian Pasta');
        $response->assertSee('Food');
    }

    public function test_user_can_create_product_category(): void
    {
        $response = $this->actingAs($this->user)->post('/product-categories', [
            'name' => 'Clothes',
            'color' => 'blue',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('product_categories', [
            'user_id' => $this->user->id,
            'name' => 'Clothes',
        ]);
    }

    public function test_user_can_create_product_category_via_json(): void
    {
        $response = $this->actingAs($this->user)->postJson('/product-categories', [
            'name' => 'Electronics',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'category' => [
                'name' => 'Electronics',
            ],
        ]);
        $this->assertDatabaseHas('product_categories', [
            'user_id' => $this->user->id,
            'name' => 'Electronics',
        ]);
    }

    public function test_user_can_delete_product_category(): void
    {
        $category = ProductCategory::create([
            'user_id' => $this->user->id,
            'name' => 'Shoes',
        ]);

        $response = $this->actingAs($this->user)->delete("/product-categories/{$category->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('product_categories', [
            'id' => $category->id,
        ]);
    }

    public function test_user_can_create_product_under_category(): void
    {
        $category = ProductCategory::create([
            'user_id' => $this->user->id,
            'name' => 'Shoes',
        ]);

        $response = $this->actingAs($this->user)->post('/products', [
            'name' => 'Running Sneakers',
            'category_id' => $category->id,
            'description' => 'Lightweight running sneakers',
            'price' => 85.00,
            'unit' => 'pair',
            'sku' => 'SHOE-001',
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'user_id' => $this->user->id,
            'name' => 'Running Sneakers',
            'price' => 85.00,
            'sku' => 'SHOE-001',
        ]);
    }

    public function test_user_can_update_product(): void
    {
        $product = Product::create([
            'user_id' => $this->user->id,
            'name' => 'Old Shirt',
            'price' => 20.00,
            'unit' => 'pcs',
        ]);

        $response = $this->actingAs($this->user)->put("/products/{$product->id}", [
            'name' => 'Premium Cotton Shirt',
            'price' => 35.00,
            'unit' => 'pcs',
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Premium Cotton Shirt',
            'price' => 35.00,
        ]);
    }

    public function test_user_can_delete_product(): void
    {
        $product = Product::create([
            'user_id' => $this->user->id,
            'name' => 'Item to Delete',
            'price' => 10.00,
        ]);

        $response = $this->actingAs($this->user)->delete("/products/{$product->id}");
        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_user_cannot_edit_another_users_product(): void
    {
        $product = Product::create([
            'user_id' => $this->otherUser->id,
            'name' => 'Other Item',
            'price' => 50.00,
        ]);

        $response = $this->actingAs($this->user)->get("/products/{$product->id}/edit");
        $response->assertStatus(403);

        $updateResponse = $this->actingAs($this->user)->put("/products/{$product->id}", [
            'name' => 'Hacked Name',
            'price' => 1.00,
        ]);
        $updateResponse->assertStatus(403);
    }

    public function test_products_are_passed_to_invoice_create_view(): void
    {
        Product::create([
            'user_id' => $this->user->id,
            'name' => 'Catalog Product A',
            'price' => 99.00,
        ]);

        $response = $this->actingAs($this->user)->get(route('invoices.create'));
        $response->assertStatus(200);
        $response->assertSee('Catalog Product A');
        $response->assertSee('1-Click Add From Products Catalog');
    }
}
