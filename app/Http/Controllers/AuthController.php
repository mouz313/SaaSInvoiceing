<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use App\Services\FirebaseTokenVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (Auth::user()->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'invoice_credits' => 5, // 5 free starter credits
        ]);

        try {
            Mail::to($user->email)->send(new WelcomeUserMail($user));
        } catch (\Throwable $e) {
            Log::warning('Welcome email could not be sent: '.$e->getMessage());
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Account created! You received 5 free invoice credits.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'You have been logged out.');
    }

    public function demoLogin(string $role): RedirectResponse
    {
        if (! app()->environment('local', 'testing')) {
            abort(403, 'Demo login is disabled in this environment.');
        }

        if (! in_array($role, ['admin', 'user'])) {
            $role = 'user';
        }

        $user = User::where('role', $role)->first();

        if (! $user) {
            $user = User::create([
                'name' => $role === 'admin' ? 'System Administrator' : 'Demo Business User',
                'email' => $role === 'admin' ? 'admin@invoicehub.test' : 'demo@invoicehub.test',
                'password' => Hash::make('password123'),
                'role' => $role,
                'invoice_credits' => $role === 'admin' ? 9999 : 25,
            ]);
        }

        Auth::login($user);
        request()->session()->regenerate();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')->with('success', 'Logged in as Demo Admin!');
        }

        return redirect()->route('dashboard')->with('success', 'Logged in as Demo User!');
    }

    public function firebaseSession(Request $request, FirebaseTokenVerifier $verifier): JsonResponse
    {
        $validated = $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        $verifiedUser = $verifier->verify($validated['id_token']);

        if (! $verifiedUser) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid, expired, or unverified Firebase ID token.',
            ], 401);
        }

        $firebaseUid = $verifiedUser['uid'];
        $email = $verifiedUser['email'];
        $name = $verifiedUser['name'] ?: explode('@', $email)[0];
        $avatarUrl = $verifiedUser['avatar_url'] ?? null;

        $user = User::where('firebase_uid', $firebaseUid)
            ->orWhere('email', $email)
            ->first();

        if (! $user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'firebase_uid' => $firebaseUid,
                'avatar_url' => $avatarUrl,
                'role' => 'user',
                'invoice_credits' => 5,
            ]);

            try {
                Mail::to($user->email)->send(new WelcomeUserMail($user));
            } catch (\Throwable $e) {
                Log::warning('Welcome email could not be sent: '.$e->getMessage());
            }
        } else {
            $user->update([
                'firebase_uid' => $firebaseUid,
                'avatar_url' => $avatarUrl ?? $user->avatar_url,
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        $redirect = $user->isAdmin() ? route('admin.dashboard') : route('dashboard');

        return response()->json([
            'success' => true,
            'redirect' => $redirect,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }
}
