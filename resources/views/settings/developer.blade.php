@extends('layouts.app')

@section('title', 'Developer API & Webhooks')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto" x-data="{ createKeyModal: false, createWebhookModal: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-950 dark:text-white tracking-tight flex items-center gap-2">
                <i data-lucide="terminal" class="w-7 h-7 text-blue-600"></i>
                Developer API & Webhooks
            </h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                Integrate external SaaS platforms, POS systems, accounting software, and automate real-time billing events.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <button @click="createKeyModal = true" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm shadow-blue-500/20 inline-flex items-center gap-1.5 transition">
                <i data-lucide="key" class="w-4 h-4"></i>
                Generate API Key
            </button>
            <button @click="createWebhookModal = true" class="px-4 py-2 rounded-xl bg-slate-900 dark:bg-white hover:bg-slate-800 dark:hover:bg-slate-100 text-white dark:text-slate-900 font-bold text-xs shadow-sm inline-flex items-center gap-1.5 transition">
                <i data-lucide="webhook" class="w-4 h-4"></i>
                Add Webhook
            </button>
        </div>
    </div>

    <!-- Freshly Generated API Key Alert Banner -->
    @if(session('new_api_key'))
        <div class="p-5 rounded-2xl bg-amber-50 dark:bg-amber-950/60 border-2 border-amber-400 dark:border-amber-600 text-amber-900 dark:text-amber-200 space-y-3 shadow-md">
            <div class="flex items-center gap-2 font-black text-base">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 dark:text-amber-400"></i>
                <span>Save your new API Key immediately!</span>
            </div>
            <p class="text-xs">
                For security reasons, this key will <strong>never be shown again</strong>. Please copy it and store it in a secure environment file.
            </p>
            <div class="flex items-center gap-2 bg-white dark:bg-slate-900 p-2.5 rounded-xl border border-amber-300 dark:border-amber-700 max-w-2xl font-mono text-sm select-all">
                <input type="text" readonly value="{{ session('new_api_key') }}" id="new-key-input" class="w-full bg-transparent border-0 outline-none text-slate-900 dark:text-white font-mono text-xs sm:text-sm">
                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('new-key-input').value); alert('API Key copied to clipboard!');" class="px-3 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold shrink-0 transition">
                    Copy Key
                </button>
            </div>
        </div>
    @endif

    <!-- API Keys Panel -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center text-blue-600">
                    <i data-lucide="key-round" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-950 dark:text-white">Active API Keys</h2>
                    <p class="text-xs text-slate-500">Bearer tokens used to authenticate requests to the REST API.</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3 px-6">Name</th>
                        <th class="py-3 px-6">Key Prefix</th>
                        <th class="py-3 px-6">Created</th>
                        <th class="py-3 px-6">Last Used</th>
                        <th class="py-3 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($apiKeys as $key)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition">
                            <td class="py-4 px-6 font-bold text-slate-900 dark:text-white">{{ $key->name }}</td>
                            <td class="py-4 px-6 font-mono text-slate-500">{{ $key->key_prefix }}••••••••••••••••••••</td>
                            <td class="py-4 px-6 text-slate-600 dark:text-slate-400">{{ $key->created_at->format('M d, Y') }}</td>
                            <td class="py-4 px-6 text-slate-600 dark:text-slate-400">{{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Never' }}</td>
                            <td class="py-4 px-6 text-right">
                                <form method="POST" action="{{ route('settings.api-keys.destroy', $key) }}" onsubmit="return confirm('Are you sure you want to revoke this API key? Any integration using it will immediately stop working.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg font-bold transition">
                                        Revoke
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 px-6 text-center text-slate-500">
                                No API keys generated yet. Click "Generate API Key" to create your first credential.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Webhooks Panel -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/50 flex items-center justify-center text-purple-600">
                    <i data-lucide="radio" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-950 dark:text-white">Registered Webhooks</h2>
                    <p class="text-xs text-slate-500">Receive real-time notifications for payment settlements and invoice lifecycle changes.</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3 px-6">Endpoint URL</th>
                        <th class="py-3 px-6">Subscribed Events</th>
                        <th class="py-3 px-6">Secret Key</th>
                        <th class="py-3 px-6">Last Triggered</th>
                        <th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($webhooks as $webhook)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition">
                            <td class="py-4 px-6 font-mono text-slate-900 dark:text-white max-w-xs truncate">{{ $webhook->url }}</td>
                            <td class="py-4 px-6">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($webhook->events as $ev)
                                        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-mono font-bold text-slate-700 dark:text-slate-300">
                                            {{ $ev }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-4 px-6 font-mono text-slate-500 text-[11px]">{{ substr($webhook->secret, 0, 10) }}••••••••</td>
                            <td class="py-4 px-6 text-slate-600 dark:text-slate-400">{{ $webhook->last_triggered_at ? $webhook->last_triggered_at->diffForHumans() : 'Never' }}</td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <form method="POST" action="{{ route('settings.webhooks.ping', $webhook) }}">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/60 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-bold transition">
                                            Test Ping
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('settings.webhooks.destroy', $webhook) }}" onsubmit="return confirm('Delete this webhook endpoint?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg font-bold transition">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 px-6 text-center text-slate-500">
                                No webhooks configured. Click "Add Webhook" to receive automated webhook events.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- REST API Quick Start Guide -->
    <div class="bg-slate-900 text-slate-100 rounded-2xl p-6 sm:p-8 space-y-5 border border-slate-800 shadow-md">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-600/20 text-blue-400 flex items-center justify-center">
                <i data-lucide="code-2" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-white">Public REST API V1 Quick Reference</h3>
                <p class="text-xs text-slate-400">All requests require <code>Authorization: Bearer ih_live_...</code> header.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs text-slate-400">
                    <span class="font-bold text-slate-300">1. List Invoices: GET /api/v1/invoices</span>
                </div>
                <pre class="bg-black/50 p-4 rounded-xl text-xs font-mono text-emerald-400 overflow-x-auto border border-white/5">curl -X GET "{{ url('/api/v1/invoices') }}" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Accept: application/json"</pre>
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs text-slate-400">
                    <span class="font-bold text-slate-300">2. Create Invoice: POST /api/v1/invoices</span>
                </div>
                <pre class="bg-black/50 p-4 rounded-xl text-xs font-mono text-blue-400 overflow-x-auto border border-white/5">curl -X POST "{{ url('/api/v1/invoices') }}" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"client_name":"Tech Corp PK","invoice_date":"2026-09-25","due_date":"2026-10-02","items":[{"description":"Consulting","quantity":1,"unit_price":25000}]}'</pre>
            </div>
        </div>
    </div>

    <!-- Create API Key Modal -->
    <div x-show="createKeyModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 space-y-4 border border-slate-200 dark:border-slate-800 shadow-xl" @click.outside="createKeyModal = false">
            <h3 class="text-lg font-bold text-slate-950 dark:text-white">Generate New API Key</h3>
            <form method="POST" action="{{ route('settings.api-keys.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Key Name / Identifier</label>
                    <input type="text" name="name" required placeholder="e.g. Production Backend, POS Terminal, Zapier" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="createKeyModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">
                        Generate Key
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Webhook Modal -->
    <div x-show="createWebhookModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 space-y-4 border border-slate-200 dark:border-slate-800 shadow-xl" @click.outside="createWebhookModal = false">
            <h3 class="text-lg font-bold text-slate-950 dark:text-white">Register Webhook Endpoint</h3>
            <form method="POST" action="{{ route('settings.webhooks.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payload URL</label>
                    <input type="url" name="url" required placeholder="https://api.yourdomain.com/webhooks/invoicehub" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Subscribe to Events</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                            <input type="checkbox" name="events[]" value="invoice.created" checked class="rounded text-blue-600">
                            <span><code>invoice.created</code> — When an invoice is created</span>
                        </label>
                        <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                            <input type="checkbox" name="events[]" value="invoice.paid" checked class="rounded text-blue-600">
                            <span><code>invoice.paid</code> — When an invoice is fully paid</span>
                        </label>
                        <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                            <input type="checkbox" name="events[]" value="invoice.payment_received" checked class="rounded text-blue-600">
                            <span><code>invoice.payment_received</code> — When a partial or full payment is verified</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="createWebhookModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold">
                        Register Webhook
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
