<?php

namespace Tests\Feature;

use Database\Seeders\CmsAndSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CmsAndSettingsSeeder::class);
    }

    public function test_homepage_is_accessible_and_renders_rich_sections(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('InvoiceHub');
        $response->assertSee('Minimalist Clean');
        $response->assertSee('Corporate Enterprise');
        $response->assertSee('Modern Tech');
        $response->assertSee('Elegant Serif');
        $response->assertSee('Wall of Love');
        $response->assertSee('Predictable Pricing');
    }

    public function test_about_page_is_accessible_and_displays_cms_content(): void
    {
        $response = $this->get('/about');

        $response->assertStatus(200);
        $response->assertSee('Our Journey & Purpose', false);
        $response->assertSee('Why We Reimagined the Everyday Invoice');
        $response->assertSee('The Principles That Guide Us');
    }

    public function test_contact_page_is_accessible(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
        $response->assertSee('Direct Contact Channels');
        $response->assertSee('Send Us a Direct Message');
    }

    public function test_user_can_submit_contact_inquiry(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Sarah Connor',
            'email' => 'sarah@cyberdyne.test',
            'phone' => '+1 555-4321',
            'subject' => 'Enterprise & High Volume Plan',
            'message' => 'We need 500+ invoices generated monthly for our robotics division.',
        ]);

        $response->assertRedirect(route('pages.contact'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contact_inquiries', [
            'name' => 'Sarah Connor',
            'email' => 'sarah@cyberdyne.test',
            'subject' => 'Enterprise & High Volume Plan',
            'status' => 'unread',
        ]);
    }

    public function test_faq_page_is_accessible_and_renders_faqs(): void
    {
        $response = $this->get('/faq');

        $response->assertStatus(200);
        $response->assertSee('Frequently Asked Questions');
        $response->assertSee('Knowledge Base & Support', false);
        $response->assertSee('What makes InvoiceHub different from traditional invoicing software?');
    }
}
