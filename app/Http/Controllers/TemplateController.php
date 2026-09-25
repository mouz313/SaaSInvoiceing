<?php

namespace App\Http\Controllers;

use App\Models\InvoiceTemplate;
use App\Models\UserTemplatePurchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class TemplateController extends Controller
{
    /**
     * Display the Template Store / Marketplace.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $category = $request->query('category');
        $search = $request->query('search');
        $filter = $request->query('filter', 'all'); // 'all', 'owned', 'premium', 'free'

        $query = InvoiceTemplate::where('is_active', true)->orderBy('sort_order');

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $allTemplates = $query->get();
        $ownedSlugs = $user ? $user->ownedTemplateSlugs() : ['minimalist', 'corporate', 'creative', 'grid'];

        // Apply ownership / price filter in memory
        $templates = $allTemplates->filter(function ($t) use ($filter, $ownedSlugs) {
            $isOwned = in_array($t->slug, $ownedSlugs, true);
            if ($filter === 'owned') {
                return $isOwned;
            }
            if ($filter === 'premium') {
                return ! $t->is_free && ! $isOwned;
            }
            if ($filter === 'free') {
                return $t->is_free;
            }

            return true;
        });

        $categories = InvoiceTemplate::where('is_active', true)->pluck('category')->unique()->sort()->values();
        $mockInvoice = InvoiceTemplate::sampleInvoice($user);

        return view('templates.index', compact('templates', 'ownedSlugs', 'categories', 'category', 'search', 'filter', 'mockInvoice'));
    }

    /**
     * Start Stripe checkout session for purchasing a template.
     */
    public function checkout(InvoiceTemplate $template): RedirectResponse
    {
        $user = Auth::user();

        if ($template->isOwnedBy($user)) {
            return redirect()->route('templates.index')
                ->with('info', "You already own the {$template->name} template!");
        }

        // Free template instant unlock
        if ($template->is_free || (float) $template->price <= 0) {
            UserTemplatePurchase::firstOrCreate([
                'user_id' => $user->id,
                'template_id' => $template->id,
            ], [
                'price_paid' => 0.00,
                'currency' => $template->currency ?: 'USD',
                'payment_method' => 'free_unlock',
                'transaction_id' => 'free_'.time(),
            ]);

            return redirect()->route('templates.index')
                ->with('success', "Template '{$template->name}' has been unlocked for free!");
        }

        // Stripe Checkout
        $stripeSecret = setting('stripe_secret_key') ?: config('services.stripe.secret');

        if (! $stripeSecret) {
            return back()->with('error', 'Payment gateway is not currently configured. Please contact administrator.');
        }

        Stripe::setApiKey($stripeSecret);

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'customer_email' => $user->email,
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($template->currency ?: 'usd'),
                    'product_data' => [
                        'name' => "Invoice Template: {$template->name}",
                        'description' => $template->description ?: "Lifetime access to the {$template->name} invoice design style.",
                    ],
                    'unit_amount' => (int) round($template->price * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'metadata' => [
                'type' => 'template_purchase',
                'user_id' => (string) $user->id,
                'template_id' => (string) $template->id,
                'template_slug' => $template->slug,
            ],
            'success_url' => route('templates.checkout.success', ['template' => $template->slug]).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('templates.index'),
        ]);

        return redirect($session->url);
    }

    /**
     * Handle successful checkout return from Stripe.
     */
    public function checkoutSuccess(Request $request, InvoiceTemplate $template): RedirectResponse
    {
        $user = Auth::user();
        $sessionId = $request->query('session_id');

        UserTemplatePurchase::firstOrCreate([
            'user_id' => $user->id,
            'template_id' => $template->id,
        ], [
            'price_paid' => $template->price,
            'currency' => $template->currency ?: 'USD',
            'payment_method' => 'stripe',
            'transaction_id' => $sessionId ?: 'tx_'.time(),
        ]);

        return redirect()->route('templates.index')
            ->with('success', "🎉 Congratulations! You have successfully unlocked the '{$template->name}' template. It is now ready to use in your invoices!");
    }
}
