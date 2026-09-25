<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoiceTemplate;
use App\Models\UserTemplatePurchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplateController extends Controller
{
    /**
     * Display the Admin Templates Catalog & Pricing Manager.
     */
    public function index(): View
    {
        $templates = InvoiceTemplate::withCount('purchases')
            ->orderBy('sort_order')
            ->get();

        $totalRevenue = UserTemplatePurchase::sum('price_paid');
        $totalSales = UserTemplatePurchase::count();
        $activeCount = $templates->where('is_active', true)->count();

        return view('admin.templates.index', compact('templates', 'totalRevenue', 'totalSales', 'activeCount'));
    }

    /**
     * Update template pricing, active status, and metadata.
     */
    public function update(Request $request, InvoiceTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_free' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $isFree = $request->boolean('is_free') || (float) $validated['price'] <= 0;

        $template->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'price' => $isFree ? 0.00 : (float) $validated['price'],
            'is_free' => $isFree,
            'is_active' => $request->boolean('is_active'),
            'description' => $validated['description'],
        ]);

        return redirect()->route('admin.templates.index')
            ->with('success', "Template '{$template->name}' updated successfully.");
    }

    /**
     * Toggle active status of a template.
     */
    public function toggleActive(InvoiceTemplate $template): RedirectResponse
    {
        $template->update([
            'is_active' => ! $template->is_active,
        ]);

        $status = $template->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.templates.index')
            ->with('success', "Template '{$template->name}' has been {$status}.");
    }
}
