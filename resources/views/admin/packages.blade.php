@extends('layouts.admin')

@section('title', 'Pricing Packages & Tiers')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">Pricing Packages</h2>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Manage Stripe-backed packages and invoice quotas</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($packages as $pkg)
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 flex flex-col justify-between shadow-xs">
            <div>
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold text-lg text-slate-900 dark:text-white">{{ $pkg->name }}</h3>
                    @if($pkg->is_popular)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                            POPULAR
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $pkg->description }}</p>

                <div class="mt-4 flex items-baseline gap-1">
                    <span class="text-3xl font-black text-slate-900 dark:text-white">${{ number_format($pkg->price, 0) }}</span>
                    <span class="text-xs text-slate-400">/ {{ $pkg->billing_period }}</span>
                </div>

                <div class="mt-4 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 text-xs">
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Invoice Limit:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $pkg->invoice_limit === -1 ? 'Unlimited' : $pkg->invoice_limit . ' Invoices' }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Active Subscribers:</span>
                        <span class="font-bold text-blue-600 dark:text-blue-400">{{ $pkg->users_count }} users</span>
                    </div>
                </div>

                <ul class="mt-4 space-y-1.5 text-xs text-slate-600 dark:text-slate-300">
                    @if($pkg->features)
                        @foreach($pkg->features as $f)
                            <li class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-500"></i>
                                {{ $f }}
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-400">Slug: <code class="text-slate-600 dark:text-slate-300 font-mono">{{ $pkg->slug }}</code></span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $pkg->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-slate-100 text-slate-600' }}">
                    {{ $pkg->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
