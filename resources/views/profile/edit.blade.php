@extends('layouts.app')

@section('title', 'Profile & Settings')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12"
     x-data="{ 
        activeTab: '{{ request()->query('tab', session('active_tab', $errors->hasAny(['fbr_pos_id', 'fbr_pos_usin', 'fbr_bearer_token']) ? 'fbr' : ($errors->hasAny(['bank_name', 'bank_account_number', 'bank_iban', 'raast_id', 'jazzcash_number', 'easypaisa_number']) ? 'payments' : ($errors->hasAny(['current_password', 'password', 'password_confirmation']) ? 'security' : ($errors->hasAny(['company_name', 'tax_id', 'address', 'city', 'country']) ? 'business' : ($errors->hasAny(['default_currency', 'default_payment_instructions', 'default_notes']) ? 'invoicing' : 'personal')))))) }}',
        avatarPreview: null,
        removeAvatar: false,
        previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                this.removeAvatar = false;
                const reader = new FileReader();
                reader.onload = (e) => { this.avatarPreview = e.target.result; };
                reader.readAsDataURL(file);
            }
        }
     }">

    <!-- Page Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-200">Settings</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Account & Invoicing Settings</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage your identity, business profile, default invoice preferences, and account security.</p>
        </div>

        <div class="flex items-center gap-2">
            @if($user->isAdmin())
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    Administrator
                </span>
            @endif
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                {{ $user->package ? $user->package->name : 'Starter Plan' }}
            </span>
        </div>
    </div>

    <!-- Quick User Profile Banner -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 rounded-2xl p-4 sm:p-5 text-white shadow-md border border-slate-700/50">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="relative w-14 h-14 rounded-xl bg-gradient-to-tr from-blue-500 to-indigo-500 p-0.5 shadow-md shrink-0">
                    <div class="w-full h-full rounded-xl overflow-hidden bg-slate-900 flex items-center justify-center font-bold text-lg text-blue-400">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        @endif
                    </div>
                </div>

                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-base sm:text-lg font-bold tracking-tight">{{ $user->name }}</h2>
                        @if($user->company_name)
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-white/10 text-blue-200 font-medium border border-white/10">
                                {{ $user->company_name }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5 flex items-center gap-1.5">
                        <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                        {{ $user->email }}
                    </p>
                </div>
            </div>

            <!-- Credits & Invoices Quick Counters -->
            <div class="flex items-center gap-2.5">
                <div class="bg-white/10 backdrop-blur-md px-3.5 py-2 rounded-xl border border-white/10 min-w-[90px] text-center">
                    <span class="text-slate-400 block text-[10px] uppercase tracking-wider font-semibold">Credits</span>
                    <span class="font-bold text-white text-base">
                        {{ $user->package && $user->package->invoice_limit === -1 ? 'Unlimited' : $user->invoice_credits }}
                    </span>
                </div>
                <div class="bg-white/10 backdrop-blur-md px-3.5 py-2 rounded-xl border border-white/10 min-w-[90px] text-center">
                    <span class="text-slate-400 block text-[10px] uppercase tracking-wider font-semibold">Invoices</span>
                    <span class="font-bold text-white text-base">{{ $user->invoices()->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Navigation Tabs -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-2 border-b border-slate-200 dark:border-slate-800 scrollbar-none">
        <button type="button" 
                @click="activeTab = 'personal'"
                :class="activeTab === 'personal' 
                    ? 'bg-blue-600 text-white shadow-xs' 
                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800'"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg font-semibold text-xs whitespace-nowrap transition">
            <i data-lucide="user" class="w-3.5 h-3.5"></i>
            Personal Details
        </button>

        <button type="button" 
                @click="activeTab = 'business'"
                :class="activeTab === 'business' 
                    ? 'bg-blue-600 text-white shadow-xs' 
                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800'"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg font-semibold text-xs whitespace-nowrap transition">
            <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
            Business & Company
        </button>

        <button type="button" 
                @click="activeTab = 'payments'"
                :class="activeTab === 'payments' 
                    ? 'bg-emerald-600 text-white shadow-xs' 
                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800'"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg font-semibold text-xs whitespace-nowrap transition">
            <i data-lucide="wallet" class="w-3.5 h-3.5 text-emerald-400"></i>
            <span>Payment Methods & Accounts</span>
            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-600 dark:text-emerald-400">
                PK Wallets
            </span>
        </button>

        <button type="button" 
                @click="activeTab = 'fbr'"
                :class="activeTab === 'fbr' 
                    ? 'bg-emerald-700 text-white shadow-xs' 
                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800'"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg font-semibold text-xs whitespace-nowrap transition">
            <i data-lucide="qr-code" class="w-3.5 h-3.5 text-emerald-400"></i>
            <span>FBR POS &amp; Invoicing</span>
            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider {{ $user->fbr_enabled ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                {{ $user->fbr_enabled ? 'Active' : 'Offline' }}
            </span>
        </button>

        <button type="button" 
                @click="activeTab = 'invoicing'"
                :class="activeTab === 'invoicing' 
                    ? 'bg-blue-600 text-white shadow-xs' 
                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800'"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg font-semibold text-xs whitespace-nowrap transition">
            <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
            Invoicing Defaults
        </button>

        <button type="button" 
                @click="activeTab = 'security'"
                :class="activeTab === 'security' 
                    ? 'bg-blue-600 text-white shadow-xs' 
                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800'"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg font-semibold text-xs whitespace-nowrap transition">
            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
            Security & Password
        </button>

        <button type="button" 
                @click="activeTab = 'appearance'"
                :class="activeTab === 'appearance' 
                    ? 'bg-blue-600 text-white shadow-xs' 
                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800'"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg font-semibold text-xs whitespace-nowrap transition">
            <i data-lucide="palette" class="w-3.5 h-3.5"></i>
            Appearance & Theme
        </button>
    </div>

    <!-- SECTION 1: Personal Details & Avatar (Saves Independently) -->
    <div x-show="activeTab === 'personal'" x-cloak class="bg-white dark:bg-slate-900 rounded-2xl p-5 sm:p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4 gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4 text-blue-600 dark:text-blue-400"></i>
                    Personal Profile Details
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage your identity, email address, phone, and profile avatar.</p>
            </div>
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-0.5 rounded-full w-fit">
                <i data-lucide="check-circle" class="w-3 h-3"></i>
                Independent Section
            </span>
        </div>

        <form method="POST" action="{{ route('profile.personal') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')
            <input type="hidden" name="remove_avatar" :value="removeAvatar ? 1 : 0">

            <!-- Avatar Upload & Live Preview -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Profile Avatar</label>
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="relative w-14 h-14 rounded-xl bg-slate-100 dark:bg-slate-800 overflow-hidden border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 shrink-0 font-bold text-base shadow-xs">
                        <!-- Preview Image if newly selected -->
                        <template x-if="avatarPreview">
                            <img :src="avatarPreview" alt="Preview" class="w-full h-full object-cover">
                        </template>

                        <!-- Existing avatar if not removed and no new preview -->
                        <template x-if="!avatarPreview && !removeAvatar">
                            <div>
                                @if($user->avatar_url)
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                @endif
                            </div>
                        </template>

                        <!-- Initial placeholder if removed -->
                        <template x-if="!avatarPreview && removeAvatar">
                            <span>{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        </template>
                    </div>

                    <div class="flex-1 space-y-1.5">
                        <div class="flex items-center gap-2.5">
                            <label class="px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 font-semibold text-xs hover:bg-blue-100 dark:hover:bg-blue-900/60 transition cursor-pointer border border-blue-200 dark:border-blue-800 inline-flex items-center gap-1.5">
                                <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                Upload New Photo
                                <input type="file" name="avatar" accept="image/*" class="sr-only" @change="previewImage($event)">
                            </label>

                            @if($user->avatar_url)
                                <button type="button" @click="removeAvatar = true; avatarPreview = null" x-show="!removeAvatar" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 transition inline-flex items-center gap-1.5">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    Remove Avatar
                                </button>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-400">JPG, PNG, WebP or SVG up to 2MB.</p>
                        @error('avatar')<p class="text-xs text-rose-500 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                <!-- Full Name -->
                <div>
                    <label for="personal_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Your Full Name *</label>
                    <input type="text" name="name" id="personal_name" value="{{ old('name', $user->name) }}" required class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @error('name')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <!-- Email Address -->
                <div>
                    <label for="personal_email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Address *</label>
                    <input type="email" name="email" id="personal_email" value="{{ old('email', $user->email) }}" required class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @error('email')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <!-- Phone -->
                <div class="sm:col-span-2">
                    <label for="personal_phone" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                    <input type="text" name="phone" id="personal_phone" value="{{ old('phone', $user->phone) }}" placeholder="+1 (555) 000-0000" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @error('phone')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Footer Save -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-400">Updates personal identity and avatar.</span>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="save" class="w-3.5 h-3.5"></i>
                    Save Personal Details
                </button>
            </div>
        </form>
    </div>

    <!-- SECTION 2: Business & Company Details (Saves Independently) -->
    <div x-show="activeTab === 'business'" x-cloak class="bg-white dark:bg-slate-900 rounded-2xl p-5 sm:p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4 gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="building-2" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                    Business & Company Information
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Appears on the header and billing section of your invoices.</p>
            </div>
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 px-2.5 py-0.5 rounded-full w-fit">
                <i data-lucide="check-circle" class="w-3 h-3"></i>
                Independent Section
            </span>
        </div>

        <form method="POST" action="{{ route('profile.business') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Company Name -->
                <div>
                    <label for="biz_company_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Company / Freelance Name</label>
                    <input type="text" name="company_name" id="biz_company_name" value="{{ old('company_name', $user->company_name) }}" placeholder="e.g. Apex Digital Solutions LLC" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @error('company_name')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <!-- Tax ID / VAT -->
                <div>
                    <label for="biz_tax_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">General Tax ID / VAT</label>
                    <input type="text" name="tax_id" id="biz_tax_id" value="{{ old('tax_id', $user->tax_id) }}" placeholder="e.g. US-12345678 or VAT-GB998877" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @error('tax_id')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <!-- NTN (FBR National Tax Number) -->
                <div>
                    <label for="biz_ntn" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">FBR NTN (National Tax Number)</label>
                    <input type="text" name="ntn" id="biz_ntn" value="{{ old('ntn', $user->ntn) }}" placeholder="e.g. 1234567-8" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @error('ntn')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <!-- STRN (Sales Tax Registration Number) -->
                <div>
                    <label for="biz_strn" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">STRN (Sales Tax Reg Number)</label>
                    <input type="text" name="strn" id="biz_strn" value="{{ old('strn', $user->strn) }}" placeholder="e.g. 12-00-1234-567-89" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @error('strn')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <!-- CNIC / National ID -->
                <div>
                    <label for="biz_cnic" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">CNIC / National Identity Card</label>
                    <input type="text" name="cnic" id="biz_cnic" value="{{ old('cnic', $user->cnic) }}" placeholder="e.g. 35201-1234567-1" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @error('cnic')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <!-- Street Address -->
                <div class="sm:col-span-2">
                    <label for="biz_address" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Business Street Address</label>
                    <input type="text" name="address" id="biz_address" value="{{ old('address', $user->address) }}" placeholder="e.g. 100 Main Street, Suite 400" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @error('address')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <!-- City -->
                <div>
                    <label for="biz_city" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">City</label>
                    <input type="text" name="city" id="biz_city" value="{{ old('city', $user->city) }}" placeholder="e.g. New York / London / Lahore" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @error('city')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <!-- Country -->
                <div>
                    <label for="biz_country" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Country</label>
                    <input type="text" name="country" id="biz_country" value="{{ old('country', $user->country) }}" placeholder="e.g. United States, Pakistan, UAE" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @error('country')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Footer Save -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-400">Updates business identity & billing address.</span>
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs sm:text-sm shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="save" class="w-3.5 h-3.5"></i>
                    Save Business Details
                </button>
            </div>
        </form>
    </div>

    <!-- SECTION 3: Invoicing Defaults (Saves Independently) -->
    <div x-show="activeTab === 'invoicing'" x-cloak class="bg-white dark:bg-slate-900 rounded-2xl p-5 sm:p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4 gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="receipt" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                    Default Invoicing Preferences
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pre-fills automatically on every new invoice.</p>
            </div>
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-0.5 rounded-full w-fit">
                <i data-lucide="check-circle" class="w-3 h-3"></i>
                Independent Section
            </span>
        </div>

        <form method="POST" action="{{ route('profile.invoicing') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="inv_default_currency" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Default Invoice Currency</label>
                <select name="default_currency" id="inv_default_currency" class="w-full sm:w-72 px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition font-medium">
                    @foreach(['USD' => 'USD ($) - US Dollar', 'EUR' => 'EUR (€) - Euro', 'GBP' => 'GBP (£) - British Pound', 'CAD' => 'CAD ($) - Canadian Dollar', 'AUD' => 'AUD ($) - Australian Dollar', 'PKR' => 'PKR (₨) - Pakistani Rupee', 'INR' => 'INR (₹) - Indian Rupee', 'AED' => 'AED (د.إ) - UAE Dirham', 'SAR' => 'SAR (﷼) - Saudi Riyal', 'JPY' => 'JPY (¥) - Japanese Yen'] as $code => $lbl)
                        <option value="{{ $code }}" {{ old('default_currency', $user->default_currency ?? 'USD') === $code ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                @error('default_currency')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="inv_default_payment_instructions" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Default Remittance & Bank Instructions</label>
                <textarea name="default_payment_instructions" id="inv_default_payment_instructions" rows="3" placeholder="e.g. Wire Transfer Details:&#10;Bank: Chase Manhattan&#10;IBAN / Account #: US1234567890&#10;Routing: 021000021" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">{{ old('default_payment_instructions', $user->default_payment_instructions) }}</textarea>
                @error('default_payment_instructions')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="inv_default_notes" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Default Terms, Conditions & Notes</label>
                <textarea name="default_notes" id="inv_default_notes" rows="2" placeholder="e.g. Thank you for choosing our services! Please pay within 14 days of invoice date." class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">{{ old('default_notes', $user->default_notes) }}</textarea>
                @error('default_notes')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <!-- Footer Save Invoicing -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <input type="hidden" name="redirect_tab" value="invoicing">
                <span class="text-xs text-slate-400">Pre-fills new invoices automatically.</span>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="save" class="w-3.5 h-3.5"></i>
                    Save Invoicing Defaults
                </button>
            </div>
        </form>
    </div>

    <!-- SECTION: Payment Methods & Accounts (Saves Independently) -->
    <div x-show="activeTab === 'payments'" x-cloak class="bg-white dark:bg-slate-900 rounded-2xl p-5 sm:p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4 gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="wallet" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                    Payment Methods & Accounts
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Configure your Pakistani Bank accounts (IBFT), Raast ID, and Mobile Wallets (JazzCash & EasyPaisa).</p>
            </div>
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-0.5 rounded-full w-fit">
                <i data-lucide="check-circle" class="w-3 h-3"></i>
                Active on Invoices
            </span>
        </div>

        <form method="POST" action="{{ route('profile.payments') }}" class="space-y-5">
            @csrf
            @method('PUT')
            <input type="hidden" name="redirect_tab" value="payments">

            <!-- Pakistani Local Bank & Raast Transfer (IBFT) -->
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">
                        <i data-lucide="building" class="w-3.5 h-3.5"></i>
                    </span>
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Bank Transfer / IBFT & Raast Account</h3>
                        <p class="text-[11px] text-slate-400">Direct client bank transfers on public invoices.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="bank_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Bank Name</label>
                        <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $user->bank_name) }}" placeholder="e.g. Meezan Bank / HBL / Bank Alfalah" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        @error('bank_name')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="bank_account_title" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Account Title</label>
                        <input type="text" name="bank_account_title" id="bank_account_title" value="{{ old('bank_account_title', $user->bank_account_title) }}" placeholder="e.g. Acme Tech Solutions" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        @error('bank_account_title')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="bank_account_number" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Account Number</label>
                        <input type="text" name="bank_account_number" id="bank_account_number" value="{{ old('bank_account_number', $user->bank_account_number) }}" placeholder="e.g. 01010101234567" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        @error('bank_account_number')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="bank_iban" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">IBAN</label>
                        <input type="text" name="bank_iban" id="bank_iban" value="{{ old('bank_iban', $user->bank_iban) }}" placeholder="e.g. PK36MEZN0001234567890123" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        @error('bank_iban')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="raast_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Raast ID (Instant 0-Fee Transfer)</label>
                        <input type="text" name="raast_id" id="raast_id" value="{{ old('raast_id', $user->raast_id) }}" placeholder="e.g. 03001234567 or Raast IBAN" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        @error('raast_id')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Mobile Wallets (JazzCash & EasyPaisa Direct Numbers) -->
            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-md bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold text-xs">
                        <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
                    </span>
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Mobile Wallets (JazzCash & EasyPaisa)</h3>
                        <p class="text-[11px] text-slate-400">Clients can transfer directly from their apps to your number.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="jazzcash_number" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">JazzCash Mobile Number</label>
                        <input type="text" name="jazzcash_number" id="jazzcash_number" value="{{ old('jazzcash_number', $user->jazzcash_number) }}" placeholder="e.g. 03001234567" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        @error('jazzcash_number')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="jazzcash_title" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">JazzCash Account Title</label>
                        <input type="text" name="jazzcash_title" id="jazzcash_title" value="{{ old('jazzcash_title', $user->jazzcash_title) }}" placeholder="e.g. Muhammad Ali" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        @error('jazzcash_title')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="easypaisa_number" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">EasyPaisa Mobile Number</label>
                        <input type="text" name="easypaisa_number" id="easypaisa_number" value="{{ old('easypaisa_number', $user->easypaisa_number) }}" placeholder="e.g. 03451234567" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        @error('easypaisa_number')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="easypaisa_title" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">EasyPaisa Account Title</label>
                        <input type="text" name="easypaisa_title" id="easypaisa_title" value="{{ old('easypaisa_title', $user->easypaisa_title) }}" placeholder="e.g. Muhammad Ali" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        @error('easypaisa_title')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Automated Gateway Credentials (Optional) -->
            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3" x-data="{ openGateways: false }">
                <div class="flex items-center justify-between cursor-pointer" @click="openGateways = !openGateways">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs">
                            <i data-lucide="key-round" class="w-3.5 h-3.5"></i>
                        </span>
                        <div>
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                Automated Merchant API Gateways (Optional)
                                <span class="text-[9px] uppercase font-bold px-1.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">Advanced</span>
                            </h3>
                            <p class="text-[11px] text-slate-400">Direct checkout via JazzCash / EasyPaisa merchant credentials.</p>
                        </div>
                    </div>
                    <button type="button" class="text-xs text-blue-600 dark:text-blue-400 font-semibold hover:underline" x-text="openGateways ? 'Hide Credentials' : 'Show Credentials'"></button>
                </div>

                <div x-show="openGateways" x-cloak class="space-y-3 pt-1">
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-2.5">
                        <h4 class="text-xs font-semibold text-slate-700 dark:text-slate-300">JazzCash Merchant Credentials</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">Merchant ID</label>
                                <input type="text" name="jazzcash_merchant_id" value="{{ old('jazzcash_merchant_id', $user->jazzcash_merchant_id) }}" placeholder="MC12345" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">Password</label>
                                <input type="password" name="jazzcash_password" placeholder="{{ $user->jazzcash_password ? '••••••••' : 'Password' }}" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">Hash Key</label>
                                <input type="password" name="jazzcash_hash_key" placeholder="{{ $user->jazzcash_hash_key ? '••••••••' : 'Integrity Salt' }}" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-2.5">
                        <h4 class="text-xs font-semibold text-slate-700 dark:text-slate-300">EasyPaisa Merchant Credentials</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">Store ID</label>
                                <input type="text" name="easypaisa_store_id" value="{{ old('easypaisa_store_id', $user->easypaisa_store_id) }}" placeholder="Store ID" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">Hash Key / Secret</label>
                                <input type="password" name="easypaisa_hash_key" placeholder="{{ $user->easypaisa_hash_key ? '••••••••' : 'Secret Key' }}" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Save -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-400">Clients can transfer payments into these accounts.</span>
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs sm:text-sm shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="save" class="w-3.5 h-3.5"></i>
                    Save Payment Methods
                </button>
            </div>
        </form>
    </div>

    <!-- SECTION: FBR Digital Invoicing & POS Integration (Saves Independently) -->
    <div x-show="activeTab === 'fbr'" x-cloak class="bg-white dark:bg-slate-900 rounded-2xl p-5 sm:p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4 gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="qr-code" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                    FBR Digital Invoicing &amp; POS Integration (Pakistan)
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Integrate with Federal Board of Revenue (FBR) Electronic Billing System (EBS) to generate fiscalized invoice numbers and QR codes.</p>
            </div>
            @if($user->fbr_enabled)
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-0.5 rounded-full w-fit">
                    <i data-lucide="check-circle" class="w-3 h-3"></i>
                    FBR Active ({{ ucfirst($user->fbr_environment) }})
                </span>
            @else
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-full w-fit">
                    <i data-lucide="power-off" class="w-3 h-3"></i>
                    Disabled
                </span>
            @endif
        </div>

        <form method="POST" action="{{ route('profile.fbr') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Enable Integration Toggle & Environment -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                <div class="flex items-start gap-3">
                    <input type="checkbox" name="fbr_enabled" id="fbr_enabled" value="1" {{ old('fbr_enabled', $user->fbr_enabled) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <div>
                        <label for="fbr_enabled" class="block text-xs sm:text-sm font-bold text-slate-900 dark:text-white cursor-pointer">Enable FBR Digital Invoicing</label>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Automatically register created invoices with the FBR Electronic Billing System and generate official FBR fiscal QR codes.</p>
                    </div>
                </div>

                <div>
                    <label for="fbr_environment" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Environment Mode</label>
                    <select name="fbr_environment" id="fbr_environment" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500">
                        <option value="sandbox" {{ old('fbr_environment', $user->fbr_environment) === 'sandbox' ? 'selected' : '' }}>Sandbox / Testing (Simulation Mode)</option>
                        <option value="production" {{ old('fbr_environment', $user->fbr_environment) === 'production' ? 'selected' : '' }}>Production (Live FBR e-Invoicing Server)</option>
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1">In Sandbox mode, invoices generate verified simulated FBR IDs without contacting live tax servers.</p>
                </div>
            </div>

            <!-- Credentials Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="fbr_pos_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">FBR POS ID *</label>
                    <input type="text" name="fbr_pos_id" id="fbr_pos_id" value="{{ old('fbr_pos_id', $user->fbr_pos_id) }}" placeholder="e.g. 102938" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500">
                    <p class="text-[10px] text-slate-400 mt-1">Point of Sale identifier assigned to your business branch by FBR.</p>
                    @error('fbr_pos_id')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="fbr_pos_usin" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Unique System Identifier (USIN) (Optional)</label>
                    <input type="text" name="fbr_pos_usin" id="fbr_pos_usin" value="{{ old('fbr_pos_usin', $user->fbr_pos_usin) }}" placeholder="e.g. POS-MAIN-01" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500">
                    <p class="text-[10px] text-slate-400 mt-1">Unique device or system name. Defaults to invoice sequence if blank.</p>
                    @error('fbr_pos_usin')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="fbr_bearer_token" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">FBR IMS Bearer Auth Token (Production)</label>
                <input type="password" name="fbr_bearer_token" id="fbr_bearer_token" placeholder="{{ $user->fbr_bearer_token ? '•••••••••••••••••••••••••••••••• (Encrypted in DB)' : 'Paste your FBR Authorization Bearer token' }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500">
                <p class="text-[10px] text-slate-400 mt-1">Token is encrypted at rest using AES-256. Leave blank to preserve existing token.</p>
                @error('fbr_bearer_token')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- FBR Requirements & Info Notice -->
            <div class="p-3.5 rounded-xl bg-blue-50/80 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900/40 text-xs text-blue-900 dark:text-blue-300 space-y-1.5">
                <div class="flex items-center gap-2 font-bold">
                    <i data-lucide="info" class="w-4 h-4 text-blue-600 dark:text-blue-400"></i>
                    Pakistan Sales Tax &amp; FBR Compliance Requirements
                </div>
                <p class="text-[11px] text-blue-800/80 dark:text-blue-300/80 leading-relaxed">
                    Under Sales Tax Rules, Tier-1 Retailers and registered service providers must integrate their electronic point-of-sale systems with FBR IMS. Invoices issued with FBR enabled will carry an official FBR Invoice Number and a scannable QR verification code.
                </p>
            </div>

            <!-- Footer Save -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-400">Settings take effect immediately for new invoices.</span>
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs sm:text-sm shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="save" class="w-3.5 h-3.5"></i>
                    Save FBR Settings
                </button>
            </div>
        </form>
    </div>

    <!-- SECTION 4: Security & Password (Saves Independently) -->
    <div x-show="activeTab === 'security'" x-cloak class="bg-white dark:bg-slate-900 rounded-2xl p-5 sm:p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4 gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="lock" class="w-4 h-4 text-amber-500"></i>
                    Security & Password Update
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Ensure your account is using a strong, unique password.</p>
            </div>
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2.5 py-0.5 rounded-full w-fit">
                <i data-lucide="shield" class="w-3 h-3"></i>
                Independent Section
            </span>
        </div>

        <form method="POST" action="{{ route('profile.password') }}" class="space-y-5">
            @csrf
            @method('PUT')

            @if(!empty($user->password))
            <div>
                <label for="sec_current_password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Current Password *</label>
                <input type="password" name="current_password" id="sec_current_password" required placeholder="Enter current password" class="w-full sm:w-80 px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                @error('current_password')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="sec_password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">New Password *</label>
                    <input type="password" name="password" id="sec_password" required placeholder="Minimum 8 characters" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                    @error('password')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="sec_password_confirmation" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Confirm New Password *</label>
                    <input type="password" name="password_confirmation" id="sec_password_confirmation" required placeholder="Repeat new password" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                </div>
            </div>

            <!-- Footer Save -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-400">Immediately changes your authentication credential.</span>
                <button type="submit" class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 text-white font-semibold text-xs sm:text-sm shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="key" class="w-3.5 h-3.5"></i>
                    Update Password
                </button>
            </div>
        </form>
    </div>

    <!-- SECTION 5: Appearance & Theme (Applies Instantly) -->
    <div x-show="activeTab === 'appearance'" x-cloak class="bg-white dark:bg-slate-900 rounded-2xl p-5 sm:p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="palette" class="w-4 h-4 text-purple-600 dark:text-purple-400"></i>
                Appearance & Theme
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Select your preferred user interface mode. Switches instantly across all pages.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 max-w-xl" x-data="{ currentTheme: localStorage.getItem('invoice_hub_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') }">
            <!-- Light Theme -->
            <button type="button" 
                    @click="applyTheme('light'); currentTheme = 'light'"
                    :class="currentTheme === 'light' ? 'ring-2 ring-blue-600 bg-blue-50/50 dark:bg-slate-800 border-blue-600 shadow-xs' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                    class="p-4 rounded-xl border text-left transition flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 shadow-xs">
                    <i data-lucide="sun" class="w-5 h-5"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white">Light Mode</span>
                        <span x-show="currentTheme === 'light'" class="w-2 h-2 rounded-full bg-blue-600"></span>
                    </div>
                    <span class="block text-[11px] text-slate-500 mt-0.5">Crisp, clean & bright interface</span>
                </div>
            </button>

            <!-- Dark Theme -->
            <button type="button" 
                    @click="applyTheme('dark'); currentTheme = 'dark'"
                    :class="currentTheme === 'dark' ? 'ring-2 ring-blue-600 bg-blue-50/50 dark:bg-slate-800 border-blue-600 shadow-xs' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                    class="p-4 rounded-xl border text-left transition flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-indigo-950 text-indigo-300 flex items-center justify-center shrink-0 shadow-xs">
                    <i data-lucide="moon" class="w-5 h-5"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white">Dark Mode</span>
                        <span x-show="currentTheme === 'dark'" class="w-2 h-2 rounded-full bg-blue-600"></span>
                    </div>
                    <span class="block text-[11px] text-slate-500 mt-0.5">Deep slate & eye-comfort theme</span>
                </div>
            </button>
        </div>
    </div>

</div>
@endsection
