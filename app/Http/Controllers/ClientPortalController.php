<?php

namespace App\Http\Controllers;

use App\Mail\ClientPortalMagicLinkMail;
use App\Models\Client;
use App\Models\Estimate;
use App\Services\StatementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClientPortalController extends Controller
{
    public function accessViaToken(string $token): RedirectResponse
    {
        $client = Client::where('portal_access_token', $token)->first();

        if (! $client) {
            return redirect()->route('portal.login')
                ->with('error', 'Invalid portal access link. Please request a new one.');
        }

        if ($client->portal_token_expires_at && $client->portal_token_expires_at->isPast()) {
            return redirect()->route('portal.login')
                ->with('error', 'This portal access link has expired. Please request a new magic link below.');
        }

        session([
            'portal_client_id' => $client->id,
            'portal_token_authenticated' => true,
        ]);

        return redirect()->route('portal.dashboard')
            ->with('success', "Welcome to your Client Portal, {$client->name}!");
    }

    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('portal_client_id')) {
            return redirect()->route('portal.dashboard');
        }

        return view('portal.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $client = Client::where('email', $request->email)->first();

        if (! $client) {
            return back()->withInput()->with('error', 'No client record found for this email address.');
        }

        if ($request->filled('password')) {
            if ($client->password && Hash::check($request->password, $client->password)) {
                $request->session()->put('portal_client_id', $client->id);

                if ($client->must_change_password) {
                    return redirect()->route('portal.change-password')
                        ->with('info', 'Please set your private portal password to continue.');
                }

                return redirect()->route('portal.dashboard')
                    ->with('success', "Welcome back, {$client->name}!");
            }

            return back()->withInput()->with('error', 'Incorrect password. You can request a passwordless magic link below.');
        }

        // If no password provided, send magic access link
        return $this->sendMagicLink($client);
    }

    public function requestLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $client = Client::where('email', $request->email)->first();

        if ($client) {
            return $this->sendMagicLink($client);
        }

        return back()->with('info', 'If an active client account exists with that email, a secure login link has been dispatched.');
    }

    private function sendMagicLink(Client $client): RedirectResponse
    {
        $client->update([
            'portal_access_token' => Str::random(60),
            'portal_token_expires_at' => now()->addHours(48),
        ]);

        try {
            Mail::to($client->email)->send(new ClientPortalMagicLinkMail($client));

            return back()->with('success', "A secure portal link has been sent to {$client->email}. Please check your inbox.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to dispatch access email: '.$e->getMessage());
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('portal_client_id');

        return redirect()->route('portal.login')
            ->with('info', 'You have been safely logged out of your portal.');
    }

    public function dashboard(Request $request): View
    {
        /** @var Client $client */
        $client = $request->attributes->get('portalClient');

        $invoicesCount = $client->invoices()->count();
        $outstandingBalance = $client->totalOutstanding();
        $totalPaid = $client->totalPaid();
        $pendingEstimatesCount = $client->estimates()->where('status', 'sent')->count();

        $recentInvoices = $client->invoices()
            ->latest('invoice_date')
            ->take(5)
            ->get();

        $pendingEstimates = $client->estimates()
            ->where('status', 'sent')
            ->latest()
            ->take(5)
            ->get();

        $recentPayments = $client->invoicePayments()
            ->with('invoice')
            ->latest('invoice_payments.paid_at')
            ->take(5)
            ->get();

        return view('portal.dashboard', compact(
            'client',
            'invoicesCount',
            'outstandingBalance',
            'totalPaid',
            'pendingEstimatesCount',
            'recentInvoices',
            'pendingEstimates',
            'recentPayments'
        ));
    }

    public function invoices(Request $request): View
    {
        /** @var Client $client */
        $client = $request->attributes->get('portalClient');
        $status = $request->query('status', 'all');

        $invoicesQuery = $client->invoices()->latest('invoice_date');

        if ($status === 'unpaid') {
            $invoicesQuery->whereIn('status', ['sent', 'partially_paid', 'overdue']);
        } elseif ($status === 'paid') {
            $invoicesQuery->where('status', 'paid');
        } elseif ($status === 'overdue') {
            $invoicesQuery->where('status', 'overdue')
                ->orWhere(function ($q) {
                    $q->whereIn('status', ['sent', 'partially_paid'])
                        ->where('due_date', '<', now());
                });
        }

        $invoices = $invoicesQuery->paginate(15)->withQueryString();

        return view('portal.invoices', compact('client', 'invoices', 'status'));
    }

    public function estimates(Request $request): View
    {
        /** @var Client $client */
        $client = $request->attributes->get('portalClient');

        $estimates = $client->estimates()
            ->latest('estimate_date')
            ->paginate(15);

        return view('portal.estimates', compact('client', 'estimates'));
    }

    public function acceptEstimate(Request $request, Estimate $estimate): RedirectResponse
    {
        /** @var Client $client */
        $client = $request->attributes->get('portalClient');

        if ($estimate->client_id !== $client->id) {
            abort(403);
        }

        $estimate->accept();

        return back()->with('success', "Proposal #{$estimate->estimate_number} has been accepted. Thank you!");
    }

    public function declineEstimate(Request $request, Estimate $estimate): RedirectResponse
    {
        /** @var Client $client */
        $client = $request->attributes->get('portalClient');

        if ($estimate->client_id !== $client->id) {
            abort(403);
        }

        $estimate->decline();

        return back()->with('info', "Proposal #{$estimate->estimate_number} was marked as declined.");
    }

    public function payments(Request $request): View
    {
        /** @var Client $client */
        $client = $request->attributes->get('portalClient');

        $payments = $client->invoicePayments()
            ->with('invoice')
            ->latest('invoice_payments.paid_at')
            ->paginate(15);

        return view('portal.payments', compact('client', 'payments'));
    }

    public function statement(Request $request, StatementService $service): View
    {
        /** @var Client $client */
        $client = $request->attributes->get('portalClient');

        $range = $request->query('range', 'year_to_date');
        [$startDate, $endDate] = $this->resolveDateRange($range, $request->query('start_date'), $request->query('end_date'));

        $statement = $service->generate($client, $startDate, $endDate);

        return view('portal.statement', compact('client', 'statement', 'range', 'startDate', 'endDate'));
    }

    public function statementPdf(Request $request, StatementService $service): Response
    {
        /** @var Client $client */
        $client = $request->attributes->get('portalClient');

        $range = $request->query('range', 'year_to_date');
        [$startDate, $endDate] = $this->resolveDateRange($range, $request->query('start_date'), $request->query('end_date'));

        $statement = $service->generate($client, $startDate, $endDate);

        $pdf = Pdf::loadView('clients.statement_pdf', [
            'statement' => $statement,
            'isPdf' => true,
        ]);

        $fileName = "Statement_{$client->id}.pdf";

        return $pdf->download($fileName);
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function resolveDateRange(string $range, ?string $start = null, ?string $end = null): array
    {
        return match ($range) {
            'last_30_days' => [now()->subDays(30)->startOfDay(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year_to_date' => [now()->startOfYear(), now()->endOfDay()],
            'last_year' => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
            'all_time' => [null, null],
            'custom' => [
                $start ? Carbon::parse($start)->startOfDay() : null,
                $end ? Carbon::parse($end)->endOfDay() : null,
            ],
            default => [now()->startOfYear(), now()->endOfDay()],
        };
    }

    public function showChangePassword(Request $request): View
    {
        /** @var Client $client */
        $client = $request->attributes->get('portalClient');

        return view('portal.change-password', compact('client'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var Client $client */
        $client = $request->attributes->get('portalClient');

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $client->update([
            'password' => $request->password,
            'must_change_password' => false,
        ]);

        return redirect()->route('portal.dashboard')
            ->with('success', 'Your password has been successfully updated! Welcome to your Client Portal.');
    }
}
