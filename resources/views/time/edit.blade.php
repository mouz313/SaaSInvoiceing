@extends('layouts.app')

@section('title', 'Edit Time Entry')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">Edit Time Entry</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Update logged hours or rate</p>
        </div>
        <a href="{{ route('time.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">
            &larr; Back to list
        </a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-xs">
        <form method="POST" action="{{ route('time.update', $timeEntry) }}" class="space-y-4" x-data="{ hours: '{{ $timeEntry->hours }}', rate: '{{ $timeEntry->hourly_rate }}' }">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Client
                    </label>
                    <select name="client_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="">General / No Client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id', $timeEntry->client_id) == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Project Name
                    </label>
                    <input type="text" name="project_name" value="{{ old('project_name', $timeEntry->project_name) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                    Task Description *
                </label>
                <textarea name="task_description" rows="3" required
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">{{ old('task_description', $timeEntry->task_description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Hours Worked *
                    </label>
                    <input type="number" step="0.25" min="0.1" name="hours" x-model="hours" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Hourly Rate ($) *
                    </label>
                    <input type="number" step="0.01" min="0" name="hourly_rate" x-model="rate" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Date *
                    </label>
                    <input type="date" name="date" value="{{ old('date', $timeEntry->date->format('Y-m-d')) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
            </div>

            <div class="p-4 rounded-xl bg-blue-50 dark:bg-slate-700/50 flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-700 dark:text-blue-300">Total Calculated:</span>
                <span class="text-lg font-black text-blue-700 dark:text-white" x-text="'$' + ((parseFloat(hours) || 0) * (parseFloat(rate) || 0)).toFixed(2)"></span>
            </div>

            <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-700">
                <a href="{{ route('time.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                    Update Time Entry
                </button>
            </div>
        </form>
    </div>
</div>
@endsection