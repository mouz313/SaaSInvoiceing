@extends('layouts.app')

@section('title', 'Promotional Coupons & Discounts')

@section('content')
<div class="space-y-6" x-data="{
    createModal: false,
    discountType: 'percentage'
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-200">Coupons &amp; Discounts</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Invoice Coupons &amp; Promo Codes</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Offer custom discounts, loyalty incentives, and seasonal promos on client invoice checkouts.</p>
        </div>
        <button @click="createModal = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs sm:text-sm shadow-sm transition hover:shadow-amber-600/20">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            Create New Coupon
        </button>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <i data-lucide="ticket-percent" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Coupons</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $totalCoupons }}</p>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Campaigns</p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $activeCoupons }}</p>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <i data-lucide="trending-up" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Times Redeemed</p>
                <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ number_format($totalRedemptions) }}</p>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
            <form method="GET" action="{{ route('merchant.coupons.index') }}" class="w-full sm:w-80 relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search coupon code or name..."
                       class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm focus:outline-hidden focus:ring-2 focus:ring-amber-500">
            </form>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('merchant.coupons.index') }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold {{ !$status ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">All</a>
                <a href="{{ route('merchant.coupons.index', ['status' => 'active']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $status === 'active' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">Active</a>
                <a href="{{ route('merchant.coupons.index', ['status' => 'inactive']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $status === 'inactive' ? 'bg-rose-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">Inactive</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px] border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="p-4">Coupon Code</th>
                        <th class="p-4">Discount</th>
                        <th class="p-4">Client Restriction</th>
                        <th class="p-4">Usage Limit</th>
                        <th class="p-4">Validity Period</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($coupons as $coupon)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                            <td class="p-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="inline-flex items-center gap-1 font-mono font-bold text-xs px-2.5 py-1 rounded-md bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <i data-lucide="ticket" class="w-3.5 h-3.5"></i>
                                        {{ $coupon->code }}
                                    </span>
                                    @if($coupon->name)
                                        <span class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[150px]">{{ $coupon->name }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4 font-semibold text-slate-900 dark:text-white">
                                @if($coupon->discount_type === 'percentage')
                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ (float)$coupon->discount_value }}% OFF</span>
                                    @if($coupon->max_discount)
                                        <span class="text-[10px] text-slate-400 block font-normal">(Max: {{ number_format($coupon->max_discount, 2) }})</span>
                                    @endif
                                @else
                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ number_format($coupon->discount_value, 2) }} Flat</span>
                                @endif
                                @if($coupon->min_spend)
                                    <span class="text-[10px] text-slate-400 block font-normal">Min spend: {{ number_format($coupon->min_spend, 2) }}</span>
                                @endif
                            </td>
                            <td class="p-4">
                                @if($coupon->client)
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-700 dark:text-slate-300">
                                        <i data-lucide="user" class="w-3.5 h-3.5 text-blue-500"></i>
                                        {{ $coupon->client->name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full">
                                        All Clients
                                    </span>
                                @endif
                            </td>
                            <td class="p-4">
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $coupon->times_used }}</span>
                                    <span class="text-slate-400">/ {{ $coupon->max_uses ?: '∞' }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-xs text-slate-600 dark:text-slate-400">
                                @if($coupon->expires_at)
                                    <span class="{{ $coupon->expires_at->isPast() ? 'text-rose-500 font-semibold' : '' }}">
                                        Expires {{ $coupon->expires_at->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-slate-400">No expiration</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <form method="POST" action="{{ route('merchant.coupons.toggle', $coupon) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold transition {{ $coupon->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 hover:bg-slate-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $coupon->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="p-4 text-right">
                                <form method="POST" action="{{ route('merchant.coupons.destroy', $coupon) }}" onsubmit="return confirm('Are you sure you want to permanently delete coupon {{ $coupon->code }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 mx-auto flex items-center justify-center mb-3">
                                    <i data-lucide="ticket-percent" class="w-6 h-6"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-300">No coupons created yet</p>
                                <p class="text-xs text-slate-400 mt-1">Create your first coupon code to offer client invoice discounts.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($coupons->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>

    <!-- Create Coupon Modal -->
    <div x-show="createModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4"
             @click.away="createModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="ticket-percent" class="w-4 h-4 text-amber-500"></i>
                    Create Promotional Coupon
                </h3>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('merchant.coupons.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Coupon Code *</label>
                        <input type="text" name="code" required placeholder="e.g. SAVE20" class="w-full uppercase font-mono px-3 py-2 text-xs sm:text-sm rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Campaign Title</label>
                        <input type="text" name="name" placeholder="e.g. Summer Promo" class="w-full px-3 py-2 text-xs sm:text-sm rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Discount Type *</label>
                        <select name="discount_type" x-model="discountType" class="w-full px-3 py-2 text-xs sm:text-sm rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                            <option value="percentage">Percentage (% OFF)</option>
                            <option value="fixed">Fixed Amount Discount</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            <span x-text="discountType === 'percentage' ? 'Percentage (e.g. 15 for 15%) *' : 'Discount Amount *'"></span>
                        </label>
                        <input type="number" step="0.01" min="0.01" name="discount_value" required placeholder="10" class="w-full px-3 py-2 text-xs sm:text-sm rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Client Restriction</label>
                        <select name="client_id" class="w-full px-3 py-2 text-xs sm:text-sm rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                            <option value="">All Clients (Universal)</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email ?: 'No email' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Minimum Spend Subtotal</label>
                        <input type="number" step="0.01" min="0" name="min_spend" placeholder="0.00" class="w-full px-3 py-2 text-xs sm:text-sm rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-show="discountType === 'percentage'">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Max Cap Discount Amount</label>
                        <input type="number" step="0.01" min="0" name="max_discount" placeholder="Unlimited cap" class="w-full px-3 py-2 text-xs sm:text-sm rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Total Max Uses</label>
                        <input type="number" min="1" name="max_uses" placeholder="Unlimited uses" class="w-full px-3 py-2 text-xs sm:text-sm rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Starts At (Optional)</label>
                        <input type="datetime-local" name="starts_at" class="w-full px-3 py-2 text-xs sm:text-sm rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Expires At (Optional)</label>
                        <input type="datetime-local" name="expires_at" class="w-full px-3 py-2 text-xs sm:text-sm rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" id="is_active_new" value="1" checked class="h-4 w-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                    <label for="is_active_new" class="text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">Activate coupon code immediately</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="createModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold shadow-xs transition">Create Coupon</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
