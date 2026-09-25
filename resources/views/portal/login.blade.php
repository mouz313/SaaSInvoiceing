<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Portal Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4">
    <div class="w-full max-w-md" x-data="{ hasPassword: true }">
        <!-- Logo / Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-600 text-white font-black text-2xl shadow-lg shadow-blue-500/30 mb-3">
                CP
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Client Account Portal</h1>
            <p class="text-xs text-slate-500 mt-1">Access your invoices, proposals, receipts, and account statement</p>
        </div>

        <!-- Notification Alerts -->
        @if(session('success'))
            <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium">
                {{ session('success') }}
            </div>
        @endif
        @if(session('info'))
            <div class="mb-4 p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs font-medium">
                {{ session('info') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium">
                {{ session('error') }}
            </div>
        @endif

        <!-- Card Container -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
            <form method="POST" action="{{ route('portal.login') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Client Email Address
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               placeholder="your.email@company.com"
                               class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-medium focus:ring-2 focus:ring-blue-600 focus:border-blue-600 focus:outline-none transition">
                    </div>

                    <!-- Optional Password input -->
                    <div x-show="hasPassword" style="display: none;">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Account Password
                        </label>
                        <input type="password" name="password"
                               placeholder="••••••••"
                               class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-medium focus:ring-2 focus:ring-blue-600 focus:border-blue-600 focus:outline-none transition">
                    </div>

                    <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md shadow-blue-600/20 transition flex items-center justify-center gap-2">
                        <span x-text="hasPassword ? 'Sign In with Password' : 'Send Magic Access Link'"></span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between text-xs">
                <button type="button" @click="hasPassword = !hasPassword" class="text-blue-600 hover:text-blue-800 font-semibold">
                    <span x-text="hasPassword ? '← Sign in with magic link instead' : 'Have a password? Sign in with password →'"></span>
                </button>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('home') }}" class="text-xs text-slate-400 hover:text-slate-600 font-medium transition">
                &larr; Back to main website
            </a>
        </div>
    </div>
</body>
</html>