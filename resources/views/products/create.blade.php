@extends('layouts.app')

@section('title', 'Add Product / Item')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="package-plus" class="w-6 h-6 text-blue-600"></i>
                Add Product or Item
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Add an item to your catalog to drop directly into invoices with 1 click</p>
        </div>
        <a href="{{ route('products.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 flex items-center gap-1">
            &larr; Back to Catalog
        </a>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-xs">
        <form method="POST" action="{{ route('products.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                    Product / Item Name *
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Premium Cotton Shirt, Italian Pasta, Hourly Consulting"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                @error('name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Category
                    </label>
                    <select name="category_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">E.g. Food, Clothes, Shoes, Services</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Default Price ($) *
                    </label>
                    <input type="number" step="0.01" min="0" name="price" value="{{ old('price', '0.00') }}" required placeholder="0.00"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-bold">
                    @error('price') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Unit of Measurement
                    </label>
                    <input type="text" name="unit" value="{{ old('unit', 'item') }}" placeholder="item, pcs, hrs, kg, etc."
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        SKU / Item Code (Optional)
                    </label>
                    <input type="text" name="sku" value="{{ old('sku') }}" placeholder="e.g. CLOTH-001"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                    Description / Specifications (Optional)
                </label>
                <textarea name="description" rows="3" placeholder="Additional details, features, or notes shown on invoice line item..."
                          class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">{{ old('description') }}</textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-slate-800">
                <a href="{{ route('products.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold text-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Save Product
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
