@extends('layouts.app')

@section('title', 'Recurring Profile: ' . $recurring->title)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('recurring.index') }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    All Recurring Profiles
                </a>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2.5">
                <i data-lucide="refresh-cw" class="w-6 h-6 text-indigo-600 dark:text-indigo-400"></i>
                {{ $recurring->title }}
            </h2>
            <div class="flex items-center gap-3 mt-1 text-xs text-slate-500 dark:text-slate-400">
                <span>Client: <strong class="text-slate-700 dark:text-slate-300">{{ $recurring->client->name }}</strong></span>
                <span>&bull;</span>
                <span class="capitalize">Frequency: <strong class="text-slate-700 dark:text-slate-300">{{ $recurring->frequency }}</strong></span>
                <span>&bull;</span>
                <span>Next Issue: <strong class="text-slate-700 dark:text-slate-300">{{ $recurring->next_issue_date?->format('M d, Y') }}</strong></span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- 1-Click Generate Now -->
            <form action="{{ route('recurring.generate-now', $recurring) }}" method="POST" onsubmit="return confirm('Generate an invoice immediately from this recurring profile?')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm transition">
                    <i data-lucide="zap" class="w-4 h-4"></i>
                    Generate Invoice Now
                </button>
            </form>

            <!-- Toggle Pause / Resume -->
            <form action="{{ route('recurring.toggle-status', $recurring) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold transition">
                    @if($recurring->status === 'active')
                        <i data-lucide="pause" class="w-4 h-4 text-amber-500"></i>
                        Pause Profile
                    @else
                        <i data-lucide="play" class="w-4 h-4 text-emerald-500"></i>
                        Activate Profile
                    @endif
                </button>
            </form>

            <a href="{{ route('recurring.edit', $recurring) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold transition">
                <i data-lucide="edit-2" class="w-4 h-4"></i>
                Edit
            </a>

            <form action="{{ route('recurring.destroy', $recurring) }}" method="POST" onsubmit="return confirm('Delete this recurring profile?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-rose-200 dark:border-rose-900 bg-rose-50/50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 hover:bg-rose-100 text-xs font-semibold transition">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Status & Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Status</span>
            <div class="mt-1 flex items-center gap-2">
                @if($recurring->status === 'active')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Active
                    </span>
                @elseif($recurring->status === 'paused')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Paused
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                        Completed
                    </span>
                @endif
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Billing Amount</span>
            <span class="text-xl font-extrabold text-slate-900 dark:text-white mt-1 block">
                {{ $recurring->currency }} {{ number_format($recurring->total, 2) }}
            </span>
            <span class="text-[11px] text-slate-400">Per {{ rtrim($recurring->frequency, 'ly') }} cycle</span>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Next Issue Date</span>
            <span class="text-base font-bold text-slate-900 dark:text-white mt-1 block">
                {{ $recurring->next_issue_date?->format('M d, Y') }}
            </span>
            <span class="text-[11px] text-indigo-600 dark:text-indigo-400 font-semibold">{{ $recurring->next_issue_date?->diffForHumans() }}</span>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Invoices Created</span>
            <span class="text-xl font-extrabold text-purple-600 dark:text-purple-400 mt-1 block">
                {{ $recurring->invoices_generated_count }}
            </span>
            <span class="text-[11px] text-slate-400">Last: {{ $recurring->last_generated_at ? $recurring->last_generated_at->format('M d, Y') : 'Never' }}</span>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content: Line Items & Totals -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Line Items Table -->
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
                    <i data-lucide="layers" class="w-4 h-4 text-indigo-500"></i>
                    Configured Line Items
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-2.5">Description</th>
                                <th class="py-2.5 text-right">Qty</th>
                                <th class="py-2.5 text-right">Price</th>
                                <th class="py-2.5 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($recurring->items as $item)
                            <tr>
                                <td class="py-3 font-medium text-slate-800 dark:text-slate-200">{{ $item->description }}</td>
                                <td class="py-3 text-right text-slate-600 dark:text-slate-400">{{ (float)$item->quantity }}</td>
                                <td class="py-3 text-right text-slate-600 dark:text-slate-400">{{ $recurring->currency }} {{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-3 text-right font-semibold text-slate-900 dark:text-white">{{ $recurring->currency }} {{ number_format($item->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Financial Calculation Summary -->
                <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <div class="w-72 space-y-2 text-xs sm:text-sm">
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Subtotal</span>
                            <span class="font-semibold text-slate-900 dark:text-white">{{ $recurring->currency }} {{ number_format($recurring->subtotal, 2) }}</span>
                        </div>
                        @if($recurring->discount_amount > 0)
                        <div class="flex justify-between text-rose-600">
                            <span>Discount ({{ (float)$recurring->discount_rate }}%)</span>
                            <span>- {{ $recurring->currency }} {{ number_format($recurring->discount_amount, 2) }}</span>
                        </div>
                        @endif
                        @if($recurring->tax_amount > 0)
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Tax ({{ (float)$recurring->tax_rate }}%)</span>
                            <span>+ {{ $recurring->currency }} {{ number_format($recurring->tax_amount, 2) }}</span>
                        </div>
                        @endif
                        @if(!empty($recurring->additional_charges))
                            @foreach($recurring->additional_charges as $charge)
                            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                <span>{{ $charge['name'] }}</span>
                                <span>+ {{ $recurring->currency }} {{ number_format($charge['amount'], 2) }}</span>
                            </div>
                            @endforeach
                        @endif
                        <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex justify-between font-extrabold text-sm text-slate-900 dark:text-white">
                            <span>Total Per Invoice</span>
                            <span class="text-indigo-600 dark:text-indigo-400 text-base">{{ $recurring->currency }} {{ number_format($recurring->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoices Generated from this Profile -->
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                        <i data-lucide="history" class="w-4 h-4 text-indigo-500"></i>
                        Generated Invoices ({{ $recurring->invoices->count() }})
                    </h3>
                </div>

                @if($recurring->invoices->count() > 0)
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($recurring->invoices as $inv)
                    <div class="py-3 flex items-center justify-between gap-4">
                        <div>
                            <a href="{{ route('invoices.show', $inv) }}" class="font-bold text-sm text-slate-900 dark:text-white hover:text-indigo-600">
                                #{{ $inv->invoice_number }}
                            </a>
                            <div class="text-xs text-slate-400">
                                Issued: {{ $inv->invoice_date?->format('M d, Y') }} &bull; Due: {{ $inv->due_date?->format('M d, Y') }}
                            </div>
                        </div>

                        <div class="flex items-center gap-4 text-right">
                            <div>
                                <div class="font-extrabold text-sm text-slate-900 dark:text-white">
                                    {{ $inv->currency }} {{ number_format($inv->total, 2) }}
                                </div>
                                @if(($inv->amount_paid ?? 0) > 0 && !$inv->isPaid())
                                    <div class="text-[11px] text-amber-600">Paid: {{ $inv->currency }} {{ number_format($inv->amount_paid, 2) }}</div>
                                @endif
                            </div>

                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize bg-{{ $inv->status_color }}-50 text-{{ $inv->status_color }}-700 dark:bg-{{ $inv->status_color }}-950/40 dark:text-{{ $inv->status_color }}-400 border border-{{ $inv->status_color }}-200 dark:border-{{ $inv->status_color }}-800/40">
                                {{ str_replace('_', ' ', $inv->status) }}
                            </span>

                            <a href="{{ route('invoices.show', $inv) }}" class="p-1 text-slate-400 hover:text-indigo-600">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-8 text-center text-slate-400 text-xs">
                    No invoices generated yet. Click "Generate Invoice Now" above to trigger the first invoice manually, or wait for the automated schedule.
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar: Client & Configuration -->
        <div class="space-y-6">
            <!-- Client Card -->
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Client Information</span>
                <div class="font-bold text-base text-slate-900 dark:text-white">{{ $recurring->client->name }}</div>
                @if($recurring->client->company_name)
                    <div class="text-xs text-slate-500">{{ $recurring->client->company_name }}</div>
                @endif
                <div class="text-xs text-slate-500 flex items-center gap-1.5 mt-2">
                    <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                    {{ $recurring->client->email ?: 'No email provided' }}
                </div>
                @if($recurring->client->phone)
                <div class="text-xs text-slate-500 flex items-center gap-1.5">
                    <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                    {{ $recurring->client->phone }}
                </div>
                @endif
            </div>

            <!-- Profile Settings Card -->
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3 text-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Recurrence Settings</span>
                
                <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500">Frequency:</span>
                    <span class="font-bold capitalize text-slate-800 dark:text-slate-200">{{ $recurring->frequency }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500">Start Date:</span>
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $recurring->start_date?->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500">End Date:</span>
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $recurring->end_date ? $recurring->end_date->format('M d, Y') : 'Never (Indefinite)' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500">Payment Terms:</span>
                    <span class="font-medium text-slate-800 dark:text-slate-200">Due in {{ $recurring->due_days }} days</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500">Auto-Email Client:</span>
                    <span class="font-bold {{ $recurring->auto_send_email ? 'text-emerald-600' : 'text-slate-400' }}">
                        {{ $recurring->auto_send_email ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-500">Design Template:</span>
                    <span class="font-bold capitalize text-indigo-600">{{ $recurring->style }}</span>
                </div>
            </div>

            <!-- Notes & Instructions -->
            @if($recurring->notes || $recurring->payment_instructions)
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4 text-xs">
                @if($recurring->notes)
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Notes to Client</span>
                    <p class="text-slate-600 dark:text-slate-300 whitespace-pre-line">{{ $recurring->notes }}</p>
                </div>
                @endif

                @if($recurring->payment_instructions)
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Payment Instructions</span>
                    <p class="text-slate-600 dark:text-slate-300 whitespace-pre-line">{{ $recurring->payment_instructions }}</p>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection