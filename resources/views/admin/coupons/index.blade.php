@extends('layouts.admin')

@section('title', 'Promotional Coupons & Discounts')

@section('content')
<div class="space-y-6" x-data="{
    createModal: false,
    editModal: false,
    currentCoupon: {
        id: '',
        code: '',
        name: '',
        description: '',
        discount_type: 'percentage',
        discount_value: '',
        applies_to: 'all',
        min_spend: '',
        max_discount: '',
        max_uses: '',
        starts_at: '',
        expires_at: '',
        is_active: true
    },
    openEdit(coupon) {
        this.currentCoupon = { ...coupon };
        this.editModal = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">Discount Coupons & Vouchers</h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Create promotional discount codes for packages, templates, and invoices</p>
        </div>
        <button @click="createModal = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-sm transition-all hover:scale-105">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            Create New Coupon
        </button>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <i data-lucide="ticket" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Coupons</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $totalCoupons }}</p>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Campaigns</p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $activeCoupons }}</p>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                <i data-lucide="sparkles" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Redemptions</p>
                <p class="text-2xl font-black text-purple-600 dark:text-purple-400">{{ number_format($totalRedemptions) }}</p>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.coupons.index') }}" class="w-full sm:w-80 relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search code, title..."
                       class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm focus:outline-hidden focus:ring-2 focus:ring-blue-500">
            </form>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('admin.coupons.index') }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold {{ !$status ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">All</a>
                <a href="{{ route('admin.coupons.index', ['status' => 'active']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $status === 'active' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">Active</a>
                <a href="{{ route('admin.coupons.index', ['status' => 'inactive']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $status === 'inactive' ? 'bg-rose-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">Inactive</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px] border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="p-4">Coupon Code</th>
                        <th class="p-4">Discount</th>
                        <th class="p-4">Applies To</th>
                        <th class="p-4">Usage / Limit</th>
                        <th class="p-4">Validity</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($coupons as $coupon)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 font-mono font-extrabold text-blue-700 dark:text-blue-300 text-xs">
                                    {{ $coupon->code }}
                                </span>
                                @if($coupon->name)
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">({{ $coupon->name }})</span>
                                @endif
                            </div>
                            @if($coupon->description)
                                <p class="text-[11px] text-slate-400 mt-1 max-w-xs truncate">{{ $coupon->description }}</p>
                            @endif
                        </td>
                        <td class="p-4">
                            <span class="font-black text-slate-900 dark:text-white">
                                @if($coupon->discount_type === 'percentage')
                                    {{ (float) $coupon->discount_value }}% OFF
                                @else
                                    {{ \App\Support\Currency::symbol('PKR') }} {{ number_format($coupon->discount_value, 2) }} OFF
                                @endif
                            </span>
                            @if($coupon->min_spend)
                                <p class="text-[11px] text-slate-400">Min spend: {{ \App\Support\Currency::symbol('PKR') }} {{ number_format($coupon->min_spend, 0) }}</p>
                            @endif
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $coupon->applies_to }}
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="font-medium text-slate-700 dark:text-slate-300">
                                <span class="font-bold text-slate-900 dark:text-white">{{ $coupon->times_used }}</span>
                                <span class="text-slate-400">/ {{ $coupon->max_uses ? $coupon->max_uses : '∞' }}</span>
                            </div>
                        </td>
                        <td class="p-4 text-xs text-slate-500 dark:text-slate-400">
                            @if($coupon->expires_at)
                                @if($coupon->expires_at->isPast())
                                    <span class="text-rose-500 font-bold">Expired</span>
                                @else
                                    <span>Until {{ $coupon->expires_at->format('M d, Y') }}</span>
                                @endif
                            @else
                                <span class="text-emerald-500 font-medium">No Expiry</span>
                            @endif
                        </td>
                        <td class="p-4">
                            <form method="POST" action="{{ route('admin.coupons.toggle', $coupon) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-2.5 py-1 rounded-full text-xs font-bold transition {{ $coupon->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-800' }}">
                                    {{ $coupon->is_active ? 'Active' : 'Disabled' }}
                                </button>
                            </form>
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button"
                                        @click="openEdit({
                                            id: {{ $coupon->id }},
                                            code: '{{ addslashes($coupon->code) }}',
                                            name: '{{ addslashes($coupon->name ?? '') }}',
                                            description: '{{ addslashes($coupon->description ?? '') }}',
                                            discount_type: '{{ $coupon->discount_type }}',
                                            discount_value: '{{ $coupon->discount_value }}',
                                            applies_to: '{{ $coupon->applies_to }}',
                                            min_spend: '{{ $coupon->min_spend ?? '' }}',
                                            max_discount: '{{ $coupon->max_discount ?? '' }}',
                                            max_uses: '{{ $coupon->max_uses ?? '' }}',
                                            starts_at: '{{ $coupon->starts_at ? $coupon->starts_at->format('Y-m-d\TH:i') : '' }}',
                                            expires_at: '{{ $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '' }}',
                                            is_active: {{ $coupon->is_active ? 'true' : 'false' }}
                                        })"
                                        class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 hover:text-blue-600 transition">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>

                                <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Delete coupon {{ $coupon->code }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/50 text-slate-400 hover:text-rose-600 transition">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400">
                            <i data-lucide="ticket" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                            No coupons created yet. Click "Create New Coupon" to start.
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
    <div x-show="createModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.self="createModal = false" class="fixed inset-0"></div>
        <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-2xl z-10">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-extrabold text-lg text-slate-900 dark:text-white">Create Promotional Coupon</h3>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form method="POST" action="{{ route('admin.coupons.store') }}" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Coupon Code *</label>
                        <input type="text" name="code" required placeholder="e.g. EID2026, LAUNCH50"
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono font-bold uppercase text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Display Name</label>
                        <input type="text" name="name" placeholder="e.g. Eid Celebration 20% Off"
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Discount Type *</label>
                        <select name="discount_type" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (PKR / Rs)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Discount Value *</label>
                        <input type="number" step="0.01" min="0.01" name="discount_value" required placeholder="e.g. 20 or 500"
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-bold text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Applies To *</label>
                        <select name="applies_to" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                            <option value="all">All Checkouts</option>
                            <option value="packages">Subscription Packages Only</option>
                            <option value="templates">Invoice Templates Only</option>
                            <option value="invoices">Client Invoices Only</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Max Redemptions</label>
                        <input type="number" min="1" name="max_uses" placeholder="Leave empty for unlimited"
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Min Spend Required</label>
                        <input type="number" step="0.01" min="0" name="min_spend" placeholder="e.g. 1000"
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Expiry Date</label>
                        <input type="datetime-local" name="expires_at"
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Description / Campaign Notes</label>
                    <textarea name="description" rows="2" placeholder="Optional notes about this promotion..."
                              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-300 text-sm font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-sm">Save Coupon</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Coupon Modal -->
    <div x-show="editModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.self="editModal = false" class="fixed inset-0"></div>
        <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-2xl z-10">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-extrabold text-lg text-slate-900 dark:text-white">Edit Coupon</h3>
                <button @click="editModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form method="POST" :action="`{{ url('admin/coupons') }}/${currentCoupon.id}`" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Coupon Code *</label>
                        <input type="text" name="code" x-model="currentCoupon.code" required
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono font-bold uppercase text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Display Name</label>
                        <input type="text" name="name" x-model="currentCoupon.name"
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Discount Type *</label>
                        <select name="discount_type" x-model="currentCoupon.discount_type" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (PKR / Rs)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Discount Value *</label>
                        <input type="number" step="0.01" min="0.01" name="discount_value" x-model="currentCoupon.discount_value" required
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-bold text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Applies To *</label>
                        <select name="applies_to" x-model="currentCoupon.applies_to" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                            <option value="all">All Checkouts</option>
                            <option value="packages">Subscription Packages Only</option>
                            <option value="templates">Invoice Templates Only</option>
                            <option value="invoices">Client Invoices Only</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Max Redemptions</label>
                        <input type="number" min="1" name="max_uses" x-model="currentCoupon.max_uses" placeholder="Leave empty for unlimited"
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Min Spend Required</label>
                        <input type="number" step="0.01" min="0" name="min_spend" x-model="currentCoupon.min_spend"
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Expiry Date</label>
                        <input type="datetime-local" name="expires_at" x-model="currentCoupon.expires_at"
                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Description</label>
                    <textarea name="description" rows="2" x-model="currentCoupon.description"
                              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1" :checked="currentCoupon.is_active"
                           class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500">
                    <label for="edit_is_active" class="text-xs font-bold text-slate-700 dark:text-slate-300">Active and available for checkout</label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-300 text-sm font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-sm">Update Coupon</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
