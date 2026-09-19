<?php

namespace App\Http\Controllers;

use App\Mail\ClientPortalMagicLinkMail;
use App\Mail\ClientStatementMail;
use App\Models\Client;
use App\Services\StatementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');

        $clients = Auth::user()->clients()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            })
            ->withCount('invoices')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('clients.index', compact('clients', 'search'));
    }

    public function create(): View|RedirectResponse
    {
        if (! Auth::user()->canCreateClient()) {
            return redirect()->route('clients.index')
                ->with('error', 'You have reached your tier limit of '.Auth::user()->clientLimit().' clients. Please upgrade your package to add more clients.');
        }

        return view('clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if (! Auth::user()->canCreateClient()) {
            return redirect()->route('clients.index')
                ->with('error', 'You have reached your tier limit of '.Auth::user()->clientLimit().' clients. Please upgrade your package to add more clients.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'max:10'],
        ]);

        Auth::user()->clients()->create($validated);

        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    public function show(Client $client): View
    {
        $this->authorizeClient($client);

        $client->loadMissing(['invoices' => fn ($q) => $q->latest('invoice_date')->take(10), 'estimates' => fn ($q) => $q->latest('estimate_date')->take(5)]);
        $recentPayments = $client->invoicePayments()->with('invoice')->latest('invoice_payments.paid_at')->take(10)->get();

        return view('clients.show', compact('client', 'recentPayments'));
    }

    public function edit(Client $client): View
    {
        $this->authorizeClient($client);

        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClient($client);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'max:10'],
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorizeClient($client);

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }

    public function statement(Request $request, Client $client, StatementService $service): View
    {
        $this->authorizeClient($client);

        $range = $request->query('range', 'year_to_date');
        [$startDate, $endDate] = $this->resolveDateRange($range, $request->query('start_date'), $request->query('end_date'));

        $statement = $service->generate($client, $startDate, $endDate);

        return view('clients.statement', compact('client', 'statement', 'range', 'startDate', 'endDate'));
    }

    public function statementPdf(Request $request, Client $client, StatementService $service): Response
    {
        $this->authorizeClient($client);

        $range = $request->query('range', 'year_to_date');
        [$startDate, $endDate] = $this->resolveDateRange($range, $request->query('start_date'), $request->query('end_date'));

        $statement = $service->generate($client, $startDate, $endDate);

        $pdf = Pdf::loadView('clients.statement_pdf', [
            'statement' => $statement,
            'isPdf' => true,
        ]);

        $fileName = "Statement_{$client->name}.pdf";

        return $pdf->download($fileName);
    }

    public function sendStatementEmail(Request $request, Client $client, StatementService $service): RedirectResponse
    {
        $this->authorizeClient($client);

        if (empty($client->email)) {
            return back()->with('error', 'This client does not have an email address configured.');
        }

        $range = $request->input('range', 'year_to_date');
        [$startDate, $endDate] = $this->resolveDateRange($range, $request->input('start_date'), $request->input('end_date'));

        $statement = $service->generate($client, $startDate, $endDate);
        $customMessage = $request->input('custom_message', '');

        try {
            Mail::to($client->email)->send(new ClientStatementMail($client, $statement, $customMessage));

            return back()->with('success', "Statement successfully emailed to {$client->email}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to send statement email: '.$e->getMessage());
        }
    }

    public function sendPortalLinkEmail(Client $client): RedirectResponse
    {
        $this->authorizeClient($client);

        if (empty($client->email)) {
            return back()->with('error', 'This client does not have an email address configured.');
        }

        if (empty($client->portal_access_token)) {
            $client->update(['portal_access_token' => Str::random(60)]);
        }

        try {
            Mail::to($client->email)->send(new ClientPortalMagicLinkMail($client));

            return back()->with('success', "Client portal access link emailed to {$client->email}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to send portal link: '.$e->getMessage());
        }
    }

    public function unbilledItems(Client $client): JsonResponse
    {
        $this->authorizeClient($client);

        $timeEntries = Auth::user()->timeEntries()
            ->where('client_id', $client->id)
            ->unbilled()
            ->latest('date')
            ->get();

        $expenses = Auth::user()->expenses()
            ->where('client_id', $client->id)
            ->unbilled()
            ->billable()
            ->latest('expense_date')
            ->get();

        return response()->json([
            'time_entries' => $timeEntries,
            'expenses' => $expenses,
        ]);
    }

    private function authorizeClient(Client $client): void
    {
        if ($client->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this client.');
        }
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
}
