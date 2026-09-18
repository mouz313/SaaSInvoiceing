@extends('layouts.admin')

@section('title', 'Edit CMS Page — ' . ucfirst($page->slug))

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12">

    <!-- Breadcrumbs & Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('admin.cms.pages') }}" class="hover:text-blue-600 transition">CMS Pages</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-200 capitalize">Edit {{ $page->slug }}</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Edit {{ ucfirst($page->slug) }} Page</h1>
        </div>
        <a href="{{ route('admin.cms.pages') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 transition">
            &larr; Back to Pages
        </a>
    </div>

    <!-- Edit Form -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm">
        <form action="{{ route('admin.cms.pages.update', $page->slug) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label for="title" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Page Headline / Main Title *</label>
                <input type="text" name="title" id="title" value="{{ old('title', $page->title) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                @error('title')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Subtitle -->
            <div>
                <label for="subtitle" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Subtitle / Secondary Headline</label>
                <textarea name="subtitle" id="subtitle" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">{{ old('subtitle', $page->subtitle) }}</textarea>
                @error('subtitle')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Meta Description (SEO) -->
            <div>
                <label for="meta_description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Meta Description (SEO)</label>
                <input type="text" name="meta_description" id="meta_description" value="{{ old('meta_description', $page->meta_description) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                <p class="text-[11px] text-slate-400 mt-1">Appears in Google search engine snippets and social link previews.</p>
                @error('meta_description')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            @if($page->slug === 'about')
                <!-- About Specific Content: Mission & Story -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-5">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">About Page Content Sections</h3>

                    <div>
                        <label for="mission_statement" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Mission Statement</label>
                        <textarea name="mission_statement" id="mission_statement" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">{{ old('mission_statement', $page->content['mission_statement'] ?? '') }}</textarea>
                    </div>

                    <div>
                        <label for="story" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Company Origin Story</label>
                        <textarea name="story" id="story" rows="4" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">{{ old('story', $page->content['story'] ?? '') }}</textarea>
                    </div>
                </div>
            @endif

            <!-- Published Toggle -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center gap-3">
                <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $page->is_published) ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-700">
                <label for="is_published" class="text-xs font-bold text-slate-800 dark:text-slate-200">
                    Publish this page publicly (visible on public routes)
                </label>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('admin.cms.pages') }}" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Page Changes</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
