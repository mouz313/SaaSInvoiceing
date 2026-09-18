<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function checkout(Request $request, Package $package): RedirectResponse
    {
        $user = Auth::user();

        // Check if simulation is requested or Stripe secret key is placeholder
        $stripeSecret = config('services.stripe.secret');
        $isPlaceholder = empty($stripeSecret) || str_starts_with($stripeSecret, 'sk_test_placeholder');

        if ($request->boolean('simulate') || $isPlaceholder) {
            return $this->processSuccessfulPurchase($user, $package, 'sim_'.uniqid());
        }

        try {
            Stripe::setApiKey($stripeSecret);

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $package->name.' Plan',
                            'description' => $package->description ?? 'Subscription to '.$package->name,
                        ],
                        'unit_amount' => (int) ($package->price * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'customer_email' => $user->email,
                'client_reference_id' => (string) $user->id,
                'metadata' => [
                    'package_id' => $package->id,
                    'user_id' => $user->id,
                ],
                'success_url' => route('stripe.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('stripe.cancel'),
            ]);

            // Save pending transaction
            Transaction::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'stripe_session_id' => $session->id,
                'amount' => $package->price,
                'currency' => 'usd',
                'status' => 'pending',
            ]);

            return redirect()->away($session->url);
        } catch (\Exception $e) {
            Log::error('Stripe Checkout Error: '.$e->getMessage());

            // If Stripe API errors (e.g. invalid credentials), offer simulated fallback
            return redirect()->route('dashboard')
                ->with('error', 'Stripe connection issue ('.$e->getMessage().'). You can test using simulated purchase.');
        }
    }

    public function success(Request $request): RedirectResponse
    {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return redirect()->route('dashboard');
        }

        $transaction = Transaction::where('stripe_session_id', $sessionId)->first();

        if ($transaction && $transaction->status !== 'completed') {
            $user = $transaction->user;
            $package = $transaction->package;

            if ($user && $package) {
                $this->processSuccessfulPurchase($user, $package, $sessionId);
            }
        }

        return redirect()->route('dashboard')->with('success', 'Payment successful! Your package has been activated.');
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('dashboard')->with('info', 'Checkout was cancelled. No charges were made.');
    }

    public function simulate(Package $package): RedirectResponse
    {
        $user = Auth::user();

        return $this->processSuccessfulPurchase($user, $package, 'sim_'.uniqid());
    }

    private function processSuccessfulPurchase($user, Package $package, string $sessionId): RedirectResponse
    {
        // Add credits or set unlimited
        $addedCredits = $package->invoice_limit === -1 ? 999999 : $package->invoice_limit;

        $user->update([
            'package_id' => $package->id,
            'invoice_credits' => $package->invoice_limit === -1 ? 999999 : ($user->invoice_credits + $addedCredits),
        ]);

        Transaction::updateOrCreate(
            ['stripe_session_id' => $sessionId],
            [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'amount' => $package->price,
                'currency' => 'usd',
                'status' => 'completed',
            ]
        );

        return redirect()->route('dashboard')
            ->with('success', "Success! You are now on the {$package->name} plan with upgraded invoice limits.");
    }
}
