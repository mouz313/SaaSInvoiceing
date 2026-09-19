@extends('layouts.app')

@section('title', 'Recurring Invoices (Auto-Billing)')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2.5">
                <i data-lucide="refresh-cw" class="w-6 h-6 text-indigo-600 dark:text-indigo-400"></i>
                Recurring Invoices &amp; Subscriptions
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Automate your regular client retainers, subscriptions, and scheduled billing</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('recurring.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs sm:text-sm shadow-sm transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                New Recurring Profile
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Total Profiles</span>
            <span class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1 block">{{ $stats['total'] }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block">Active Profiles</span>
            <span class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 block">{{ $stats['active'] }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 block">Paused</span>
            <span class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1 block">{{ $stats['paused'] }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400 block">Invoices Generated</span>
            <span class="text-2xl font-extrabold text-purple-600 dark:text-purple-400 mt-1 block">{{ $stats['total_generated'] }}</span>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
            <a href="{{ route('recurring.index') }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ !request('status') ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                All ({{ $stats['total'] }})
            </a>
            <a href="{{ route('recurring.index', ['status' => 'active']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ request('status') === 'active' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                Active ({{ $stats['active'] }})
            </a>
            <a href="{{ route('recurring.index', ['status' => 'paused']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ request('status') === 'paused' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                Paused ({{ $stats['paused'] }})
            </a>
            <a href="{{ route('recurring.index', ['status' => 'completed']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ request('status') === 'completed' ? 'bg-slate-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                Completed
            </a>
        </div>

        <form action="{{ route('recurring.index') }}" method="GET" class="relative min-w-[240px]">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title, client..."
                   class="w-full pl-9 pr-4 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </form>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="py-3.5 px-4 sm:px-6">Profile / Title</th>
                        <th class="py-3.5 px-4">Client</th>
                        <th class="py-3.5 px-4">Frequency</th>
                        <th class="py-3.5 px-4">Next Run</th>
                        <th class="py-3.5 px-4">Amount</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs sm:text-sm">
                    @forelse($recurringInvoices as $profile)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                        <td class="py-3.5 px-4 sm:px-6 font-semibold text-slate-900 dark:text-white">
                            <a href="{{ route('recurring.show', $profile) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-2">
                                <i data-lucide="repeat" class="w-3.5 h-3.5 text-indigo-500"></i>
                                {{ $profile->title }}
                            </a>
                            <div class="text-[11px] font-normal text-slate-400 mt-0.5">
                                {{ $profile->invoices_generated_count }} {{ Str::plural('invoice', $profile->invoices_generated_count) }} generated
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-medium text-slate-800 dark:text-slate-200">{{ $profile->client->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $profile->client->company_name ?: $profile->client->email }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 capitalize">
                                {{ $profile->frequency }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-medium text-slate-800 dark:text-slate-200">{{ $profile->next_issue_date?->format('M d, Y') }}</div>
                            @if($profile->next_issue_date && $profile->next_issue_date->isPast())
                                <span class="text-[10px] text-rose-500 font-semibold">Due to run</span>
                            @else
                                <span class="text-[10px] text-slate-400">{{ $profile->next_issue_date?->diffForHumans() }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                            {{ $profile->currency }} {{ number_format($profile->total, 2) }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if($profile->status === 'active')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Active
                                </span>
                            @elseif($profile->status === 'paused')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Paused
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                    Completed
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 sm:px-6 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <form action="{{ route('recurring.generate-now', $profile) }}" method="POST" class="inline" onsubmit="return confirm('Generate an invoice right now for this profile?')">
                                    @csrf
                                    <button type="submit" title="Generate Invoice Now" class="p-1.5 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 rounded-lg transition">
                                        <i data-lucide="zap" class="w-4 h-4"></i>
                                    </button>
                                </form>

                                <form action="{{ route('recurring.toggle-status', $profile) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" title="{{ $profile->status === 'active' ? 'Pause Schedule' : 'Activate Schedule' }}" class="p-1.5 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition">
                                        @if($profile->status === 'active')
                                            <i data-lucide="pause" class="w-4 h-4 text-amber-600"></i>
                                        @else
                                            <i data-lucide="play" class="w-4 h-4 text-emerald-600"></i>
                                        @endif
                                    </button>
                                </form>

                                <a href="{{ route('recurring.edit', $profile) }}" title="Edit Profile" class="p-1.5 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </a>

                                <form action="{{ route('recurring.destroy', $profile) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this recurring profile?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete Profile" class="p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <i data-lucide="repeat" class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2"></i>
                            <p class="font-semibold text-slate-700 dark:text-slate-300">No recurring invoice profiles yet</p>
                            <p class="text-xs text-slate-400 mt-1">Set up automated recurring billing for your regular retainers &amp; subscriptions</p>
                            <a href="{{ route('recurring.create') }}" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold text-xs hover:bg-indigo-700 transition">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                Create First Recurring Profile
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recurringInvoices->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $recurringInvoices->links() }}
        </div>
        @endif
    </div>
</div>
@endsection