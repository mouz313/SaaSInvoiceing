<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sign In — InvoiceHub</title>

    @include('partials.head-assets')
</head>
<body class="h-full bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
            @if(setting('app_logo'))
                <img src="{{ Storage::url(setting('app_logo')) }}" alt="{{ setting('app_name', 'InvoiceHub') }}" class="h-12 w-auto max-w-[200px] object-contain mx-auto">
            @else
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                    <i data-lucide="receipt" class="w-6 h-6"></i>
                </div>
                <span class="font-extrabold text-2xl tracking-tight text-slate-900 dark:text-white">{{ setting('app_name', 'InvoiceHub') }}</span>
            @endif
        </a>
        <h2 class="mt-4 text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Welcome back</h2>
        <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400">Sign in to manage your clients and invoices</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4">
        <div class="bg-white dark:bg-slate-900 py-8 px-6 sm:px-10 shadow-xl border border-slate-200 dark:border-slate-800 rounded-3xl space-y-6">

            <!-- 1-Click Fast Sandbox Demo Logins -->
            <div class="p-3.5 rounded-2xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-900/60">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-800 dark:text-blue-300 flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Fast Demo Logins
                    </span>
                    <span class="text-[10px] text-blue-600 dark:text-blue-400 font-medium">Instant Access</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('auth.demo-login', 'user') }}" class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition">
                        <i data-lucide="user" class="w-3.5 h-3.5"></i> Demo User
                    </a>
                    <a href="{{ route('auth.demo-login', 'admin') }}" class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-xs transition">
                        <i data-lucide="shield" class="w-3.5 h-3.5"></i> Super Admin
                    </a>
                </div>
            </div>

            <!-- Google Sign-In Button -->
            <div>
                <button type="button" id="google-auth-btn" class="w-full flex items-center justify-center gap-3 px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold text-sm hover:bg-slate-50 dark:hover:bg-slate-750 transition shadow-xs">
                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                    Continue with Google
                </button>
            </div>

            <div class="relative flex items-center justify-center">
                <div class="border-t border-slate-200 dark:border-slate-800 w-full"></div>
                <span class="bg-white dark:bg-slate-900 px-3 text-xs uppercase tracking-wider text-slate-400 font-semibold absolute">Or with email</span>
            </div>

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Email Address
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@company.com"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    @error('email') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Password
                    </label>
                    <input type="password" name="password" required placeholder="••••••••"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    @error('password') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md shadow-blue-600/20 transition">
                    Sign In
                </button>
            </form>

            <p class="text-center text-xs text-slate-500 dark:text-slate-400">
                Don't have an account? 
                <a href="{{ route('register') }}" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">Create account &rarr;</a>
            </p>
        </div>
    </div>

    <!-- Google Auth SDK Scripts -->
    <script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-auth-compat.js"></script>

    <script>
        const firebaseConfig = {
            apiKey: "{{ config('services.firebase.api_key') }}",
            authDomain: "{{ config('services.firebase.auth_domain') }}",
            projectId: "{{ config('services.firebase.project_id') }}",
            storageBucket: "{{ config('services.firebase.storage_bucket') }}",
            messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
            appId: "{{ config('services.firebase.app_id') }}",
            measurementId: "{{ config('services.firebase.measurement_id') }}"
        };

        if (firebaseConfig.apiKey) {
            firebase.initializeApp(firebaseConfig);
        }

        const googleBtn = document.getElementById('google-auth-btn');
        const btnOriginalHTML = googleBtn ? googleBtn.innerHTML : '';

        function setButtonLoading(loading) {
            if (!googleBtn) return;
            if (loading) {
                googleBtn.disabled = true;
                googleBtn.innerHTML = `
                    <svg class="animate-spin w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Signing in...`;
            } else {
                googleBtn.disabled = false;
                googleBtn.innerHTML = btnOriginalHTML;
            }
        }

        async function sendToServer(user) {
            setButtonLoading(true);
            try {
                const response = await fetch("{{ route('auth.firebase-session') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        firebase_uid: user.uid,
                        email: user.email,
                        name: user.displayName || user.email.split('@')[0],
                        avatar_url: user.photoURL || null
                    })
                });

                const data = await response.json();
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    setButtonLoading(false);
                    alert('Login failed: ' + (data.message || 'Unknown error'));
                }
            } catch (err) {
                setButtonLoading(false);
                console.error('Server session error:', err);
                alert('Could not complete sign-in. Please try again.');
            }
        }

        // Handle redirect result (fallback from popup-blocked)
        if (firebaseConfig.apiKey) {
            firebase.auth().getRedirectResult().then(result => {
                if (result && result.user) {
                    sendToServer(result.user);
                }
            }).catch(err => {
                console.error('Redirect result error:', err);
            });
        }

        googleBtn?.addEventListener('click', async () => {
            if (!firebaseConfig.apiKey) {
                alert('Google Sign-In is not configured. Please set up the API keys in Admin Settings.');
                return;
            }

            setButtonLoading(true);

            try {
                const provider = new firebase.auth.GoogleAuthProvider();
                provider.addScope('email');
                provider.addScope('profile');

                const result = await firebase.auth().signInWithPopup(provider);
                await sendToServer(result.user);
            } catch (error) {
                console.error('Google Auth Error:', error);

                if (error.code === 'auth/popup-blocked' || error.code === 'auth/popup-closed-by-user') {
                    // Fallback to redirect-based flow
                    try {
                        const provider = new firebase.auth.GoogleAuthProvider();
                        provider.addScope('email');
                        provider.addScope('profile');
                        await firebase.auth().signInWithRedirect(provider);
                        return; // Page will redirect
                    } catch (redirectErr) {
                        console.error('Redirect fallback error:', redirectErr);
                    }
                }

                setButtonLoading(false);

                const messages = {
                    'auth/popup-closed-by-user': 'Sign-in window was closed. Please try again.',
                    'auth/cancelled-popup-request': 'Sign-in was cancelled. Please try again.',
                    'auth/network-request-failed': 'Network error. Please check your connection.',
                    'auth/internal-error': 'Authentication service error. Please try again later.',
                    'auth/unauthorized-domain': 'This domain is not authorized. Please contact support.',
                };

                alert(messages[error.code] || 'Google Sign-In failed: ' + error.message);
            }
        });
    </script>
</body>
</html>
