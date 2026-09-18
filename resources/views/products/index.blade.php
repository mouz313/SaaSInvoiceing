@extends('layouts.app')

@section('title', 'Products & Services')

@section('content')
<div class="space-y-6" x-data="{ categoryModal: false }">
    <!-- Top Action & Search Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="package" class="w-6 h-6 text-blue-600"></i>
                Products & Catalog
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Manage catalog items and services for 1-click invoice addition</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button @click="categoryModal = true" type="button" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs sm:text-sm transition border border-slate-200 dark:border-slate-700">
                <i data-lucide="tags" class="w-4 h-4 text-blue-600"></i>
                Manage Categories ({{ $categories->count() }})
            </button>

            <form method="GET" action="{{ route('products.index') }}" class="relative">
                @if($categoryId)
                    <input type="hidden" name="category" value="{{ $categoryId }}">
                @endif
                <input type="text" name="search" value="{{ $search }}" placeholder="Search items, SKU..." 
                       class="w-44 sm:w-56 pl-9 pr-4 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
            </form>

            <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-sm transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Product
            </a>
        </div>
    </div>

    <!-- Category Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-200 dark:border-slate-800 scrollbar-none">
        <a href="{{ route('products.index', array_filter(['search' => $search])) }}" 
           class="px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition {{ !$categoryId ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
            All Products ({{ Auth::user()->products()->count() }})
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('products.index', array_filter(['category' => $cat->id, 'search' => $search])) }}" 
               class="px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition flex items-center gap-1.5 {{ $categoryId == $cat->id ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                {{ $cat->name }}
                <span class="text-[10px] opacity-75">({{ $cat->products_count }})</span>
            </a>
        @endforeach
    </div>

    <!-- Products Table Card -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        @if($products->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <i data-lucide="package" class="w-6 h-6"></i>
                </div>
                <h4 class="font-bold text-slate-800 dark:text-slate-200 text-base">No products found</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                    @if($search || $categoryId)
                        No items matched your active filters. Try resetting the category or search.
                    @else
                        Create your product catalog (e.g. Food, Clothes, Shoes, or Services) to add them to invoices in 1 click!
                    @endif
                </p>
                <div class="mt-4 flex items-center justify-center gap-2">
                    @if($search || $categoryId)
                        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold text-xs">
                            Clear Filters
                        </a>
                    @endif
                    <a href="{{ route('products.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add First Product
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5">Item / Service</th>
                            <th class="px-6 py-3.5">Category</th>
                            <th class="px-6 py-3.5">SKU / Code</th>
                            <th class="px-6 py-3.5 text-right">Default Price</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($products as $product)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $product->name }}</div>
                                @if($product->description)
                                    <div class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-md">{{ $product->description }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($product->category)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/50 dark:border-blue-800/50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        {{ $product->category->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">Uncategorized</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-600 dark:text-slate-300">
                                {{ $product->sku ?: '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-extrabold text-slate-900 dark:text-white text-base">
                                    ${{ number_format($product->price, 2) }}
                                </span>
                                <span class="text-xs text-slate-400 block">/ {{ $product->unit ?: 'item' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('products.edit', $product) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Edit Product">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Delete {{ addslashes($product->name) }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition" title="Delete Product">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- Category Management Modal -->
    <div x-show="categoryModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs" 
         style="display: none;">
        
        <div @click.away="categoryModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i data-lucide="tags" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-slate-900 dark:text-white">Manage Product Categories</h3>
                        <p class="text-xs text-slate-500">Group items like Food, Clothes, Shoes, Electronics</p>
                    </div>
                </div>
                <button @click="categoryModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Add Category Form -->
            <form action="{{ route('product-categories.store') }}" method="POST" class="space-y-3 bg-slate-50 dark:bg-slate-850 p-4 rounded-xl border border-slate-200 dark:border-slate-700/60">
                @csrf
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Add New Category</label>
                <div class="flex gap-2">
                    <input type="text" name="name" placeholder="e.g. Food, Clothes, Shoes, Consulting" required
                           class="flex-1 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs shrink-0 flex items-center gap-1.5">
                        <i data-lucide="plus" class="w-4 h-4"></i> Add
                    </button>
                </div>
            </form>

            <!-- Existing Categories List -->
            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Existing Categories ({{ $categories->count() }})</label>
                @if($categories->isEmpty())
                    <p class="text-xs text-slate-400 italic py-2">No categories yet. Add one above.</p>
                @else
                    <div class="max-h-60 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/60 pr-1">
                        @foreach($categories as $cat)
                            <div class="py-2.5 flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $cat->name }}</span>
                                    <span class="text-[11px] text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full">
                                        {{ $cat->products_count }} {{ Str::plural('item', $cat->products_count) }}
                                    </span>
                                </div>
                                <form action="{{ route('product-categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Delete category {{ addslashes($cat->name) }}? Associated items will become uncategorized.');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-rose-600 p-1 rounded-lg transition" title="Delete category">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex justify-end pt-3 border-t border-slate-200 dark:border-slate-800">
                <button @click="categoryModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200">
                    Done
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
