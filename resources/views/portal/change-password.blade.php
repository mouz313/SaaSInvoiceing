<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Portal Password - {{ $client->user->company_name ?: $client->user->name }}</title>
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
    <div class="w-full max-w-md" x-data="{ showPass: false, showConfirm: false }">
        <!-- Logo / Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-600 text-white font-black text-2xl shadow-lg shadow-blue-500/30 mb-3">
                {{ strtoupper(substr($client->user->company_name ?: $client->user->name, 0, 1)) }}
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                {{ $client->must_change_password ? 'Set Your Private Password' : 'Change Portal Password' }}
            </h1>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                {{ $client->must_change_password 
                    ? 'Welcome! For your account security, please create your own private password to continue.' 
                    : 'Choose a strong, memorable password for your portal account.' }}
            </p>
        </div>

        <!-- Notification Alerts -->
        @if(session('info'))
            <div class="mb-4 p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs font-medium flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium">
                {{ session('error') }}
            </div>
        @endif

        <!-- Card Container -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
            <form method="POST" action="{{ route('portal.update-password') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        New Password *
                    </label>
                    <div class="relative">
                        <input :type="showPass ? 'text' : 'password'" name="password" required autofocus
                               placeholder="Minimum 8 characters..."
                               class="w-full pl-4 pr-10 py-3 rounded-xl border border-slate-300 text-sm font-medium focus:ring-2 focus:ring-blue-600 focus:border-blue-600 focus:outline-none transition">
                        <button type="button" @click="showPass = !showPass" class="absolute right-3 top-3 text-slate-400 hover:text-slate-600">
                            <svg x-show="!showPass" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showPass" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                    @error('password') <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Confirm New Password *
                    </label>
                    <div class="relative">
                        <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required
                               placeholder="Re-type new password..."
                               class="w-full pl-4 pr-10 py-3 rounded-xl border border-slate-300 text-sm font-medium focus:ring-2 focus:ring-blue-600 focus:border-blue-600 focus:outline-none transition">
                        <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-3 text-slate-400 hover:text-slate-600">
                            <svg x-show="!showConfirm" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showConfirm" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md shadow-blue-600/20 transition flex items-center justify-center gap-2 cursor-pointer">
                        <span>Save Password &amp; Open Portal</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>

            @if(!$client->must_change_password)
                <div class="mt-4 text-center">
                    <a href="{{ route('portal.dashboard') }}" class="text-xs text-slate-500 hover:text-slate-800 font-semibold">
                        &larr; Cancel and return to Dashboard
                    </a>
                </div>
            @endif
        </div>

        <div class="text-center mt-6">
            <form method="POST" action="{{ route('portal.logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-xs text-slate-400 hover:text-rose-600 font-medium transition">
                    Log out of portal
                </button>
            </form>
        </div>
    </div>
</body>
</html>
