@extends('layouts.app')

@section('title', 'Log Expense')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">Log Expense</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Record a company or client-billable expense</p>
        </div>
        <a href="{{ route('expenses.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">
            &larr; Back to list
        </a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-xs">
        <form method="POST" action="{{ route('expenses.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Client (Optional)
                    </label>
                    <select name="client_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="">General / Business Overhead</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Category *
                    </label>
                    <select name="category" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                    Description *
                </label>
                <input type="text" name="description" value="{{ old('description') }}" required placeholder="e.g. Server hosting renewal, Flight ticket, Client lunch"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Amount *
                    </label>
                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required placeholder="0.00"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Currency *
                    </label>
                    <select name="currency" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        @foreach(\App\Support\Currency::all() as $code => $curr)
                            <option value="{{ $code }}" {{ old('currency', 'USD') === $code ? 'selected' : '' }}>
                                {{ $code }} ({{ $curr['symbol'] }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Expense Date *
                    </label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_billable" value="1" {{ old('is_billable', true) ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">This expense is billable to the selected client</span>
                </label>
            </div>

            <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-700">
                <a href="{{ route('expenses.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                    Save Expense
                </button>
            </div>
        </form>
    </div>
</div>
@endsection