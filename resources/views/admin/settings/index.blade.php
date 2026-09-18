@extends('layouts.admin')

@section('title', 'System & Integration Settings')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12" x-data="{ currentTab: '{{ $activeTab }}' }">

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

</div>
@endsection
