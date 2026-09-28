<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TeamController extends Controller
{
    /**
     * Display team management dashboard.
     */
    public function index(Request $request): View
    {
        $owner = $request->user();

        $members = TeamMember::with('user')
            ->where('owner_id', $owner->id)
            ->latest()
            ->get();

        return view('teams.index', compact('members'));
    }

    /**
     * Invite or add a new team member.
     */
    public function store(Request $request): RedirectResponse
    {
        $owner = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', 'in:admin,accountant,viewer'],
        ]);

        if (strtolower($validated['email']) === strtolower($owner->email)) {
            return back()->with('error', 'You are already the owner of this organization account.');
        }

        // Check if user exists or create account for them
        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make(Str::random(16)),
                'company_name' => $owner->company_name,
                'default_currency' => $owner->default_currency ?? 'PKR',
            ]);
        }

        // Check if already on the team
        $existing = TeamMember::where('owner_id', $owner->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return back()->with('error', 'This user is already a member of your team.');
        }

        TeamMember::create([
            'owner_id' => $owner->id,
            'user_id' => $user->id,
            'role' => $validated['role'],
            'status' => 'active',
        ]);

        return redirect()->route('teams.index')
            ->with('success', "👥 {$user->name} has been added to your team as {$validated['role']}!");
    }

    /**
     * Update team member's role.
     */
    public function update(Request $request, int|string $id): RedirectResponse
    {
        $team = TeamMember::findOrFail($id);

        if ($team->owner_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,accountant,viewer'],
        ]);

        $team->update(['role' => $validated['role']]);

        return redirect()->route('teams.index')
            ->with('success', "Role updated to {$validated['role']}.");
    }

    /**
     * Remove member from team.
     */
    public function destroy(Request $request, int|string $id): RedirectResponse
    {
        $team = TeamMember::findOrFail($id);

        if ($team->owner_id !== $request->user()->id) {
            abort(403);
        }

        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', 'Team member removed.');
    }
}
