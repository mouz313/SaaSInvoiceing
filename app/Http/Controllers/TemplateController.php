<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
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
     * Full-page live interactive preview of an invoice template style.
     */
    public function preview(Request $request, string $slug): View
    {
        $user = Auth::user();
        $template = InvoiceTemplate::where('slug', $slug)->first();

        if (! $template) {
            $template = new InvoiceTemplate([
                'name' => ucwords(str_replace(['_', '-'], ' ', $slug)),
                'slug' => $slug,
                'category' => 'Standard',
                'description' => 'Baseline professional invoice design style.',
                'price' => 0.00,
                'is_free' => true,
                'is_active' => true,
            ]);
        }

        $invoice = InvoiceTemplate::sampleInvoice($user);
        $invoice->style = $slug;
        $isOwned = $template->exists ? $template->isOwnedBy($user) : true;

        return view('templates.preview', compact('template', 'invoice', 'isOwned'));
    }

    /**
     * Start Stripe checkout session for purchasing a template.
     */
    public function checkout(Request $request, InvoiceTemplate $template): RedirectResponse
    {
        $user = Auth::user();

        if ($template->isOwnedBy($user)) {
            return redirect()->route('templates.index')
                ->with('info', "You already own the {$template->name} template!");
        }

        $couponCode = $request->input('coupon_code');
        $coupon = null;
        $finalPrice = (float) $template->price;
        $discountAmount = 0.00;

        if ($couponCode) {
            $coupon = Coupon::active()
                ->where('code', strtoupper(trim($couponCode)))
                ->first();

            if ($coupon && $coupon->isValid((float) $template->price, 'templates')) {
                $discountAmount = $coupon->calculateDiscount((float) $template->price);
                $finalPrice = max(0, round((float) $template->price - $discountAmount, 2));
            }
        }

        // Free template or 100% coupon discount instant unlock
        if ($template->is_free || $finalPrice <= 0) {
            UserTemplatePurchase::firstOrCreate([
                'user_id' => $user->id,
                'template_id' => $template->id,
            ], [
                'price_paid' => 0.00,
                'currency' => $template->currency ?: 'USD',
                'payment_method' => $coupon ? 'coupon_unlock' : 'free_unlock',
                'transaction_id' => ($coupon ? 'cpn_' : 'free_').time(),
            ]);

            if ($coupon) {
                $coupon->recordUsage($user->id, $template, $discountAmount);
            }

            return redirect()->route('templates.index')
                ->with('success', $coupon
                    ? "🎉 Coupon '{$coupon->code}' applied! Template '{$template->name}' unlocked for free!"
                    : "Template '{$template->name}' has been unlocked for free!");
        }

        // Stripe Checkout
        $stripeSecret = setting('stripe_secret_key') ?: config('services.stripe.secret');

        if (! $stripeSecret) {
            return back()->with('error', 'Payment gateway is not currently configured. Please contact administrator.');
        }

        Stripe::setApiKey($stripeSecret);

        $successUrl = route('templates.checkout.success', ['template' => $template->slug])
            .'?session_id={CHECKOUT_SESSION_ID}'
            .($coupon ? '&coupon_id='.$coupon->id.'&discount='.$discountAmount.'&amount='.$finalPrice : '');

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'customer_email' => $user->email,
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($template->currency ?: 'usd'),
                    'product_data' => [
                        'name' => "Invoice Template: {$template->name}",
                        'description' => $coupon
                            ? "Lifetime access to {$template->name} (Coupon: {$coupon->code} applied)"
                            : ($template->description ?: "Lifetime access to the {$template->name} invoice design style."),
                    ],
                    'unit_amount' => (int) round($finalPrice * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'metadata' => [
                'type' => 'template_purchase',
                'user_id' => (string) $user->id,
                'template_id' => (string) $template->id,
                'template_slug' => $template->slug,
                'coupon_id' => $coupon ? (string) $coupon->id : '',
                'discount' => (string) $discountAmount,
            ],
            'success_url' => $successUrl,
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
        $couponId = $request->query('coupon_id');
        $discount = (float) $request->query('discount', 0);
        $amount = (float) $request->query('amount', $template->price);

        UserTemplatePurchase::firstOrCreate([
            'user_id' => $user->id,
            'template_id' => $template->id,
        ], [
            'price_paid' => $amount,
            'currency' => $template->currency ?: 'USD',
            'payment_method' => 'stripe',
            'transaction_id' => $sessionId ?: 'tx_'.time(),
        ]);

        if ($couponId) {
            $coupon = Coupon::find($couponId);
            if ($coupon) {
                $coupon->recordUsage($user->id, $template, $discount);
            }
        }

        return redirect()->route('templates.index')
            ->with('success', "🎉 Congratulations! You have successfully unlocked the '{$template->name}' template. It is now ready to use in your invoices!");
    }
}
