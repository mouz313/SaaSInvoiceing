<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class PublicInvoiceController extends Controller
{
    /**
     * Display the public invoice view for clients.
     */
    public function show(string $token): View
    {
        $invoice = Invoice::with(['client', 'items', 'user', 'logo'])
            ->where('public_token', $token)
            ->firstOrFail();

        // Mark as viewed if not yet marked and not owner viewing
        $invoice->markAsViewed();

        return view('invoices.public', compact('invoice'));
    }

    /**
     * Download the invoice PDF publicly via secure token.
     */
    public function pdf(string $token): Response
    {
        $invoice = Invoice::with(['client', 'items', 'user', 'logo'])
            ->where('public_token', $token)
            ->firstOrFail();

        $viewName = match ($invoice->style) {
            'corporate' => 'invoices.templates.corporate',
            'creative' => 'invoices.templates.creative',
            'grid' => 'invoices.templates.grid',
            default => 'invoices.templates.minimalist',
        };

        $pdf = Pdf::loadView($viewName, [
            'invoice' => $invoice,
            'isPdf' => true,
        ]);

        $fileName = "Invoice-{$invoice->invoice_number}.pdf";

        return $pdf->download($fileName);
    }

    /**
     * Initiate Stripe checkout for paying the invoice online.
     */
    public function checkout(Request $request, string $token): RedirectResponse
    {
        $invoice = Invoice::with(['client', 'user'])
            ->where('public_token', $token)
            ->firstOrFail();

        if ($invoice->isPaid()) {
            return redirect()->route('invoices.public', $token)
                ->with('info', 'This invoice has already been marked as paid.');
        }

        $stripeSecret = config('services.stripe.secret');
        $isPlaceholder = empty($stripeSecret) || str_starts_with($stripeSecret, 'sk_test_placeholder');

        // Check if simulation mode is active or Stripe keys are test placeholders
        if ($request->boolean('simulate') || $isPlaceholder) {
            $invoice->markAsPaid('sim_pay_'.uniqid());

            return redirect()->route('invoices.public.success', $token)
                ->with('success', 'Payment simulated successfully! The invoice has been marked as paid.');
        }

        try {
            Stripe::setApiKey($stripeSecret);

            $currency = strtolower($invoice->currency ?: 'usd');
            $unitAmount = (int) round($invoice->total * 100);

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => 'Invoice #'.$invoice->invoice_number,
                            'description' => 'Payment for services by '.($invoice->user->company_name ?: $invoice->user->name),
                        ],
                        'unit_amount' => $unitAmount,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'customer_email' => $invoice->client->email,
                'metadata' => [
                    'type' => 'invoice_payment',
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'public_token' => $invoice->public_token,
                ],
                'success_url' => route('invoices.public.success', $token).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('invoices.public', $token),
            ]);

            return redirect()->away($session->url);
        } catch (\Exception $e) {
            Log::error('Invoice Stripe Checkout Error: '.$e->getMessage());

            return redirect()->route('invoices.public', $token)
                ->with('error', 'Unable to initiate online payment: '.$e->getMessage());
        }
    }

    /**
     * Handle return after successful payment.
     */
    public function success(Request $request, string $token): View
    {
        $invoice = Invoice::with(['client', 'items', 'user', 'logo'])
            ->where('public_token', $token)
            ->firstOrFail();

        $sessionId = $request->query('session_id');

        if ($sessionId && ! $invoice->isPaid()) {
            $stripeSecret = config('services.stripe.secret');
            if (! empty($stripeSecret) && ! str_starts_with($stripeSecret, 'sk_test_placeholder')) {
                try {
                    Stripe::setApiKey($stripeSecret);
                    $session = Session::retrieve($sessionId);
                    if ($session->payment_status === 'paid') {
                        $invoice->markAsPaid($session->payment_intent);
                    }
                } catch (\Exception $e) {
                    Log::error('Stripe Session Retrieval Failed: '.$e->getMessage());
                }
            }
        }

        return view('invoices.public_success', compact('invoice'));
    }
}
