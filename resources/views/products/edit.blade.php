@extends('layouts.app')

@section('title', 'Edit Product — ' . $product->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="edit-3" class="w-6 h-6 text-blue-600"></i>
                Edit Product
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Update item pricing or details in your catalog</p>
        </div>
        <a href="{{ route('products.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 flex items-center gap-1">
            &larr; Back to Catalog
        </a>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-xs">
        <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                    Product / Item Name *
                </label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                @error('name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div x-data="{
                    categories: {{ Js::from($categories) }},
                    selectedCategoryId: '{{ old('category_id', $product->category_id) }}',
                    showAddCategory: false,
                    newCategoryName: '',
                    isLoading: false,
                    errorMessage: '',
                    async createCategory() {
                        if (!this.newCategoryName.trim()) return;
                        this.isLoading = true;
                        this.errorMessage = '';
                        try {
                            const response = await fetch('{{ route('product-categories.store') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ name: this.newCategoryName.trim() })
                            });
                            const data = await response.json();
                            if (response.ok && data.category) {
                                this.categories.push(data.category);
                                this.selectedCategoryId = String(data.category.id);
                                this.newCategoryName = '';
                                this.showAddCategory = false;
                            } else {
                                this.errorMessage = data.message || (data.errors && data.errors.name ? data.errors.name[0] : 'Failed to create category');
                            }
                        } catch (err) {
                            this.errorMessage = 'Network error. Try again.';
                        } finally {
                            this.isLoading = false;
                        }
                    }
                }">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Category
                        </label>
                        <button type="button" @click="showAddCategory = !showAddCategory" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                            <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                            <span x-text="showAddCategory ? 'Cancel' : '+ Add New Category'"></span>
                        </button>
                    </div>

                    <!-- Inline Quick Add Input -->
                    <div x-show="showAddCategory" x-cloak class="mb-2 p-2 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 space-y-1.5 shadow-2xs">
                        <div class="flex items-center gap-1.5">
                            <input type="text" x-model="newCategoryName" @keydown.enter.prevent="createCategory()" placeholder="New category name (e.g. Food, Clothing)..."
                                   class="flex-1 px-3 py-1.5 rounded-lg border border-blue-300 dark:border-blue-700 bg-white dark:bg-slate-900 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <button type="button" @click="createCategory()" :disabled="isLoading"
                                    class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold disabled:opacity-50 transition shadow-2xs">
                                <span x-show="!isLoading">Save</span>
                                <span x-show="isLoading">...</span>
                            </button>
                        </div>
                        <p x-show="errorMessage" x-text="errorMessage" class="text-[11px] text-rose-600 font-medium"></p>
                    </div>

                    <select name="category_id" x-model="selectedCategoryId" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="">-- Select Category --</option>
                        <template x-for="category in categories" :key="category.id">
                            <option :value="category.id" x-text="category.name" :selected="category.id == selectedCategoryId"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Default Price ($) *
                    </label>
                    <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-bold">
                    @error('price') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Unit of Measurement
                    </label>
                    <input type="text" name="unit" value="{{ old('unit', $product->unit) }}" placeholder="item, pcs, hrs, kg, etc."
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        SKU / Item Code (Optional)
                    </label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" placeholder="e.g. CLOTH-001"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                    Description / Specifications (Optional)
                </label>
                <textarea name="description" rows="3"
                          class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-slate-800">
                <a href="{{ route('products.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold text-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Update Product
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
