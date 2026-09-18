<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use App\Models\ContactInquiry;
use App\Models\Faq;
use App\Models\User;
use Database\Seeders\CmsAndSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCmsTest extends TestCase
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

    public function test_admin_can_view_cms_pages(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/cms/pages');

        $response->assertStatus(200);
        $response->assertSee('CMS Pages Management');
        $response->assertSee('about Page');
        $response->assertSee('contact Page');
        $response->assertSee('faq Page');
    }

    public function test_admin_can_update_cms_page(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/cms/pages/about', [
            'title' => 'Updated Mission for World Domination',
            'subtitle' => 'Building stellar invoices across galaxy',
            'meta_description' => 'Updated meta description for SEO',
            'is_published' => '1',
            'mission_statement' => 'To make invoicing ultra fast and glorious.',
        ]);

        $response->assertRedirect(route('admin.cms.pages'));
        $response->assertSessionHas('success');

        $page = CmsPage::findBySlug('about');
        $this->assertEquals('Updated Mission for World Domination', $page->title);
        $this->assertEquals('To make invoicing ultra fast and glorious.', $page->content['mission_statement']);
    }

    public function test_admin_can_manage_faqs_crud(): void
    {
        // 1. Create FAQ
        $createResp = $this->actingAs($this->admin)->post('/admin/cms/faqs', [
            'question' => 'How do I export my invoices to Excel or CSV?',
            'answer' => 'You can export client records and ledger statements directly from your dashboard.',
            'category' => 'features',
            'order' => 10,
            'is_published' => '1',
        ]);

        $createResp->assertRedirect(route('admin.cms.faqs'));
        $faq = Faq::where('question', 'How do I export my invoices to Excel or CSV?')->first();
        $this->assertNotNull($faq);

        // 2. Update FAQ
        $updateResp = $this->actingAs($this->admin)->put("/admin/cms/faqs/{$faq->id}", [
            'question' => 'How do I export my invoices to CSV or QuickBooks?',
            'answer' => 'Export CSV files with 1 click from the invoices tab.',
            'category' => 'features',
            'order' => 12,
            'is_published' => '1',
        ]);

        $updateResp->assertRedirect(route('admin.cms.faqs'));
        $this->assertEquals('How do I export my invoices to CSV or QuickBooks?', $faq->fresh()->question);

        // 3. Delete FAQ
        $deleteResp = $this->actingAs($this->admin)->delete("/admin/cms/faqs/{$faq->id}");
        $deleteResp->assertRedirect(route('admin.cms.faqs'));
        $this->assertNull(Faq::find($faq->id));
    }

    public function test_admin_can_manage_contact_inquiries(): void
    {
        $inquiry = ContactInquiry::create([
            'name' => 'Marcus Vance',
            'email' => 'marcus@example.com',
            'subject' => 'Billing Help Needed',
            'message' => 'Please help with custom invoice layout font sizing.',
            'status' => 'unread',
        ]);

        // View inquiries index
        $indexResp = $this->actingAs($this->admin)->get('/admin/cms/inquiries');
        $indexResp->assertStatus(200);
        $indexResp->assertSee('Marcus Vance');
        $indexResp->assertSee('Billing Help Needed');

        // Update status to read
        $patchResp = $this->actingAs($this->admin)->patch("/admin/cms/inquiries/{$inquiry->id}/status", [
            'status' => 'read',
        ]);
        $this->assertEquals('read', $inquiry->fresh()->status);

        // Delete inquiry
        $deleteResp = $this->actingAs($this->admin)->delete("/admin/cms/inquiries/{$inquiry->id}");
        $deleteResp->assertRedirect(route('admin.cms.inquiries'));
        $this->assertNull(ContactInquiry::find($inquiry->id));
    }

    public function test_regular_user_cannot_access_cms_routes(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/cms/pages');
        $response->assertStatus(403);
    }
}
