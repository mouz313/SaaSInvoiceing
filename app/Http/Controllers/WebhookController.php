<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Package;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class WebhookController extends Controller
{
    public function handleStripe(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        $event = null;

        if (! empty($webhookSecret) && ! str_starts_with($webhookSecret, 'whsec_placeholder')) {
            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
            } catch (SignatureVerificationException $e) {
                Log::error('Stripe Webhook Signature Verification Failed: '.$e->getMessage());

                return response()->json(['error' => 'Invalid signature'], 400);
            } catch (\UnexpectedValueException $e) {
                Log::error('Stripe Webhook Invalid Payload: '.$e->getMessage());

                return response()->json(['error' => 'Invalid payload'], 400);
            }
        } else {
            $event = json_decode($payload);
        }

        if (! $event) {
            return response()->json(['error' => 'Empty event'], 400);
        }

        $eventType = is_object($event) ? ($event->type ?? '') : '';

        if ($eventType === 'checkout.session.completed') {
            $session = $event->data->object;
            $sessionId = $session->id ?? null;

            // Check if this is an invoice payment
            if (($session->metadata->type ?? '') === 'invoice_payment') {
                $invoiceId = $session->metadata->invoice_id ?? null;
                if ($invoiceId) {
                    $invoice = Invoice::find($invoiceId);
                    if ($invoice) {
                        $invoice->markAsPaid($session->payment_intent ?? $sessionId);
                        Log::info("Invoice #{$invoice->invoice_number} marked as paid via Stripe Webhook");

                        return response()->json(['status' => 'invoice_paid']);
                    }
                }
            }

            $userId = $session->metadata->user_id ?? $session->client_reference_id ?? null;
            $packageId = $session->metadata->package_id ?? null;

            if ($userId && $packageId) {
                $user = User::find($userId);
                $package = Package::find($packageId);

                if ($user && $package) {
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

                    Log::info("User {$user->id} upgraded to package {$package->name} via Stripe Webhook");
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
