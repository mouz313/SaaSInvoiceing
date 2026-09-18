<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Jane Freelancer',
            'email' => 'jane@example.com',
            'password' => Hash::make('oldpassword123'),
            'role' => 'user',
            'invoice_credits' => 5,
        ]);
    }

    public function test_user_can_view_profile_settings_page(): void
    {
        $response = $this->actingAs($this->user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Account & Invoicing Settings', false);
        $response->assertSee('Jane Freelancer');
        $response->assertSee('Appearance & Theme', false);
    }

    public function test_user_can_update_profile_and_business_details(): void
    {
        $response = $this->actingAs($this->user)->put('/profile', [
            'name' => 'Jane Doe Studios',
            'company_name' => 'Apex Digital Solutions',
            'email' => 'jane.doe@example.com',
            'phone' => '+1 555-123-4567',
            'address' => '742 Evergreen Terrace',
            'city' => 'Springfield',
            'country' => 'United States',
            'tax_id' => 'US-99887766',
            'default_currency' => 'EUR',
            'default_notes' => 'Standard payment terms: Net 15 days.',
            'default_payment_instructions' => 'Wire to Account IBAN DE89370400440532013000',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $fresh = $this->user->fresh();
        $this->assertEquals('Jane Doe Studios', $fresh->name);
        $this->assertEquals('Apex Digital Solutions', $fresh->company_name);
        $this->assertEquals('jane.doe@example.com', $fresh->email);
        $this->assertEquals('EUR', $fresh->default_currency);
        $this->assertEquals('US-99887766', $fresh->tax_id);
        $this->assertEquals('Standard payment terms: Net 15 days.', $fresh->default_notes);
    }

    public function test_user_can_upload_avatar(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->actingAs($this->user)->put('/profile', [
            'name' => 'Jane Freelancer',
            'email' => 'jane@example.com',
            'avatar' => $file,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertNotNull($this->user->fresh()->avatar_url);
        $this->assertStringContainsString('/storage/avatars/', $this->user->fresh()->avatar_url);
    }

    public function test_user_can_update_password(): void
    {
        $response = $this->actingAs($this->user)->put('/profile/password', [
            'current_password' => 'oldpassword123',
            'password' => 'newsecretpassword123',
            'password_confirmation' => 'newsecretpassword123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('newsecretpassword123', $this->user->fresh()->password));
    }

    public function test_password_update_rejects_incorrect_current_password(): void
    {
        $response = $this->actingAs($this->user)->put('/profile/password', [
            'current_password' => 'wrongpassword',
            'password' => 'newsecretpassword123',
            'password_confirmation' => 'newsecretpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('oldpassword123', $this->user->fresh()->password));
    }

    public function test_user_can_update_personal_section_independently(): void
    {
        $response = $this->actingAs($this->user)->put('/profile/personal', [
            'name' => 'Jane Independent',
            'email' => 'jane.ind@example.com',
            'phone' => '+1 555-999-8888',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $fresh = $this->user->fresh();
        $this->assertEquals('Jane Independent', $fresh->name);
        $this->assertEquals('jane.ind@example.com', $fresh->email);
        $this->assertEquals('+1 555-999-8888', $fresh->phone);
    }

    public function test_user_can_update_business_section_independently(): void
    {
        $response = $this->actingAs($this->user)->put('/profile/business', [
            'company_name' => 'Independent Agency Co',
            'tax_id' => 'TAX-778899',
            'address' => '123 Market St',
            'city' => 'Austin',
            'country' => 'USA',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $fresh = $this->user->fresh();
        $this->assertEquals('Independent Agency Co', $fresh->company_name);
        $this->assertEquals('TAX-778899', $fresh->tax_id);
        $this->assertEquals('123 Market St', $fresh->address);
    }

    public function test_user_can_update_invoicing_defaults_independently(): void
    {
        $response = $this->actingAs($this->user)->put('/profile/invoicing', [
            'default_currency' => 'GBP',
            'default_payment_instructions' => 'Wire to UK Bank Sort Code 20-00-00',
            'default_notes' => 'Net 30 days terms apply.',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $fresh = $this->user->fresh();
        $this->assertEquals('GBP', $fresh->default_currency);
        $this->assertEquals('Wire to UK Bank Sort Code 20-00-00', $fresh->default_payment_instructions);
        $this->assertEquals('Net 30 days terms apply.', $fresh->default_notes);
    }
}
