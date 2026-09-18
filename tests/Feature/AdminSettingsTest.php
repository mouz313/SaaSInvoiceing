<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CmsAndSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CmsAndSettingsSeeder::class);

        $this->admin = User::create([
            'name' => 'Admin Boss',
            'email' => 'admin@invoicehub.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $this->user = User::create([
            'name' => 'Regular User',
            'email' => 'user@invoicehub.test',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);
    }

    public function test_admin_can_view_system_settings(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/settings');

        $response->assertStatus(200);
        $response->assertSee('System Settings & Integrations', false);
        $response->assertSee('General & Branding', false);
        $response->assertSee('Social Media');
        $response->assertSee('Stripe Payments');
        $response->assertSee('Firebase & Google OAuth', false);
    }

    public function test_admin_can_update_social_settings(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/settings', [
            'group' => 'social',
            'social_twitter' => 'https://twitter.com/my_new_twitter_handle',
            'social_linkedin' => 'https://linkedin.com/company/mynewbrand',
            'social_github' => 'https://github.com/mynewbrand',
            'social_facebook' => 'https://facebook.com/mynewbrand',
            'social_instagram' => 'https://instagram.com/mynewbrand',
            'social_youtube' => 'https://youtube.com/@mynewbrand',
        ]);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'social']));
        $response->assertSessionHas('success');

        $this->assertEquals('https://twitter.com/my_new_twitter_handle', setting('social_twitter'));
        $this->assertEquals('https://github.com/mynewbrand', setting('social_github'));
    }

    public function test_admin_can_update_stripe_settings(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/settings', [
            'group' => 'stripe',
            'stripe_mode' => 'test',
            'stripe_publishable_key' => 'pk_test_updated_1234567890',
            'stripe_secret_key' => 'sk_test_updated_0987654321',
            'stripe_webhook_secret' => 'whsec_updated_secret_hash',
            'stripe_currency' => 'eur',
        ]);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'stripe']));
        $response->assertSessionHas('success');

        $this->assertEquals('pk_test_updated_1234567890', setting('stripe_publishable_key'));
        $this->assertEquals('sk_test_updated_0987654321', setting('stripe_secret_key'));
        $this->assertEquals('eur', setting('stripe_currency'));
    }

    public function test_admin_can_update_firebase_settings(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/settings', [
            'group' => 'firebase',
            'firebase_api_key' => 'AIzaSyUpdatedKey123',
            'firebase_auth_domain' => 'updated-project.firebaseapp.com',
            'firebase_project_id' => 'updated-project',
            'firebase_storage_bucket' => 'updated-project.firebasestorage.app',
            'firebase_messaging_sender_id' => '998877665544',
            'firebase_app_id' => '1:998877665544:web:xyz123',
            'firebase_measurement_id' => 'G-XYZ12345',
        ]);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'firebase']));
        $response->assertSessionHas('success');

        $this->assertEquals('AIzaSyUpdatedKey123', setting('firebase_api_key'));
        $this->assertEquals('updated-project', setting('firebase_project_id'));
    }

    public function test_admin_can_upload_and_remove_logo_and_favicon(): void
    {
        Storage::fake('public');

        $logo = UploadedFile::fake()->image('custom_logo.png', 200, 200);
        $favicon = UploadedFile::fake()->create('custom_favicon.ico', 10, 'image/x-icon');

        $response = $this->actingAs($this->admin)->post('/admin/settings', [
            'group' => 'general',
            'app_name' => 'BrandNewHub',
            'support_email' => 'support@brandnewhub.test',
            'logo' => $logo,
            'favicon' => $favicon,
        ]);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'general']));
        $response->assertSessionHas('success');

        $this->assertEquals('BrandNewHub', setting('app_name'));
        $this->assertNotNull(setting('app_logo'));
        $this->assertNotNull(setting('app_favicon'));

        $logoPath = setting('app_logo');
        $faviconPath = setting('app_favicon');
        Storage::disk('public')->assertExists($logoPath);
        Storage::disk('public')->assertExists($faviconPath);

        // Test removal
        $removeResponse = $this->actingAs($this->admin)->post('/admin/settings', [
            'group' => 'general',
            'app_name' => 'BrandNewHub',
            'support_email' => 'support@brandnewhub.test',
            'remove_logo' => '1',
            'remove_favicon' => '1',
        ]);

        $removeResponse->assertRedirect(route('admin.settings.index', ['tab' => 'general']));
        $this->assertEmpty(setting('app_logo'));
        $this->assertEmpty(setting('app_favicon'));
        Storage::disk('public')->assertMissing($logoPath);
        Storage::disk('public')->assertMissing($faviconPath);
    }

    public function test_regular_user_cannot_access_settings(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/settings');
        $response->assertStatus(403);
    }
}
