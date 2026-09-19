<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $categoryId = $request->input('category');

        $categories = Auth::user()->productCategories()->withCount('products')->get();

        $products = Auth::user()->products()
            ->with('category')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('products.index', compact('products', 'categories', 'search', 'categoryId'));
    }

    public function create(): View
    {
        $categories = Auth::user()->productCategories()->get();

        return view('products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'sku' => ['nullable', 'string', 'max:100'],
        ]);

        if (! empty($validated['category_id'])) {
            $cat = ProductCategory::where('id', $validated['category_id'])->where('user_id', Auth::id())->first();
            if (! $cat) {
                $validated['category_id'] = null;
            }
        }

        Auth::user()->products()->create($validated);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $this->authorizeProduct($product);
        $categories = Auth::user()->productCategories()->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($product);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'sku' => ['nullable', 'string', 'max:100'],
        ]);

        if (! empty($validated['category_id'])) {
            $cat = ProductCategory::where('id', $validated['category_id'])->where('user_id', Auth::id())->first();
            if (! $cat) {
                $validated['category_id'] = null;
            }
        }

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeProduct($product);

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function storeCategory(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:50'],
        ]);

        $category = Auth::user()->productCategories()->create([
            'name' => $validated['name'],
            'color' => $validated['color'] ?? 'blue',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'category' => $category,
            ], 201);
        }

        return redirect()->back()->with('success', 'Category added successfully.');
    }

    public function destroyCategory(ProductCategory $category): RedirectResponse
    {
        if ($category->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this category.');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully.');
    }

    private function authorizeProduct(Product $product): void
    {
        if ($product->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this product.');
        }
    }
}
