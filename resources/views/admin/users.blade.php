@extends('layouts.admin')

@section('title', 'Users & Invoice Credits')

@section('content')
<div class="space-y-6">
    <!-- Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">Platform Users Directory</h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Inspect registered users, manage invoice credits, and toggle administrative roles</p>
        </div>

        <form method="GET" action="{{ route('admin.users') }}" class="relative">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or email..." 
                   class="w-full sm:w-64 pl-9 pr-4 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-600 focus:outline-none shadow-xs">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
        </form>
    </div>

    <!-- Table Card -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-3.5">User Profile</th>
                        <th class="px-6 py-3.5">Role</th>
                        <th class="px-6 py-3.5">Plan Tier</th>
                        <th class="px-6 py-3.5">Invoices Issued</th>
                        <th class="px-6 py-3.5">Credits Balance</th>
                        <th class="px-6 py-3.5 text-right">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @foreach($users as $user)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $user->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $user->email }}</div>
                                    @if($user->firebase_uid)
                                        <span class="inline-flex items-center gap-1 text-[10px] text-amber-600 dark:text-amber-400 font-mono mt-0.5">
                                            <i data-lucide="shield" class="w-3 h-3"></i> Firebase: {{ substr($user->firebase_uid, 0, 8) }}...
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <form method="POST" action="{{ route('admin.users.toggle-role', $user) }}" class="inline">
                                @csrf
                                <button type="submit" title="Click to toggle role" 
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase transition {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800 hover:bg-purple-200' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 hover:bg-slate-200' }}">
                                    {{ $user->role }}
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-slate-700 dark:text-slate-300">
                            {{ $user->package->name ?? 'Free Tier' }}
                        </td>
                        <td class="px-6 py-4 font-bold text-blue-600 dark:text-blue-400 text-xs">
                            {{ $user->invoices_count }} invoices
                        </td>
                        <td class="px-6 py-4">
                            <form method="POST" action="{{ route('admin.users.update-credits', $user) }}" class="flex items-center gap-2">
                                @csrf
                                <input type="number" name="credits" value="{{ $user->invoice_credits }}" min="0" 
                                       class="w-20 px-2.5 py-1 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs text-right font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition">
                                    Set
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 text-right text-xs text-slate-500 dark:text-slate-400">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
