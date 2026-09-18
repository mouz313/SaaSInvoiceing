<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;
use App\Models\ContactInquiry;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        $page = CmsPage::findBySlug('about');

        return view('pages.about', compact('page'));
    }

    public function contact(): View
    {
        $page = CmsPage::findBySlug('contact');

        return view('pages.contact', compact('page'));
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        ContactInquiry::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'unread',
        ]);

        return redirect()->route('pages.contact')->with('success', 'Thank you for reaching out! Your inquiry has been received. Our support team will get back to you shortly.');
    }

    public function faq(Request $request): View
    {
        $page = CmsPage::findBySlug('faq');
        $currentCategory = $request->query('category', 'all');

        $query = Faq::published();
        if ($currentCategory !== 'all') {
            $query->where('category', $currentCategory);
        }

        $faqs = $query->get();
        $categories = [
            'all' => 'All Questions',
            'general' => 'General',
            'billing' => 'Billing & Packages',
            'features' => 'Features & PDF',
            'security' => 'Security & Auth',
        ];

        return view('pages.faq', compact('page', 'faqs', 'categories', 'currentCategory'));
    }
}
