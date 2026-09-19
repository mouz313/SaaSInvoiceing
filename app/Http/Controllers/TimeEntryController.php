<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TimeEntryController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $status = $request->query('status', 'all');
        $clientId = $request->query('client_id');

        $query = $user->timeEntries()->with('client', 'invoice')->latest('date');

        if ($status === 'unbilled') {
            $query->unbilled();
        } elseif ($status === 'billed') {
            $query->where('is_billed', true);
        }

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        $timeEntries = $query->paginate(15)->withQueryString();

        $totalUnbilledHours = (float) $user->timeEntries()->unbilled()->sum('hours');
        $totalUnbilledAmount = (float) $user->timeEntries()->unbilled()->sum('total_amount');
        $clients = $user->clients()->orderBy('name')->get();

        return view('time.index', compact('timeEntries', 'totalUnbilledHours', 'totalUnbilledAmount', 'clients', 'status', 'clientId'));
    }

    public function create(): View
    {
        $clients = Auth::user()->clients()->orderBy('name')->get();

        return view('time.create', compact('clients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['nullable', 'exists:clients,id'],
            'project_name' => ['nullable', 'string', 'max:255'],
            'task_description' => ['required', 'string'],
            'hours' => ['required', 'numeric', 'min:0.01'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
        ]);

        if (! empty($validated['client_id'])) {
            $client = Auth::user()->clients()->find($validated['client_id']);
            if (! $client) {
                abort(403);
            }
        }

        Auth::user()->timeEntries()->create($validated);

        return redirect()->route('time.index')->with('success', 'Time entry logged successfully.');
    }

    public function edit(TimeEntry $timeEntry): View
    {
        $this->authorizeEntry($timeEntry);

        $clients = Auth::user()->clients()->orderBy('name')->get();

        return view('time.edit', compact('timeEntry', 'clients'));
    }

    public function update(Request $request, TimeEntry $timeEntry): RedirectResponse
    {
        $this->authorizeEntry($timeEntry);

        $validated = $request->validate([
            'client_id' => ['nullable', 'exists:clients,id'],
            'project_name' => ['nullable', 'string', 'max:255'],
            'task_description' => ['required', 'string'],
            'hours' => ['required', 'numeric', 'min:0.01'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
        ]);

        if (! empty($validated['client_id'])) {
            $client = Auth::user()->clients()->find($validated['client_id']);
            if (! $client) {
                abort(403);
            }
        }

        $timeEntry->update($validated);

        return redirect()->route('time.index')->with('success', 'Time entry updated successfully.');
    }

    public function destroy(TimeEntry $timeEntry): RedirectResponse
    {
        $this->authorizeEntry($timeEntry);

        $timeEntry->delete();

        return redirect()->route('time.index')->with('success', 'Time entry deleted.');
    }

    private function authorizeEntry(TimeEntry $timeEntry): void
    {
        if ($timeEntry->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
