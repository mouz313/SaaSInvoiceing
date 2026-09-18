<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\ContactInquiry;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CmsController extends Controller
{
    // 1. Pages Management
    public function pages(): View
    {
        $pages = CmsPage::all();

        return view('admin.cms.pages', compact('pages'));
    }

    public function editPage(string $slug): View
    {
        $page = CmsPage::where('slug', $slug)->firstOrFail();

        return view('admin.cms.pages_edit', compact('page'));
    }

    public function updatePage(Request $request, string $slug): RedirectResponse
    {
        $page = CmsPage::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'is_published' => ['nullable', 'boolean'],
            'mission_statement' => ['nullable', 'string', 'max:2000'],
            'story' => ['nullable', 'string', 'max:3000'],
        ]);

        $content = $page->content ?? [];
        if ($request->has('mission_statement')) {
            $content['mission_statement'] = $request->input('mission_statement');
        }
        if ($request->has('story')) {
            $content['story'] = $request->input('story');
        }

        $page->update([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'is_published' => $request->has('is_published'),
            'content' => $content,
        ]);

        return redirect()->route('admin.cms.pages')->with('success', "Page '{$page->title}' updated successfully.");
    }

    // 2. FAQ Management
    public function faqs(): View
    {
        $faqs = Faq::orderBy('category')->orderBy('order', 'asc')->get();
        $categories = [
            'general' => 'General',
            'billing' => 'Billing & Packages',
            'features' => 'Features & PDF',
            'security' => 'Security & Auth',
        ];

        return view('admin.cms.faqs', compact('faqs', 'categories'));
    }

    public function storeFaq(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:3000'],
            'category' => ['required', 'string', 'in:general,billing,features,security'],
            'order' => ['nullable', 'integer'],
        ]);

        Faq::create([
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'category' => $validated['category'],
            'order' => $validated['order'] ?? 0,
            'is_published' => $request->has('is_published'),
        ]);

        return redirect()->route('admin.cms.faqs')->with('success', 'FAQ question created successfully.');
    }

    public function updateFaq(Request $request, Faq $faq): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:3000'],
            'category' => ['required', 'string', 'in:general,billing,features,security'],
            'order' => ['nullable', 'integer'],
        ]);

        $faq->update([
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'category' => $validated['category'],
            'order' => $validated['order'] ?? $faq->order,
            'is_published' => $request->has('is_published'),
        ]);

        return redirect()->route('admin.cms.faqs')->with('success', 'FAQ item updated successfully.');
    }

    public function deleteFaq(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('admin.cms.faqs')->with('success', 'FAQ item removed successfully.');
    }

    // 3. Contact Inquiries Management
    public function inquiries(Request $request): View
    {
        $status = $request->query('status', 'all');
        $query = ContactInquiry::latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $inquiries = $query->paginate(15);
        $unreadCount = ContactInquiry::unread()->count();

        return view('admin.cms.inquiries', compact('inquiries', 'status', 'unreadCount'));
    }

    public function updateInquiryStatus(Request $request, ContactInquiry $inquiry): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:unread,read,replied'],
        ]);

        $updateData = ['status' => $validated['status']];
        if ($validated['status'] === 'replied') {
            $updateData['replied_at'] = now();
        }

        $inquiry->update($updateData);

        return redirect()->back()->with('success', 'Inquiry status updated successfully.');
    }

    public function deleteInquiry(ContactInquiry $inquiry): RedirectResponse
    {
        $inquiry->delete();

        return redirect()->route('admin.cms.inquiries')->with('success', 'Inquiry message deleted.');
    }
}
