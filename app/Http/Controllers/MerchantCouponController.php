<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MerchantCouponController extends Controller
{
    public function index(Request $request): View
    {
        $owner = $request->user()->currentAccountOwner();
        $search = $request->query('search');
        $status = $request->query('status');

        $query = Coupon::query()
            ->where('user_id', $owner->id)
            ->withCount('usages')
            ->with('client')
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $coupons = $query->paginate(12)->withQueryString();
        $clients = $owner->clients()->orderBy('name')->get();

        $totalCoupons = Coupon::where('user_id', $owner->id)->count();
        $activeCoupons = Coupon::where('user_id', $owner->id)->where('is_active', true)->count();
        $totalRedemptions = Coupon::where('user_id', $owner->id)->sum('times_used');

        return view('coupons.index', compact('coupons', 'clients', 'search', 'status', 'totalCoupons', 'activeCoupons', 'totalRedemptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $owner = $request->user()->currentAccountOwner();

        if ($request->user()->isViewer()) {
            abort(403, 'Viewers do not have permission to create promotional coupons.');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'min_spend' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $validated['code'] = strtoupper(Str::slug($validated['code'], ''));
        $validated['user_id'] = $owner->id;
        $validated['applies_to'] = 'invoices';
        $validated['is_active'] = $request->boolean('is_active', true);

        Coupon::create($validated);

        return redirect()->route('merchant.coupons.index')->with('success', "Coupon '{$validated['code']}' created successfully.");
    }

    public function toggleStatus(Request $request, Coupon $coupon): RedirectResponse
    {
        $owner = $request->user()->currentAccountOwner();

        if ((int) $coupon->user_id !== (int) $owner->id) {
            abort(403, 'Unauthorized access to coupon.');
        }

        if ($request->user()->isViewer()) {
            abort(403, 'Viewers cannot change coupon status.');
        }

        $coupon->update(['is_active' => ! $coupon->is_active]);

        $status = $coupon->is_active ? 'activated' : 'deactivated';

        return redirect()->route('merchant.coupons.index')->with('success', "Coupon '{$coupon->code}' has been {$status}.");
    }

    public function destroy(Request $request, Coupon $coupon): RedirectResponse
    {
        $owner = $request->user()->currentAccountOwner();

        if ((int) $coupon->user_id !== (int) $owner->id) {
            abort(403, 'Unauthorized access to coupon.');
        }

        if ($request->user()->isViewer()) {
            abort(403, 'Viewers cannot delete coupons.');
        }

        $code = $coupon->code;
        $coupon->delete();

        return redirect()->route('merchant.coupons.index')->with('success', "Coupon '{$code}' deleted successfully.");
    }
}
