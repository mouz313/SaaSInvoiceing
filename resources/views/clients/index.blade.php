@extends('layouts.app')

@section('title', 'Clients Management')

@section('content')
<div class="space-y-6">
    <!-- Top Action & Search Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">Client Profiles</h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Manage billing addresses and customer contact information</p>
        </div>

        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('clients.index') }}" class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search clients..." 
                       class="w-48 sm:w-64 pl-9 pr-4 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
            </form>

            <a href="{{ route('clients.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-sm transition">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Add Client
            </a>
        </div>
    </div>

    <!-- Clients Table Card -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        @if($clients->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <h4 class="font-bold text-slate-800 dark:text-slate-200 text-base">No clients found</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                    @if($search)
                        No clients matched "{{ $search }}". Try a different query.
                    @else
                        Add your first client profile to start issuing professional invoices.
                    @endif
                </p>
                <a href="{{ route('clients.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Client Now
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5">Client Name</th>
                            <th class="px-6 py-3.5">Company</th>
                            <th class="px-6 py-3.5">Contact Info</th>
                            <th class="px-6 py-3.5">Location</th>
                            <th class="px-6 py-3.5">Invoices</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($clients as $client)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $client->name }}</div>
                                @if($client->tax_id)
                                    <div class="text-[11px] text-slate-400">TAX: {{ $client->tax_id }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                                {{ $client->company_name ?: '—' }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                                <div>{{ $client->email ?: 'No email' }}</div>
                                @if($client->phone)
                                    <div class="text-xs text-slate-400">{{ $client->phone }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300 text-xs">
                                {{ $client->city ? $client->city.', ' : '' }}{{ $client->country }}
                            </td>
                            <td class="px-6 py-4 font-bold text-blue-600 dark:text-blue-400">
                                {{ $client->invoices_count }} invoices
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('clients.show', $client) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="View Client Profile">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('clients.statement', $client) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 transition" title="Account Statement">
                                        <i data-lucide="file-text" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('clients.edit', $client) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Edit Client">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Are you sure you want to delete this client?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition" title="Delete Client">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">
                {{ $clients->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
