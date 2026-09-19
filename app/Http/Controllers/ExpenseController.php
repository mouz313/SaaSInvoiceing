<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $status = $request->query('status', 'all');
        $category = $request->query('category');
        $clientId = $request->query('client_id');

        $query = $user->expenses()->with('client', 'invoice')->latest('expense_date');

        if ($status === 'unbilled') {
            $query->unbilled();
        } elseif ($status === 'billed') {
            $query->where('is_billed', true);
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        $expenses = $query->paginate(15)->withQueryString();

        $totalUnbilled = (float) $user->expenses()->unbilled()->billable()->sum('amount');
        $totalAll = (float) $user->expenses()->sum('amount');
        $clients = $user->clients()->orderBy('name')->get();

        $categories = ['Software', 'Travel', 'Hosting', 'Supplies', 'Meals', 'Contractor', 'General'];

        return view('expenses.index', compact('expenses', 'totalUnbilled', 'totalAll', 'clients', 'categories', 'status', 'category', 'clientId'));
    }

    public function create(): View
    {
        $clients = Auth::user()->clients()->orderBy('name')->get();
        $categories = ['Software', 'Travel', 'Hosting', 'Supplies', 'Meals', 'Contractor', 'General'];

        return view('expenses.create', compact('clients', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['nullable', 'exists:clients,id'],
            'category' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'max:10'],
            'expense_date' => ['required', 'date'],
            'is_billable' => ['boolean'],
        ]);

        $validated['is_billable'] = $request->boolean('is_billable', true);
        $validated['currency'] = $validated['currency'] ?? (Auth::user()->default_currency ?? 'USD');

        if (! empty($validated['client_id'])) {
            $client = Auth::user()->clients()->find($validated['client_id']);
            if (! $client) {
                abort(403);
            }
        }

        Auth::user()->expenses()->create($validated);

        return redirect()->route('expenses.index')->with('success', 'Expense logged successfully.');
    }

    public function edit(Expense $expense): View
    {
        $this->authorizeExpense($expense);

        $clients = Auth::user()->clients()->orderBy('name')->get();
        $categories = ['Software', 'Travel', 'Hosting', 'Supplies', 'Meals', 'Contractor', 'General'];

        return view('expenses.edit', compact('expense', 'clients', 'categories'));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorizeExpense($expense);

        $validated = $request->validate([
            'client_id' => ['nullable', 'exists:clients,id'],
            'category' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'max:10'],
            'expense_date' => ['required', 'date'],
            'is_billable' => ['boolean'],
        ]);

        $validated['is_billable'] = $request->boolean('is_billable', true);

        if (! empty($validated['client_id'])) {
            $client = Auth::user()->clients()->find($validated['client_id']);
            if (! $client) {
                abort(403);
            }
        }

        $expense->update($validated);

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorizeExpense($expense);

        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }

    private function authorizeExpense(Expense $expense): void
    {
        if ($expense->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
