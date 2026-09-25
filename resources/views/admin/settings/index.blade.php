@extends('layouts.admin')

@section('title', 'System & Integration Settings')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12" 
     x-data="{ currentTab: '{{ $activeTab }}' }" 
     x-init="$watch('currentTab', () => $nextTick(() => { if (typeof lucide !== 'undefined') { lucide.createIcons(); } }))">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">System Settings & Integrations</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Configure global application branding, social media links, Stripe payments, and Firebase authentication.</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-2 overflow-x-auto">
        <button type="button" @click="currentTab = 'general'" :class="currentTab === 'general' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0">
            <i data-lucide="sliders" class="w-4 h-4"></i>
            <span>General & Branding</span>
        </button>

        <button type="button" @click="currentTab = 'social'" :class="currentTab === 'social' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0">
            <i data-lucide="share-2" class="w-4 h-4"></i>
            <span>Social Media</span>
        </button>

        <button type="button" @click="currentTab = 'stripe'" :class="currentTab === 'stripe' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0">
            <i data-lucide="credit-card" class="w-4 h-4"></i>
            <span>Stripe Payments</span>
        </button>

        <button type="button" @click="currentTab = 'firebase'" :class="currentTab === 'firebase' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0">
            <i data-lucide="flame" class="w-4 h-4 text-amber-400"></i>
            <span>Firebase & Google OAuth</span>
        </button>

        <button type="button" @click="currentTab = 'cron'" :class="currentTab === 'cron' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0">
            <i data-lucide="clock" class="w-4 h-4 text-emerald-400"></i>
            <span>Cron & Automations</span>
            @if(!empty($grouped['cron']['cron_last_heartbeat_at']) && \Carbon\Carbon::parse($grouped['cron']['cron_last_heartbeat_at'])->diffInMinutes(now()) <= 5)
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" title="Active Scheduler"></span>
            @endif
        </button>
    </div>

    <!-- TAB 1: General & Branding -->
    <div x-show="currentTab === 'general'" x-cloak class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="building" class="w-5 h-5 text-blue-600"></i>
                General Identity & Contact Information
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">These values are displayed across the public footer, contact page, and outgoing notifications.</p>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="group" value="general">

            <!-- Brand Visuals: Logo & Favicon -->
            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 space-y-4">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                        <i data-lucide="image" class="w-4 h-4 text-blue-600"></i>
                        Brand Visuals & Icons
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Upload custom brand identity assets for headers and browser tabs</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-1">
                    <!-- Website Logo -->
                    <div class="space-y-3 bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Website Logo
                        </label>

                        <div class="flex items-center gap-4">
                            @if(!empty($grouped['general']['app_logo']))
                                <div class="h-14 px-3 py-2 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-center shrink-0">
                                    <img src="{{ Storage::url($grouped['general']['app_logo']) }}" alt="Current Logo" class="max-h-10 max-w-[140px] object-contain">
                                </div>
                            @else
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white shrink-0 shadow-sm shadow-blue-500/20">
                                    <i data-lucide="receipt" class="w-6 h-6"></i>
                                </div>
                            @endif

                            <div class="text-xs flex-1">
                                @if(!empty($grouped['general']['app_logo']))
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold block">✓ Custom logo active</span>
                                    <label class="inline-flex items-center gap-1.5 mt-1 text-rose-600 hover:text-rose-700 cursor-pointer">
                                        <input type="checkbox" name="remove_logo" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                                        <span>Remove & reset to default</span>
                                    </label>
                                @else
                                    <span class="text-slate-400 italic">Default icon logo in use</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <input type="file" name="logo" accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml"
                                   class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-950 dark:file:text-blue-300 hover:file:bg-blue-100 cursor-pointer">
                            <p class="text-[11px] text-slate-400 mt-1">Recommended: Transparent PNG or SVG (max 2MB, height: 40-60px)</p>
                            @error('logo') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Browser Favicon -->
                    <div class="space-y-3 bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Browser Favicon
                        </label>

                        <div class="flex items-center gap-4">
                            @if(!empty($grouped['general']['app_favicon']))
                                <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-center shrink-0">
                                    <img src="{{ Storage::url($grouped['general']['app_favicon']) }}" alt="Current Favicon" class="w-6 h-6 object-contain">
                                </div>
                            @else
                                <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700 flex items-center justify-center shrink-0">
                                    <i data-lucide="globe" class="w-6 h-6"></i>
                                </div>
                            @endif

                            <div class="text-xs flex-1">
                                @if(!empty($grouped['general']['app_favicon']))
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold block">✓ Custom favicon active</span>
                                    <label class="inline-flex items-center gap-1.5 mt-1 text-rose-600 hover:text-rose-700 cursor-pointer">
                                        <input type="checkbox" name="remove_favicon" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                                        <span>Remove & reset to default</span>
                                    </label>
                                @else
                                    <span class="text-slate-400 italic">No custom favicon uploaded</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <input type="file" name="favicon" accept=".ico,image/png,image/svg+xml,image/x-icon,image/jpeg"
                                   class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-950 dark:file:text-blue-300 hover:file:bg-blue-100 cursor-pointer">
                            <p class="text-[11px] text-slate-400 mt-1">Recommended: 32x32px or 64x64px ICO, PNG, or SVG (max 1MB)</p>
                            @error('favicon') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Application Name</label>
                    <input type="text" name="app_name" value="{{ old('app_name', $grouped['general']['app_name']) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Official Support Email</label>
                    <input type="email" name="support_email" value="{{ old('support_email', $grouped['general']['support_email']) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Support Hotline / Phone</label>
                    <input type="text" name="support_phone" value="{{ old('support_phone', $grouped['general']['support_phone']) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Footer Copyright Text</label>
                    <input type="text" name="copyright_text" value="{{ old('copyright_text', $grouped['general']['copyright_text']) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Office / Headquarters Address</label>
                <textarea name="office_address" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">{{ old('office_address', $grouped['general']['office_address']) }}</textarea>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save General Settings</span>
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 2: Social Media Links -->
    <div x-show="currentTab === 'social'" x-cloak class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="share-2" class="w-5 h-5 text-blue-600"></i>
                Social Media Channels
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Links entered here will appear automatically in the public footer icons.</p>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="group" value="social">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Twitter / X URL</label>
                    <input type="url" name="social_twitter" value="{{ old('social_twitter', $grouped['social']['social_twitter']) }}" placeholder="https://twitter.com/..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">LinkedIn URL</label>
                    <input type="url" name="social_linkedin" value="{{ old('social_linkedin', $grouped['social']['social_linkedin']) }}" placeholder="https://linkedin.com/company/..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">GitHub URL</label>
                    <input type="url" name="social_github" value="{{ old('social_github', $grouped['social']['social_github']) }}" placeholder="https://github.com/..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Facebook URL</label>
                    <input type="url" name="social_facebook" value="{{ old('social_facebook', $grouped['social']['social_facebook']) }}" placeholder="https://facebook.com/..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Instagram URL</label>
                    <input type="url" name="social_instagram" value="{{ old('social_instagram', $grouped['social']['social_instagram']) }}" placeholder="https://instagram.com/..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">YouTube URL</label>
                    <input type="url" name="social_youtube" value="{{ old('social_youtube', $grouped['social']['social_youtube']) }}" placeholder="https://youtube.com/@..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Social Links</span>
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 3: Stripe Settings -->
    <div x-show="currentTab === 'stripe'" x-cloak class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="credit-card" class="w-5 h-5 text-emerald-500"></i>
                Stripe Payment Gateway
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage your Stripe API credentials for subscription checkout and webhook verification.</p>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="group" value="stripe">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Stripe Mode</label>
                    <select name="stripe_mode" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold">
                        <option value="test" {{ old('stripe_mode', $grouped['stripe']['stripe_mode']) === 'test' ? 'selected' : '' }}>Test Sandbox (pk_test_ / sk_test_)</option>
                        <option value="live" {{ old('stripe_mode', $grouped['stripe']['stripe_mode']) === 'live' ? 'selected' : '' }}>Live Production (pk_live_ / sk_live_)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Settlement Currency</label>
                    <input type="text" name="stripe_currency" value="{{ old('stripe_currency', $grouped['stripe']['stripe_currency']) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono uppercase">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Stripe Publishable Key *</label>
                <input type="text" name="stripe_publishable_key" value="{{ old('stripe_publishable_key', $grouped['stripe']['stripe_publishable_key']) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Stripe Secret Key *</label>
                <input type="password" name="stripe_secret_key" value="{{ old('stripe_secret_key', $grouped['stripe']['stripe_secret_key']) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Stripe Webhook Secret</label>
                <input type="password" name="stripe_webhook_secret" value="{{ old('stripe_webhook_secret', $grouped['stripe']['stripe_webhook_secret']) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono">
                <p class="text-[11px] text-slate-400 mt-1">Webhook endpoint URL: <code class="text-blue-500 font-mono">{{ url('/webhook/stripe') }}</code></p>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Stripe Keys</span>
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 4: Firebase Configuration -->
    <div x-show="currentTab === 'firebase'" x-cloak class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="flame" class="w-5 h-5 text-amber-500"></i>
                Firebase & Google Authentication
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Configure the Web SDK parameters for Google 1-tap sign-in and cloud session syncing.</p>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="group" value="firebase">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">API Key *</label>
                    <input type="text" name="firebase_api_key" value="{{ old('firebase_api_key', $grouped['firebase']['firebase_api_key']) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Auth Domain *</label>
                    <input type="text" name="firebase_auth_domain" value="{{ old('firebase_auth_domain', $grouped['firebase']['firebase_auth_domain']) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Project ID *</label>
                    <input type="text" name="firebase_project_id" value="{{ old('firebase_project_id', $grouped['firebase']['firebase_project_id']) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Storage Bucket</label>
                    <input type="text" name="firebase_storage_bucket" value="{{ old('firebase_storage_bucket', $grouped['firebase']['firebase_storage_bucket']) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Messaging Sender ID</label>
                    <input type="text" name="firebase_messaging_sender_id" value="{{ old('firebase_messaging_sender_id', $grouped['firebase']['firebase_messaging_sender_id']) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">App ID *</label>
                    <input type="text" name="firebase_app_id" value="{{ old('firebase_app_id', $grouped['firebase']['firebase_app_id']) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Measurement ID (Google Analytics)</label>
                <input type="text" name="firebase_measurement_id" value="{{ old('firebase_measurement_id', $grouped['firebase']['firebase_measurement_id']) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono">
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Firebase Config</span>
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 5: Cron & Automations -->
    <div x-show="currentTab === 'cron'" x-cloak class="space-y-6">

        <!-- Top Header & Global Run Trigger -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">Cron Jobs & System Automation</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Automate recurring invoices generation, status synchronization, and multi-tier email reminders.</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <form action="{{ route('admin.settings.cron.run') }}" method="POST">
                    @csrf
                    <input type="hidden" name="task" value="all">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                        <i data-lucide="play-circle" class="w-4 h-4"></i>
                        <span>Run All Scheduled Tasks Now</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Cron Health & Heartbeat Status Card -->
        @php
            $heartbeat = $grouped['cron']['cron_last_heartbeat_at'];
            $isHeartbeatActive = !empty($heartbeat) && \Carbon\Carbon::parse($heartbeat)->diffInMinutes(now()) <= 15;
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Scheduler Health</span>
                    @if($isHeartbeatActive)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Active
                        </span>
                    @elseif(!empty($heartbeat))
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            Idle / Waiting
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                            Awaiting Pulse
                        </span>
                    @endif
                </div>
                <div class="mt-4">
                    <div class="text-xs text-slate-500 dark:text-slate-400">Last Scheduler Pulse:</div>
                    <div class="text-sm font-bold text-slate-900 dark:text-white mt-0.5">
                        {{ !empty($heartbeat) ? \Carbon\Carbon::parse($heartbeat)->diffForHumans() : 'No heartbeat detected yet' }}
                    </div>
                    @if(!empty($heartbeat))
                        <div class="text-[11px] font-mono text-slate-400 mt-1">{{ $heartbeat }}</div>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Server Clock</span>
                    <i data-lucide="globe" class="w-4 h-4 text-blue-500"></i>
                </div>
                <div class="mt-4">
                    <div class="text-xs text-slate-500 dark:text-slate-400">App Timezone ({{ config('app.timezone') }}):</div>
                    <div class="text-sm font-bold text-slate-900 dark:text-white mt-0.5">{{ now()->format('Y-m-d H:i:s') }}</div>
                    <div class="text-[11px] text-slate-400 mt-1">UTC: {{ now()->setTimezone('UTC')->format('Y-m-d H:i:s') }}</div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Environment</span>
                    <i data-lucide="server" class="w-4 h-4 text-purple-500"></i>
                </div>
                <div class="mt-4">
                    <div class="text-xs text-slate-500 dark:text-slate-400">PHP Version:</div>
                    <div class="text-sm font-bold text-slate-900 dark:text-white mt-0.5">PHP {{ PHP_VERSION }}</div>
                    <div class="text-[11px] text-slate-400 mt-1">Laravel {{ app()->version() }}</div>
                </div>
            </div>
        </div>

        <!-- 1. Web-based Cron Trigger for cron-job.org (Zero Server Setup) -->
        @php
            $webCronUrl = url('/cron/run/' . ($grouped['cron']['cron_secret_token'] ?? ''));
            $lastWebPing = $grouped['cron']['cron_last_web_run_at'] ?? '';
        @endphp
        <div class="bg-gradient-to-br from-indigo-950 via-slate-900 to-blue-950 rounded-3xl p-6 sm:p-8 text-white border border-blue-500/30 shadow-2xl shadow-blue-900/20 space-y-6"
             x-data="{ copiedWeb: false }">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-bold bg-blue-500/20 text-blue-300 border border-blue-400/30 mb-2">
                        <i data-lucide="globe-2" class="w-3.5 h-3.5 text-blue-400"></i>
                        Recommended: Zero Server Configuration
                    </div>
                    <h3 class="text-xl font-black text-white flex items-center gap-2">
                        <span>Web-Based Automation URL for cron-job.org</span>
                    </h3>
                    <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
                        Server terminal ya cPanel access ki koi zaroorat nahi! Simply neechay dia gaya Webhook URL copy karein aur <strong>cron-job.org</strong> par add kar dein. cron-job.org is URL ko automatically ping karega aur aap ke recurring invoices aur payment reminders bina kisi rukawat ke trigger hotay rahein ge.
                    </p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <form action="{{ route('admin.settings.cron.regenerate') }}" method="POST" onsubmit="return confirm('Are you sure you want to regenerate the Web Cron Secret Key? Existing cron-job.org setup will need to be updated with the new URL.')">
                        @csrf
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 transition flex items-center gap-1.5" title="Regenerate secret security token">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>Regenerate Key</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- The URL Bar with Copy & Test Buttons -->
            <div class="space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs font-bold text-slate-300">
                    <span class="flex items-center gap-1.5 text-emerald-400">
                        <i data-lucide="link" class="w-4 h-4"></i>
                        <span>Your Dedicated Web Cron URL (GET / POST):</span>
                    </span>
                    <div class="flex items-center gap-2">
                        <a href="{{ $webCronUrl }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-blue-600/30 hover:bg-blue-600/50 text-blue-300 hover:text-white text-[11px] font-bold border border-blue-400/30 transition flex items-center gap-1.5">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            <span>Test Ping in Browser</span>
                        </a>
                        <button type="button" 
                                @click="navigator.clipboard.writeText('{{ $webCronUrl }}'); copiedWeb = true; setTimeout(() => copiedWeb = false, 2500)"
                                class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-[11px] font-bold shadow-md shadow-blue-600/30 transition flex items-center gap-1.5">
                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                            <span x-text="copiedWeb ? 'Copied to Clipboard!' : 'Copy Webhook URL'"></span>
                        </button>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-black/60 border border-blue-500/40 font-mono text-xs text-emerald-300 select-all break-all shadow-inner">
                    {{ $webCronUrl }}
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-1 text-[11px] text-slate-400">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ !empty($lastWebPing) ? 'bg-emerald-400 animate-pulse' : 'bg-slate-500' }}"></span>
                        <span>Last Webhook Ping: <strong class="text-white">{{ !empty($lastWebPing) ? \Carbon\Carbon::parse($lastWebPing)->diffForHumans() . ' (' . $lastWebPing . ')' : 'No web pings received yet' }}</strong></span>
                    </div>
                    <div class="text-slate-400">
                        Protected by 256-bit Security Token
                    </div>
                </div>
            </div>

            <!-- Step by Step Setup Guide -->
            <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-blue-400 flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>cron-job.org Setup Guide (takes 30 seconds):</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-800/60 border border-slate-700/60">
                        <div class="font-bold text-blue-300">1. Sign Up Free</div>
                        <div class="text-slate-400 text-[11px] mt-1">Open <a href="https://cron-job.org" target="_blank" class="text-blue-400 underline">cron-job.org</a> and log in to your dashboard.</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-800/60 border border-slate-700/60">
                        <div class="font-bold text-blue-300">2. Create Cronjob</div>
                        <div class="text-slate-400 text-[11px] mt-1">Click <strong>"Create Cronjob"</strong> button on the top right.</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-800/60 border border-slate-700/60">
                        <div class="font-bold text-blue-300">3. Paste URL</div>
                        <div class="text-slate-400 text-[11px] mt-1">Paste the Webhook URL above in the <strong>URL field</strong> (Method: <code>GET</code>).</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-800/60 border border-slate-700/60">
                        <div class="font-bold text-blue-300">4. Set Interval</div>
                        <div class="text-slate-400 text-[11px] mt-1">Select <strong>"Every 1 minute"</strong> (or 5/15 mins) and click <strong>Create</strong>. Done!</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Production Crontab Setup Card -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-950 rounded-3xl p-6 sm:p-8 text-white border border-slate-800 shadow-xl space-y-5"
             x-data="{ copiedCron: false, copiedWork: false }">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30 mb-2">
                    <i data-lucide="terminal" class="w-3.5 h-3.5"></i>
                    Alternative: Server CLI / Terminal Setup
                </div>
                <h3 class="text-lg font-black text-white">Linux Server / cPanel Crontab Command</h3>
                <p class="text-xs text-slate-400 mt-1">
                    Add this single Cron Job entry to your cPanel, DirectAdmin, Plesk, or Linux crontab (<code class="text-blue-300">crontab -e</code>). It runs once every minute and allows Laravel to handle all recurring invoices, status checks, and reminder emails automatically.
                </p>
            </div>

            <!-- Linux Crontab Entry -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs font-bold text-slate-300">
                    <span>Linux / cPanel Crontab Entry (Every Minute):</span>
                    <button type="button" 
                            @click="navigator.clipboard.writeText('* * * * * cd {{ str_replace('\\', '/', base_path()) }} && {{ PHP_BINARY }} artisan schedule:run >> /dev/null 2>&1'); copiedCron = true; setTimeout(() => copiedCron = false, 2500)"
                            class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-[11px] font-bold transition flex items-center gap-1.5">
                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                        <span x-text="copiedCron ? 'Copied!' : 'Copy Crontab Command'"></span>
                    </button>
                </div>
                <div class="p-3.5 rounded-2xl bg-black/60 border border-slate-800 font-mono text-xs text-emerald-400 select-all overflow-x-auto">
                    * * * * * cd {{ str_replace('\\', '/', base_path()) }} && {{ PHP_BINARY }} artisan schedule:run >> /dev/null 2>&1
                </div>
            </div>

            <!-- Local Worker Command -->
            <div class="space-y-2 pt-2 border-t border-slate-800">
                <div class="flex items-center justify-between text-xs font-bold text-slate-300">
                    <span>Local Development Daemon Command (Terminal):</span>
                    <button type="button" 
                            @click="navigator.clipboard.writeText('php artisan schedule:work'); copiedWork = true; setTimeout(() => copiedWork = false, 2500)"
                            class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-[11px] font-bold transition flex items-center gap-1.5">
                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                        <span x-text="copiedWork ? 'Copied!' : 'Copy Local Command'"></span>
                    </button>
                </div>
                <div class="p-3 rounded-xl bg-black/40 border border-slate-800 font-mono text-xs text-blue-300 select-all">
                    php artisan schedule:work
                </div>
            </div>
        </div>

        <!-- Automated Tasks Dashboard Table -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
            <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="list-checks" class="w-5 h-5 text-blue-600"></i>
                    Active Automated Background Jobs
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">These tasks are executed automatically by the Cron scheduler and can also be triggered manually on demand.</p>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 uppercase tracking-wider font-bold">
                            <th class="p-4">Automation Task</th>
                            <th class="p-4">Command</th>
                            <th class="p-4">Schedule</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Last Run & Output</th>
                            <th class="p-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <!-- Task 1: Recurring Invoices -->
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="p-4">
                                <div class="font-bold text-slate-900 dark:text-white text-sm">Recurring Invoices Generator</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    Generates scheduled invoices for active recurring profiles & emails clients with credentials.
                                </div>
                            </td>
                            <td class="p-4 font-mono text-[11px] text-blue-600 dark:text-blue-400">
                                invoices:process-recurring
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-[11px]">
                                    Daily at {{ $grouped['cron']['cron_recurring_time'] }}
                                </span>
                            </td>
                            <td class="p-4">
                                @if($grouped['cron']['cron_recurring_enabled'])
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Enabled
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                        Disabled
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 max-w-xs">
                                <div class="text-[11px] font-medium text-slate-700 dark:text-slate-300">
                                    {{ $grouped['cron']['cron_last_recurring_run_at'] ? \Carbon\Carbon::parse($grouped['cron']['cron_last_recurring_run_at'])->diffForHumans() : 'Never' }}
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5" title="{{ $grouped['cron']['cron_last_recurring_result'] }}">
                                    {{ $grouped['cron']['cron_last_recurring_result'] ?: 'No execution history' }}
                                </div>
                            </td>
                            <td class="p-4 text-right">
                                <form action="{{ route('admin.settings.cron.run') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="task" value="recurring">
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 dark:hover:bg-blue-900/60 text-blue-600 dark:text-blue-400 font-bold text-xs transition inline-flex items-center gap-1.5">
                                        <i data-lucide="play" class="w-3.5 h-3.5"></i>
                                        <span>Run Now</span>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Task 2: Payment Reminders -->
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="p-4">
                                <div class="font-bold text-slate-900 dark:text-white text-sm">Payment Reminders & Overdue Sync</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    Sends 3-tier payment reminder emails (Upcoming, Due Today, Overdue) & auto-marks past-due invoices as overdue.
                                </div>
                            </td>
                            <td class="p-4 font-mono text-[11px] text-blue-600 dark:text-blue-400">
                                invoices:send-reminders
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-[11px]">
                                    Daily at {{ $grouped['cron']['cron_reminders_time'] }}
                                </span>
                            </td>
                            <td class="p-4">
                                @if($grouped['cron']['cron_reminders_enabled'])
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Enabled
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                        Disabled
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 max-w-xs">
                                <div class="text-[11px] font-medium text-slate-700 dark:text-slate-300">
                                    {{ $grouped['cron']['cron_last_reminders_run_at'] ? \Carbon\Carbon::parse($grouped['cron']['cron_last_reminders_run_at'])->diffForHumans() : 'Never' }}
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5" title="{{ $grouped['cron']['cron_last_reminders_result'] }}">
                                    {{ $grouped['cron']['cron_last_reminders_result'] ?: 'No execution history' }}
                                </div>
                            </td>
                            <td class="p-4 text-right">
                                <form action="{{ route('admin.settings.cron.run') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="task" value="reminders">
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 dark:hover:bg-blue-900/60 text-blue-600 dark:text-blue-400 font-bold text-xs transition inline-flex items-center gap-1.5">
                                        <i data-lucide="play" class="w-3.5 h-3.5"></i>
                                        <span>Run Now</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Automation Settings Configuration Form -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
            <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="sliders" class="w-5 h-5 text-blue-600"></i>
                    Automation Timing & Behavior Configuration
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Customize daily schedule timings, reminder intervals, and toggle individual automation engines.</p>
            </div>

            <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="group" value="cron">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Recurring Invoices Configuration -->
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 font-bold text-sm text-slate-900 dark:text-white">
                                <i data-lucide="repeat" class="w-4 h-4 text-blue-600"></i>
                                <span>Recurring Invoices Generation</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="cron_recurring_enabled" value="1" {{ old('cron_recurring_enabled', $grouped['cron']['cron_recurring_enabled']) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            When enabled, active recurring invoice profiles reaching their next issue date will be generated automatically.
                        </p>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Daily Execution Time (HH:MM)
                            </label>
                            <input type="time" name="cron_recurring_time" value="{{ old('cron_recurring_time', $grouped['cron']['cron_recurring_time']) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold">
                            <p class="text-[11px] text-slate-400 mt-1">Default is 06:00 (6:00 AM server time)</p>
                        </div>
                    </div>

                    <!-- Payment Reminders Configuration -->
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 font-bold text-sm text-slate-900 dark:text-white">
                                <i data-lucide="bell" class="w-4 h-4 text-blue-600"></i>
                                <span>Payment Reminders Engine</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="cron_reminders_enabled" value="1" {{ old('cron_reminders_enabled', $grouped['cron']['cron_reminders_enabled']) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            When enabled, sends automated emails for upcoming invoices, invoices due today, and past-due overdue invoices.
                        </p>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Daily Reminder Dispatch Time (HH:MM)
                            </label>
                            <input type="time" name="cron_reminders_time" value="{{ old('cron_reminders_time', $grouped['cron']['cron_reminders_time']) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold">
                            <p class="text-[11px] text-slate-400 mt-1">Default is 08:00 (8:00 AM server time)</p>
                        </div>
                    </div>

                    <!-- Upcoming Reminders Timing -->
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                            Tier 1: Upcoming Reminder (Days in Advance)
                        </label>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                            How many days prior to the invoice due date should the client receive an upcoming payment reminder?
                        </p>
                        <div class="flex items-center gap-3">
                            <input type="number" name="cron_reminder_upcoming_days" min="1" max="30" value="{{ old('cron_reminder_upcoming_days', $grouped['cron']['cron_reminder_upcoming_days']) }}" required class="w-32 px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-400">Days before due date</span>
                        </div>
                    </div>

                    <!-- Overdue Reminders Interval -->
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                            Tier 3: Overdue Notice Spacing (Anti-Spam Interval)
                        </label>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                            Minimum days to wait before re-sending an overdue notice to a delinquent client.
                        </p>
                        <div class="flex items-center gap-3">
                            <input type="number" name="cron_reminder_overdue_interval" min="1" max="30" value="{{ old('cron_reminder_overdue_interval', $grouped['cron']['cron_reminder_overdue_interval']) }}" required class="w-32 px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-400">Days between overdue notices</span>
                        </div>
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Save Automation Configuration</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection
