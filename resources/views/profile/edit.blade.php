@extends('layouts.app')

@section('title', 'Profile & Settings')

@section('content')
<div class="max-w-5xl mx-auto space-y-8 pb-16"
     x-data="{ 
        activeTab: '{{ session('active_tab', $errors->hasAny(['current_password', 'password', 'password_confirmation']) ? 'security' : ($errors->hasAny(['company_name', 'tax_id', 'address', 'city', 'country']) ? 'business' : ($errors->hasAny(['default_currency', 'default_payment_instructions', 'default_notes']) ? 'invoicing' : 'personal'))) }}',
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
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-200">Settings</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Account & Invoicing Settings</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Manage your identity, business profile, default invoice preferences, and account security independently.</p>
        </div>

        <div class="flex items-center gap-2">
            @if($user->isAdmin())
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    Administrator
                </span>
            @endif
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                {{ $user->package ? $user->package->name : 'Starter Plan' }}
            </span>
        </div>
    </div>

    <!-- Quick User Profile Banner -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-slate-900/10 border border-slate-700/50">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="relative w-20 h-20 rounded-2xl bg-gradient-to-tr from-blue-500 to-indigo-500 p-0.5 shadow-lg shrink-0">
                    <div class="w-full h-full rounded-2xl overflow-hidden bg-slate-900 flex items-center justify-center font-bold text-2xl text-blue-400">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        @endif
                    </div>
                </div>

                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-xl sm:text-2xl font-black tracking-tight">{{ $user->name }}</h2>
                        @if($user->company_name)
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-white/10 text-blue-200 font-semibold border border-white/10">
                                {{ $user->company_name }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400 mt-1 flex items-center gap-1.5">
                        <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                        {{ $user->email }}
                    </p>
                </div>
            </div>

            <!-- Credits & Invoices Quick Counters -->
            <div class="flex items-center gap-3">
                <div class="bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10 min-w-[110px] text-center">
                    <span class="text-slate-400 block text-[10px] uppercase tracking-wider font-bold">Credits</span>
                    <span class="font-extrabold text-white text-lg">
                        {{ $user->package && $user->package->invoice_limit === -1 ? 'Unlimited' : $user->invoice_credits }}
                    </span>
                </div>
                <div class="bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10 min-w-[110px] text-center">
                    <span class="text-slate-400 block text-[10px] uppercase tracking-wider font-bold">Invoices</span>
                    <span class="font-extrabold text-white text-lg">{{ $user->invoices()->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Navigation Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-200 dark:border-slate-800 scrollbar-none">
        <button type="button" 
                @click="activeTab = 'personal'"
                :class="activeTab === 'personal' 
                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' 
                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800'"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm whitespace-nowrap transition">
            <i data-lucide="user" class="w-4 h-4"></i>
            Personal Details
        </button>

        <button type="button" 
                @click="activeTab = 'business'"
                :class="activeTab === 'business' 
                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' 
                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800'"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm whitespace-nowrap transition">
            <i data-lucide="building-2" class="w-4 h-4"></i>
            Business & Company
        </button>

        <button type="button" 
                @click="activeTab = 'invoicing'"
                :class="activeTab === 'invoicing' 
                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' 
                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800'"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm whitespace-nowrap transition">
            <i data-lucide="receipt" class="w-4 h-4"></i>
            Invoicing Defaults
        </button>

        <button type="button" 
                @click="activeTab = 'security'"
                :class="activeTab === 'security' 
                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' 
                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800'"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm whitespace-nowrap transition">
            <i data-lucide="lock" class="w-4 h-4"></i>
            Security & Password
        </button>

        <button type="button" 
                @click="activeTab = 'appearance'"
                :class="activeTab === 'appearance' 
                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' 
                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800'"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm whitespace-nowrap transition">
            <i data-lucide="palette" class="w-4 h-4"></i>
            Appearance & Theme
        </button>
    </div>

    <!-- SECTION 1: Personal Details & Avatar (Saves Independently) -->
    <div x-show="activeTab === 'personal'" x-cloak class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-5 gap-2">
            <div>
                <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2.5">
                    <i data-lucide="user" class="w-5 h-5 text-blue-600 dark:text-blue-400"></i>
                    Personal Profile Details
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Manage your identity, email address, phone, and profile avatar.</p>
            </div>
            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1 rounded-full w-fit">
                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                Independent Section
            </span>
        </div>

        <form method="POST" action="{{ route('profile.personal') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="remove_avatar" :value="removeAvatar ? 1 : 0">

            <!-- Avatar Upload & Live Preview -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Profile Avatar</label>
                <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                    <div class="relative w-20 h-20 rounded-2xl bg-slate-100 dark:bg-slate-800 overflow-hidden border-2 border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 shrink-0 font-bold text-xl shadow-inner">
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

                    <div class="flex-1 space-y-2">
                        <div class="flex items-center gap-3">
                            <label class="px-4 py-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 font-bold text-xs hover:bg-blue-100 dark:hover:bg-blue-900/60 transition cursor-pointer border border-blue-200 dark:border-blue-800 inline-flex items-center gap-2">
                                <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                Upload New Photo
                                <input type="file" name="avatar" accept="image/*" class="sr-only" @change="previewImage($event)">
                            </label>

                            @if($user->avatar_url)
                                <button type="button" @click="removeAvatar = true; avatarPreview = null" x-show="!removeAvatar" class="px-3.5 py-2 rounded-xl text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 transition inline-flex items-center gap-1.5">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    Remove Avatar
                                </button>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-400">JPG, PNG, WebP or SVG up to 2MB. Stored securely on your server.</p>
                        @error('avatar')<p class="text-xs text-rose-500 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2">
                <!-- Full Name -->
                <div>
                    <label for="personal_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Your Full Name *</label>
                    <input type="text" name="name" id="personal_name" value="{{ old('name', $user->name) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @error('name')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <!-- Email Address -->
                <div>
                    <label for="personal_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Email Address *</label>
                    <input type="email" name="email" id="personal_email" value="{{ old('email', $user->email) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @error('email')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <!-- Phone -->
                <div class="sm:col-span-2">
                    <label for="personal_phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Phone Number</label>
                    <input type="text" name="phone" id="personal_phone" value="{{ old('phone', $user->phone) }}" placeholder="+1 (555) 000-0000" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @error('phone')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Footer Save -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-400">Updates only your personal identity and avatar.</span>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md shadow-blue-600/20 transition flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Save Personal Details
                </button>
            </div>
        </form>
    </div>

    <!-- SECTION 2: Business & Company Details (Saves Independently) -->
    <div x-show="activeTab === 'business'" x-cloak class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-5 gap-2">
            <div>
                <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2.5">
                    <i data-lucide="building-2" class="w-5 h-5 text-indigo-600 dark:text-indigo-400"></i>
                    Business & Company Information
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">This information appears automatically on the header and client billing sections of your invoices.</p>
            </div>
            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 px-3 py-1 rounded-full w-fit">
                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                Independent Section
            </span>
        </div>

        <form method="POST" action="{{ route('profile.business') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Company Name -->
                <div>
                    <label for="biz_company_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Company / Agency / Freelance Studio</label>
                    <input type="text" name="company_name" id="biz_company_name" value="{{ old('company_name', $user->company_name) }}" placeholder="e.g. Apex Digital Solutions LLC" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @error('company_name')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <!-- Tax ID / VAT -->
                <div>
                    <label for="biz_tax_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Tax Registration / VAT / NTN</label>
                    <input type="text" name="tax_id" id="biz_tax_id" value="{{ old('tax_id', $user->tax_id) }}" placeholder="e.g. US-12345678 or VAT-GB998877" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @error('tax_id')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <!-- Street Address -->
                <div class="sm:col-span-2">
                    <label for="biz_address" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Business Street Address</label>
                    <input type="text" name="address" id="biz_address" value="{{ old('address', $user->address) }}" placeholder="e.g. 100 Main Street, Suite 400" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @error('address')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <!-- City -->
                <div>
                    <label for="biz_city" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">City</label>
                    <input type="text" name="city" id="biz_city" value="{{ old('city', $user->city) }}" placeholder="e.g. New York / London / Lahore" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @error('city')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <!-- Country -->
                <div>
                    <label for="biz_country" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Country</label>
                    <input type="text" name="country" id="biz_country" value="{{ old('country', $user->country) }}" placeholder="e.g. United States, Pakistan, UAE" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    @error('country')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Footer Save -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-400">Updates your business identity & billing address.</span>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Save Business Details
                </button>
            </div>
        </form>
    </div>

    <!-- SECTION 3: Invoicing Defaults (Saves Independently) -->
    <div x-show="activeTab === 'invoicing'" x-cloak class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-5 gap-2">
            <div>
                <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2.5">
                    <i data-lucide="receipt" class="w-5 h-5 text-emerald-600 dark:text-emerald-400"></i>
                    Default Invoicing Preferences
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Set your default currency and standard remittance instructions. Pre-fills automatically on every new invoice.</p>
            </div>
            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1 rounded-full w-fit">
                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                Independent Section
            </span>
        </div>

        <form method="POST" action="{{ route('profile.invoicing') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="inv_default_currency" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Default Invoice Currency</label>
                <select name="default_currency" id="inv_default_currency" class="w-full sm:w-72 px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition font-medium">
                    @foreach(['USD' => 'USD ($) - US Dollar', 'EUR' => 'EUR (€) - Euro', 'GBP' => 'GBP (£) - British Pound', 'CAD' => 'CAD ($) - Canadian Dollar', 'AUD' => 'AUD ($) - Australian Dollar', 'PKR' => 'PKR (₨) - Pakistani Rupee', 'INR' => 'INR (₹) - Indian Rupee', 'AED' => 'AED (د.إ) - UAE Dirham', 'SAR' => 'SAR (﷼) - Saudi Riyal', 'JPY' => 'JPY (¥) - Japanese Yen'] as $code => $lbl)
                        <option value="{{ $code }}" {{ old('default_currency', $user->default_currency ?? 'USD') === $code ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                @error('default_currency')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="inv_default_payment_instructions" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Default Remittance & Bank Instructions</label>
                <textarea name="default_payment_instructions" id="inv_default_payment_instructions" rows="3" placeholder="e.g. Wire Transfer Details:&#10;Bank: Chase Manhattan&#10;IBAN / Account #: US1234567890&#10;Routing: 021000021" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">{{ old('default_payment_instructions', $user->default_payment_instructions) }}</textarea>
                @error('default_payment_instructions')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="inv_default_notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Default Terms, Conditions & Notes</label>
                <textarea name="default_notes" id="inv_default_notes" rows="3" placeholder="e.g. Thank you for choosing our services! Please pay within 14 days of invoice date." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">{{ old('default_notes', $user->default_notes) }}</textarea>
                @error('default_notes')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <!-- Footer Save -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-400">Pre-fills new invoices automatically.</span>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Save Invoicing Defaults
                </button>
            </div>
        </form>
    </div>

    <!-- SECTION 4: Security & Password (Saves Independently) -->
    <div x-show="activeTab === 'security'" x-cloak class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-5 gap-2">
            <div>
                <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2.5">
                    <i data-lucide="lock" class="w-5 h-5 text-amber-500"></i>
                    Security & Password Update
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Ensure your account is using a strong, unique password to prevent unauthorized access.</p>
            </div>
            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-3 py-1 rounded-full w-fit">
                <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                Independent Section
            </span>
        </div>

        <form method="POST" action="{{ route('profile.password') }}" class="space-y-6">
            @csrf
            @method('PUT')

            @if(!empty($user->password))
            <div>
                <label for="sec_current_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Current Password *</label>
                <input type="password" name="current_password" id="sec_current_password" required placeholder="Enter current password" class="w-full sm:w-80 px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                @error('current_password')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="sec_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">New Password *</label>
                    <input type="password" name="password" id="sec_password" required placeholder="Minimum 8 characters" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                    @error('password')<p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="sec_password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Confirm New Password *</label>
                    <input type="password" name="password_confirmation" id="sec_password_confirmation" required placeholder="Repeat new password" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                </div>
            </div>

            <!-- Footer Save -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-400">Immediately changes your authentication credential.</span>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 text-white font-bold text-sm shadow-md transition flex items-center gap-2">
                    <i data-lucide="key" class="w-4 h-4"></i>
                    Update Password
                </button>
            </div>
        </form>
    </div>

    <!-- SECTION 5: Appearance & Theme (Applies Instantly) -->
    <div x-show="activeTab === 'appearance'" x-cloak class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-5">
            <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2.5">
                <i data-lucide="palette" class="w-5 h-5 text-purple-600 dark:text-purple-400"></i>
                Appearance & Theme
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Select your preferred user interface mode. Switches instantly across all pages.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-xl" x-data="{ currentTheme: localStorage.getItem('invoice_hub_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') }">
            <!-- Light Theme -->
            <button type="button" 
                    @click="applyTheme('light'); currentTheme = 'light'"
                    :class="currentTheme === 'light' ? 'ring-2 ring-blue-600 bg-blue-50/50 dark:bg-slate-800 border-blue-600 shadow-sm' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                    class="p-5 rounded-2xl border text-left transition flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 shadow-xs">
                    <i data-lucide="sun" class="w-6 h-6"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-sm text-slate-900 dark:text-white">Light Mode</span>
                        <span x-show="currentTheme === 'light'" class="w-2 h-2 rounded-full bg-blue-600"></span>
                    </div>
                    <span class="block text-xs text-slate-500 mt-0.5">Crisp, clean & bright interface</span>
                </div>
            </button>

            <!-- Dark Theme -->
            <button type="button" 
                    @click="applyTheme('dark'); currentTheme = 'dark'"
                    :class="currentTheme === 'dark' ? 'ring-2 ring-blue-600 bg-blue-50/50 dark:bg-slate-800 border-blue-600 shadow-sm' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                    class="p-5 rounded-2xl border text-left transition flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-950 text-indigo-300 flex items-center justify-center shrink-0 shadow-xs">
                    <i data-lucide="moon" class="w-6 h-6"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-sm text-slate-900 dark:text-white">Dark Mode</span>
                        <span x-show="currentTheme === 'dark'" class="w-2 h-2 rounded-full bg-blue-600"></span>
                    </div>
                    <span class="block text-xs text-slate-500 mt-0.5">Deep slate & eye-comfort theme</span>
                </div>
            </button>
        </div>
    </div>

</div>
@endsection
