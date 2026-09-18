@extends('layouts.public')

@section('title', ($page->title ?? 'Contact Us') . ' — InvoiceHub')
@section('meta_description', $page->meta_description ?? 'Contact InvoiceHub support, sales, or enterprise advisory team.')

@section('content')
<div class="space-y-16 py-12 sm:py-16">

    <!-- Header Section -->
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
            <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
            <span>24/7 Global Support</span>
        </div>
        <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
            {{ $page->title ?? 'Get in Touch with Our Global Team' }}
        </h1>
        <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
            {{ $page->subtitle ?? 'Have questions regarding our subscription plans, custom invoice templates, or enterprise workflows? We are here to help.' }}
        </p>
    </section>

    <!-- Main Grid: Left info & Right Form -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

            <!-- Left Column: Contact Methods & Offices (5 cols) -->
            <div class="lg:col-span-5 space-y-6">

                <!-- Quick Help Cards -->
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2.5">
                        <i data-lucide="headset" class="w-5 h-5 text-blue-600 dark:text-blue-400"></i>
                        Direct Contact Channels
                    </h3>

                    <div class="space-y-4 text-sm">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                                <i data-lucide="mail" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-400 uppercase">Support Email</span>
                                <a href="mailto:{{ setting('support_email', 'support@invoicehub.test') }}" class="font-bold text-slate-900 dark:text-white hover:text-blue-600 transition">
                                    {{ setting('support_email', 'support@invoicehub.test') }}
                                </a>
                                <p class="text-[11px] text-slate-400 mt-0.5">Average response under 2 hours.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <i data-lucide="phone" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-400 uppercase">Phone & Hotline</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ setting('support_phone', '+1 (800) 555-0199') }}</span>
                                <p class="text-[11px] text-slate-400 mt-0.5">Mon–Fri, 9:00 AM – 6:00 PM EST.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                                <i data-lucide="clock" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-400 uppercase">Uptime & Status</span>
                                <span class="inline-flex items-center gap-1.5 font-bold text-emerald-600 dark:text-emerald-400 text-xs mt-0.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    All Systems Operational (99.9%)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Office Locations -->
                <div class="p-6 rounded-3xl bg-slate-100/70 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                        <i data-lucide="globe" class="w-4 h-4 text-indigo-500"></i>
                        Our Global Hubs
                    </h3>

                    @php
                        $locations = $page->content['office_locations'] ?? [
                            ['city' => 'San Francisco (HQ)', 'address' => '100 Market St, Suite 400', 'state' => 'CA 94105, USA', 'email' => 'sf@invoicehub.test'],
                            ['city' => 'London', 'address' => '30 St Mary Axe, Floor 14', 'state' => 'EC3A 8EP, UK', 'email' => 'london@invoicehub.test'],
                            ['city' => 'Singapore', 'address' => '10 Collyer Quay, Ocean Financial Centre', 'state' => '049315, Singapore', 'email' => 'sg@invoicehub.test'],
                        ];
                    @endphp

                    <div class="space-y-3 text-xs text-slate-600 dark:text-slate-400">
                        @foreach($locations as $loc)
                            <div class="p-3 rounded-2xl bg-white dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/60 space-y-0.5">
                                <p class="font-bold text-slate-900 dark:text-white">{{ $loc['city'] }}</p>
                                <p>{{ $loc['address'] }}, {{ $loc['state'] }}</p>
                                <p class="text-[11px] text-blue-600 dark:text-blue-400 pt-0.5">{{ $loc['email'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Right Column: Interactive Contact Form (7 cols) -->
            <div class="lg:col-span-7">
                <div class="p-8 sm:p-10 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
                    <div>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white">Send Us a Direct Message</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Fill out the form below and our customer success team will reach out within 2 business hours.</p>
                    </div>

                    <form action="{{ route('pages.contact.submit') }}" method="POST" class="space-y-5">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Your Full Name *</label>
                                <input type="text" name="name" id="name" value="{{ old('name', auth()->user()->name ?? '') }}" required placeholder="e.g. Alex Morgan" class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                @error('name')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Business Email *</label>
                                <input type="email" name="email" id="email" value="{{ old('email', auth()->user()->email ?? '') }}" required placeholder="alex@company.com" class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                @error('email')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Phone Number (Optional)</label>
                                <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" placeholder="+1 (555) 000-0000" class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                @error('phone')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                            </div>

                            <!-- Subject -->
                            <div>
                                <label for="subject" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Topic / Subject *</label>
                                <select name="subject" id="subject" required class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                    <option value="" disabled {{ old('subject') ? '' : 'selected' }}>Select an inquiry topic</option>
                                    <option value="Subscription & Billing Inquiry" {{ old('subject') == 'Subscription & Billing Inquiry' ? 'selected' : '' }}>Subscription & Billing Inquiry</option>
                                    <option value="Custom Invoice Template Request" {{ old('subject') == 'Custom Invoice Template Request' ? 'selected' : '' }}>Custom Invoice Template Request</option>
                                    <option value="Enterprise & High Volume Plan" {{ old('subject') == 'Enterprise & High Volume Plan' ? 'selected' : '' }}>Enterprise & High Volume Plan</option>
                                    <option value="Technical Support or Bug Report" {{ old('subject') == 'Technical Support or Bug Report' ? 'selected' : '' }}>Technical Support or Bug Report</option>
                                    <option value="Partnership & Integration" {{ old('subject') == 'Partnership & Integration' ? 'selected' : '' }}>Partnership & Integration</option>
                                </select>
                                @error('subject')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <!-- Message Body -->
                        <div>
                            <label for="message" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Your Detailed Message *</label>
                            <textarea name="message" id="message" rows="5" required placeholder="Describe your question, request, or proposal..." class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">{{ old('message') }}</textarea>
                            @error('message')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button type="submit" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-lg shadow-blue-600/25 transition flex items-center justify-center gap-2">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                <span>Send Message</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </section>

</div>
@endsection
