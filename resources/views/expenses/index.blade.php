@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">Expenses</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Track business expenses and pass billable costs onto client invoices</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('expenses.create') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Log Expense
            </a>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Unbilled Client Expenses</span>
            <div class="mt-2 text-2xl font-black text-blue-600">
                ${{ number_format($totalUnbilled, 2) }}
            </div>
            <div class="mt-1 text-xs text-slate-400">Billable costs waiting to be added to an invoice</div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Recorded Expenses</span>
            <div class="mt-2 text-2xl font-black text-slate-900 dark:text-white">
                ${{ number_format($totalAll, 2) }}
            </div>
            <div class="mt-1 text-xs text-slate-400">All business and client expenditures logged</div>
        </div>
    </div>

    <!-- Filters & Table Card -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden">
        <!-- Filter Bar -->
        <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 bg-slate-100 dark:bg-slate-700/60 p-1 rounded-xl text-xs">
                <a href="{{ route('expenses.index', ['status' => 'all', 'category' => $category, 'client_id' => $clientId]) }}" 
                   class="px-3 py-1.5 rounded-lg font-bold transition {{ $status === 'all' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-300' }}">
                    All
                </a>
                <a href="{{ route('expenses.index', ['status' => 'unbilled', 'category' => $category, 'client_id' => $clientId]) }}" 
                   class="px-3 py-1.5 rounded-lg font-bold transition {{ $status === 'unbilled' ? 'bg-white dark:bg-slate-800 text-blue-600 shadow-xs' : 'text-slate-600 dark:text-slate-300' }}">
                    Unbilled
                </a>
                <a href="{{ route('expenses.index', ['status' => 'billed', 'category' => $category, 'client_id' => $clientId]) }}" 
                   class="px-3 py-1.5 rounded-lg font-bold transition {{ $status === 'billed' ? 'bg-white dark:bg-slate-800 text-emerald-600 shadow-xs' : 'text-slate-600 dark:text-slate-300' }}">
                    Billed
                </a>
            </div>

            <!-- Dropdown Filters -->
            <form method="GET" action="{{ route('expenses.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <select name="category" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>

                <select name="client_id" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200">
                    <option value="">All Clients</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ $clientId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-400 font-bold text-[10px] uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Client</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4 text-right">Amount</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 font-medium">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                {{ $expense->expense_date->format('M d, Y') }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                {{ $expense->client?->name ?: 'General / Business' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-[11px]">
                                    {{ $expense->category }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300 max-w-sm">
                                {{ $expense->description }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-black text-slate-900 dark:text-white">
                                {{ \App\Support\Currency::format($expense->amount, $expense->currency) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if(! $expense->is_billable)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                        NON-BILLABLE
                                    </span>
                                @elseif($expense->is_billed)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                        BILLED
                                        @if($expense->invoice)
                                            <a href="{{ route('invoices.show', $expense->invoice) }}" class="ml-1 underline">#{{ $expense->invoice->invoice_number }}</a>
                                        @endif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">
                                        UNBILLED
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('expenses.edit', $expense) }}" class="p-1.5 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-slate-100 transition" title="Edit">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this expense?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Delete">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 text-xs">
                                No expenses logged yet. Click "Log Expense" to add your first record.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-700">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</div>
@endsection