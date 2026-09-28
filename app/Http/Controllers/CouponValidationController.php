<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponValidationController extends Controller
{
    /**
     * Validate coupon code via AJAX for checkouts.
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'target_type' => ['nullable', 'string', 'in:packages,templates,invoices'],
        ]);

        $code = strtoupper(trim($validated['code']));
        $amount = (float) $validated['amount'];
        $targetType = $validated['target_type'] ?? null;

        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'The coupon code entered does not exist.',
            ], 404);
        }

        if (! $coupon->is_active) {
            return response()->json([
                'valid' => false,
                'message' => 'This coupon code is currently disabled.',
            ], 422);
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return response()->json([
                'valid' => false,
                'message' => 'This promotional campaign has not started yet.',
            ], 422);
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return response()->json([
                'valid' => false,
                'message' => 'This coupon code has expired.',
            ], 422);
        }

        if ($coupon->max_uses && $coupon->times_used >= $coupon->max_uses) {
            return response()->json([
                'valid' => false,
                'message' => 'This coupon code has reached its maximum redemption limit.',
            ], 422);
        }

        if ($targetType && $coupon->applies_to !== 'all' && $coupon->applies_to !== $targetType) {
            return response()->json([
                'valid' => false,
                'message' => "This coupon is only eligible for {$coupon->applies_to}.",
            ], 422);
        }

        if ($coupon->min_spend && $amount < (float) $coupon->min_spend) {
            return response()->json([
                'valid' => false,
                'message' => "Minimum spend of {$coupon->min_spend} is required to apply this coupon.",
            ], 422);
        }

        $discount = $coupon->calculateDiscount($amount);
        $finalAmount = max(0, round($amount - $discount, 2));

        return response()->json([
            'valid' => true,
            'code' => $coupon->code,
            'name' => $coupon->name ?: $coupon->code,
            'discount_type' => $coupon->discount_type,
            'discount_value' => (float) $coupon->discount_value,
            'discount_amount' => $discount,
            'original_amount' => $amount,
            'final_amount' => $finalAmount,
            'message' => "Coupon '{$coupon->code}' applied successfully!",
        ]);
    }
}
