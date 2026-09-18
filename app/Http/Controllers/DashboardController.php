<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $invoicesQuery = Invoice::where('user_id', $user->id);

        $totalInvoices = (clone $invoicesQuery)->count();
        $totalPaid = (clone $invoicesQuery)->where('status', 'paid')->sum('total');
        $totalPending = (clone $invoicesQuery)->whereIn('status', ['draft', 'sent'])->sum('total');
        $totalOverdue = (clone $invoicesQuery)->where('status', 'overdue')->count();

        $recentInvoices = (clone $invoicesQuery)
            ->with('client')
            ->latest()
            ->take(5)
            ->get();

        $recentClients = $user->clients()
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'user',
            'totalInvoices',
            'totalPaid',
            'totalPending',
            'totalOverdue',
            'recentInvoices',
            'recentClients'
        ));
    }
}
