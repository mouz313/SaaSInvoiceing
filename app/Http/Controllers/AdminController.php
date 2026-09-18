<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Package;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $totalUsers = User::count();
        $totalInvoices = Invoice::count();
        $totalRevenue = Transaction::where('status', 'completed')->sum('amount');
        $activePackagesCount = Package::where('is_active', true)->count();

        $recentTransactions = Transaction::with(['user', 'package'])
            ->latest()
            ->take(6)
            ->get();

        $recentUsers = User::with('package')
            ->latest()
            ->take(6)
            ->get();

        $popularPackages = Package::withCount('users')->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalInvoices',
            'totalRevenue',
            'activePackagesCount',
            'recentTransactions',
            'recentUsers',
            'popularPackages'
        ));
    }

    public function users(Request $request): View
    {
        $search = $request->input('search');

        $users = User::with(['package'])
            ->withCount(['invoices', 'clients'])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $packages = Package::where('is_active', true)->get();

        return view('admin.users', compact('users', 'packages', 'search'));
    }

    public function updateUserCredits(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'credits' => ['required', 'integer', 'min:0'],
        ]);

        $user->update(['invoice_credits' => $validated['credits']]);

        return back()->with('success', "Updated credits for {$user->name} to {$validated['credits']}.");
    }

    public function toggleUserRole(User $user): RedirectResponse
    {
        // Don't allow demoting the current logged-in admin if they are the only admin
        if ($user->id === auth()->id() && $user->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Cannot demote the only administrator.');
            }
        }

        $newRole = $user->role === 'admin' ? 'user' : 'admin';
        $user->update(['role' => $newRole]);

        return back()->with('success', "User {$user->name} is now a {$newRole}.");
    }

    public function invoices(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $invoices = Invoice::with(['user', 'client'])
            ->when($search, function ($query, $search) {
                $query->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.invoices', compact('invoices', 'search', 'status'));
    }

    public function packages(): View
    {
        $packages = Package::withCount('users')->get();

        return view('admin.packages', compact('packages'));
    }
}
