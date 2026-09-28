<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CouponService
{
    /**
     * Validate a coupon code specifically against an invoice.
     *
     * @return array{
     *     valid: bool,
     *     coupon: ?Coupon,
     *     discount_amount: float,
     *     message: string
     * }
     */
    public function validateInvoiceCoupon(string $code, Invoice $invoice, ?Client $client = null): array
    {
        $code = trim(strtoupper($code));

        if (empty($code)) {
            return [
                'valid' => false,
                'coupon' => null,
                'discount_amount' => 0.0,
                'message' => 'Please enter a coupon code.',
            ];
        }

        // Find coupon: must belong to the invoice's merchant OR be a global platform coupon
        $coupon = Coupon::query()
            ->whereRaw('UPPER(code) = ?', [$code])
            ->where(function ($query) use ($invoice) {
                $query->where('user_id', $invoice->user_id)
                    ->orWhereNull('user_id');
            })
            ->first();

        if (! $coupon) {
            return [
                'valid' => false,
                'coupon' => null,
                'discount_amount' => 0.0,
                'message' => "Coupon code '{$code}' is invalid or does not exist for this merchant.",
            ];
        }

        if (! $coupon->is_active) {
            return [
                'valid' => false,
                'coupon' => null,
                'discount_amount' => 0.0,
                'message' => "Coupon '{$coupon->code}' is currently inactive.",
            ];
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return [
                'valid' => false,
                'coupon' => null,
                'discount_amount' => 0.0,
                'message' => "Coupon '{$coupon->code}' is not yet active.",
            ];
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return [
                'valid' => false,
                'coupon' => null,
                'discount_amount' => 0.0,
                'message' => "Coupon '{$coupon->code}' expired on ".$coupon->expires_at->format('M d, Y').'.',
            ];
        }

        if ($coupon->max_uses && $coupon->times_used >= $coupon->max_uses) {
            return [
                'valid' => false,
                'coupon' => null,
                'discount_amount' => 0.0,
                'message' => "Coupon '{$coupon->code}' has reached its maximum usage limit.",
            ];
        }

        // Check if restricted to a specific client
        $effectiveClientId = $client?->id ?: $invoice->client_id;
        if ($coupon->client_id && (int) $coupon->client_id !== (int) $effectiveClientId) {
            return [
                'valid' => false,
                'coupon' => null,
                'discount_amount' => 0.0,
                'message' => "Coupon '{$coupon->code}' is reserved for a specific client account.",
            ];
        }

        // Check target applicability
        if ($coupon->applies_to !== 'all' && $coupon->applies_to !== 'invoices') {
            return [
                'valid' => false,
                'coupon' => null,
                'discount_amount' => 0.0,
                'message' => "Coupon '{$coupon->code}' is not valid for client invoices.",
            ];
        }

        // Check minimum spend
        $subtotal = (float) ($invoice->subtotal ?: $invoice->total);
        if ($coupon->min_spend && $subtotal < (float) $coupon->min_spend) {
            return [
                'valid' => false,
                'coupon' => null,
                'discount_amount' => 0.0,
                'message' => "A minimum invoice total of {$invoice->currency} ".number_format($coupon->min_spend, 2)." is required for coupon '{$coupon->code}'.",
            ];
        }

        // Calculate discount
        $discountAmount = $coupon->calculateDiscount($subtotal);
        if ($discountAmount <= 0) {
            return [
                'valid' => false,
                'coupon' => null,
                'discount_amount' => 0.0,
                'message' => 'Coupon resulted in zero discount.',
            ];
        }

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount_amount' => $discountAmount,
            'message' => "Coupon '{$coupon->code}' verified! You saved ".number_format($discountAmount, 2)." {$invoice->currency}.",
        ];
    }

    /**
     * Apply coupon discount to an invoice in an atomic database transaction.
     */
    public function applyCouponToInvoice(Coupon $coupon, Invoice $invoice, ?User $user = null): Invoice
    {
        return DB::transaction(function () use ($coupon, $invoice, $user) {
            $subtotal = (float) ($invoice->subtotal ?: $invoice->total);
            $discountAmount = $coupon->calculateDiscount($subtotal);

            // Recompute total and balance due
            $taxAmount = (float) ($invoice->tax_amount ?: 0);
            $whtAmount = (float) ($invoice->wht_amount ?: 0);
            $extraCharges = (float) ($invoice->additional_charges_total ?: 0);
            $amountPaid = (float) ($invoice->amount_paid ?: 0);

            $newTotal = max(0, round($subtotal + $taxAmount + $extraCharges - $whtAmount - $discountAmount, 2));
            $newBalanceDue = max(0, round($newTotal - $amountPaid, 2));

            $invoice->update([
                'coupon_id' => $coupon->id,
                'coupon_code' => $coupon->code,
                'discount_amount' => $discountAmount,
                'total' => $newTotal,
                'balance_due' => $newBalanceDue,
            ]);

            // Record usage audit
            $coupon->recordUsage($user ?: $invoice->user, $invoice, $discountAmount);

            return $invoice->fresh();
        });
    }

    /**
     * Remove an applied coupon from an invoice.
     */
    public function removeCouponFromInvoice(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice) {
            $subtotal = (float) ($invoice->subtotal ?: $invoice->total);
            $taxAmount = (float) ($invoice->tax_amount ?: 0);
            $whtAmount = (float) ($invoice->wht_amount ?: 0);
            $extraCharges = (float) ($invoice->additional_charges_total ?: 0);
            $amountPaid = (float) ($invoice->amount_paid ?: 0);

            $newTotal = max(0, round($subtotal + $taxAmount + $extraCharges - $whtAmount, 2));
            $newBalanceDue = max(0, round($newTotal - $amountPaid, 2));

            $invoice->update([
                'coupon_id' => null,
                'coupon_code' => null,
                'discount_amount' => 0.00,
                'total' => $newTotal,
                'balance_due' => $newBalanceDue,
            ]);

            return $invoice->fresh();
        });
    }
}
