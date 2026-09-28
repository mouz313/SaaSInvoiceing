@extends('layouts.app')

@section('title', 'Team & Multi-User Roles')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto" x-data="{ inviteModal: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-950 dark:text-white tracking-tight flex items-center gap-2">
                <i data-lucide="users-round" class="w-7 h-7 text-indigo-600"></i>
                Team & Multi-User Roles
            </h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                Collaborate with your accountants, billing clerks, and financial officers with granular permission levels.
            </p>
        </div>
        <div>
            <button @click="inviteModal = true" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm shadow-indigo-500/20 inline-flex items-center gap-1.5 transition">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Invite Member
            </button>
        </div>
    </div>

    <!-- Role Explanation Badges -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-1.5">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 font-bold text-xs">Admin</span>
            </div>
            <p class="text-xs text-slate-600 dark:text-slate-400">
                Full authority to manage clients, templates, invoices, payments, and company settings.
            </p>
        </div>

        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-1.5">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 font-bold text-xs">Accountant</span>
            </div>
            <p class="text-xs text-slate-600 dark:text-slate-400">
                Can issue invoices, record payments, and reconcile bank statements. Cannot alter company banking or delete accounts.
            </p>
        </div>

        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-1.5">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 font-bold text-xs">Viewer</span>
            </div>
            <p class="text-xs text-slate-600 dark:text-slate-400">
                Read-only inspection for audit partners, tax consultants, and external stakeholders.
            </p>
        </div>
    </div>

    <!-- Team Members Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-950 dark:text-white">Active Team Members ({{ $members->count() + 1 }})</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3 px-6">Member</th>
                        <th class="py-3 px-6">Email</th>
                        <th class="py-3 px-6">Role</th>
                        <th class="py-3 px-6">Status</th>
                        <th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <!-- Owner Row -->
                    <tr class="bg-slate-50/30 dark:bg-slate-800/20">
                        <td class="py-4 px-6 font-bold text-slate-900 dark:text-white flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-black flex items-center justify-center text-xs">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div>
                                <span>{{ Auth::user()->name }}</span>
                                <span class="block text-[10px] text-slate-500 font-normal">Primary Account Holder</span>
                            </div>
                        </td>
                        <td class="py-4 px-6 font-mono text-slate-600 dark:text-slate-400">{{ Auth::user()->email }}</td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300 font-bold text-[10px] uppercase tracking-wider">
                                Account Owner
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center gap-1.5 text-emerald-600 font-bold">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right text-slate-400 italic">Owner</td>
                    </tr>

                    @forelse($members as $member)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition">
                            <td class="py-4 px-6 font-bold text-slate-900 dark:text-white flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold flex items-center justify-center text-xs">
                                    {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                </div>
                                <span>{{ $member->user->name }}</span>
                            </td>
                            <td class="py-4 px-6 font-mono text-slate-600 dark:text-slate-400">{{ $member->user->email }}</td>
                            <td class="py-4 px-6">
                                <form method="POST" action="{{ route('teams.update', $member) }}" class="inline-block">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" onchange="this.form.submit()" class="px-2.5 py-1 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                                        <option value="admin" {{ $member->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="accountant" {{ $member->role === 'accountant' ? 'selected' : '' }}>Accountant</option>
                                        <option value="viewer" {{ $member->role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                    </select>
                                </form>
                            </td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center gap-1.5 text-emerald-600 font-bold">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <form method="POST" action="{{ route('teams.destroy', $member) }}" onsubmit="return confirm('Remove this user from your team?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg font-bold transition">
                                        Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 px-6 text-center text-slate-500">
                                No additional team members added. Invite accountants or colleagues to help manage your billing.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Invite Member Modal -->
    <div x-show="inviteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 space-y-4 border border-slate-200 dark:border-slate-800 shadow-xl" @click.outside="inviteModal = false">
            <h3 class="text-lg font-bold text-slate-950 dark:text-white">Invite Team Member</h3>
            <form method="POST" action="{{ route('teams.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Full Name</label>
                    <input type="text" name="name" required placeholder="e.g. Asad Siddiqui" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                    <input type="email" name="email" required placeholder="e.g. asad@company.pk" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Role / Permissions</label>
                    <select name="role" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                        <option value="accountant">Accountant (Invoices, Payments, Bank Reconciliation)</option>
                        <option value="admin">Admin (Full Access & Settings)</option>
                        <option value="viewer">Viewer (Read-Only Audit)</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="inviteModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold">
                        Add to Team
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
