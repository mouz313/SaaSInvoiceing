@extends('layouts.admin')

@section('title', 'CMS Pages Management')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">CMS Pages Management</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Manage public website pages (About Us, Contact Us, FAQ) and marketing content.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('home') }}" target="_blank" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition flex items-center gap-1.5">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                <span>View Live Website</span>
            </a>
        </div>
    </div>

    <!-- Pages Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] uppercase font-extrabold text-slate-400 dark:text-slate-400 tracking-wider">
                    <tr>
                        <th class="py-4 px-6">Page Name & Route</th>
                        <th class="py-4 px-6">Headline Title</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6">Last Updated</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @foreach($pages as $page)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs uppercase">
                                        {{ substr($page->slug, 0, 2) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white capitalize">{{ $page->slug }} Page</p>
                                        <p class="text-xs text-slate-400 font-mono">/{{ $page->slug === 'home' ? '' : $page->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 max-w-xs">
                                <p class="font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $page->title }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ $page->subtitle }}</p>
                            </td>
                            <td class="py-4 px-6">
                                @if($page->is_published)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-500">
                                {{ $page->updated_at->diffForHumans() }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('admin.cms.pages.edit', $page->slug) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/60 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-bold text-xs transition">
                                    <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                    <span>Edit Content</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
