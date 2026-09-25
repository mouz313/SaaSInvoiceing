<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SvgSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user();
        $user->load(['package']);

        return view('profile.edit', compact('user'));
    }

    /**
     * Section 1: Update personal identity & avatar.
     */
    public function updatePersonal(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_avatar')) {
            $this->deleteStoredAvatar($user);
            $validated['avatar_url'] = null;
        } elseif ($request->hasFile('avatar')) {
            $validated['avatar_url'] = $this->handleAvatarUpload($request, $user);
        }

        unset($validated['avatar'], $validated['remove_avatar']);

        $user->update($validated);

        return redirect()->route('profile.edit')
            ->with('success', 'Personal information updated successfully.')
            ->with('active_tab', 'personal');
    }

    /**
     * Section 2: Update business & address information.
     */
    public function updateBusiness(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
        ]);

        $user->update($validated);

        return redirect()->route('profile.edit')
            ->with('success', 'Business details updated successfully.')
            ->with('active_tab', 'business');
    }

    /**
     * Section 3: Update default invoice preferences & instructions.
     */
    public function updateInvoicing(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'default_currency' => ['nullable', 'string', 'in:USD,EUR,GBP,CAD,AUD,PKR,INR,AED,SAR,JPY'],
            'default_payment_instructions' => ['nullable', 'string', 'max:1000'],
            'default_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $user->update($validated);

        return redirect()->route('profile.edit')
            ->with('success', 'Default invoicing preferences updated successfully.')
            ->with('active_tab', 'invoicing');
    }

    /**
     * Legacy / Full update endpoint (supports both single-request and backward compatibility).
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'default_currency' => ['nullable', 'string', 'in:USD,EUR,GBP,CAD,AUD,PKR,INR,AED,SAR,JPY'],
            'default_notes' => ['nullable', 'string', 'max:1000'],
            'default_payment_instructions' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar_url'] = $this->handleAvatarUpload($request, $user);
        }

        unset($validated['avatar']);

        $user->update($validated);

        return redirect()->route('profile.edit')->with('success', 'Profile and invoicing settings saved successfully.');
    }

    /**
     * Section 4: Update password & security.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $rules = [
            'password' => ['required', 'confirmed', Password::min(8)],
        ];

        // If user already has a local password (not just OAuth), require current password
        if (! empty($user->password)) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validate($rules);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Invalidate active sessions on other devices
        Auth::logoutOtherDevices($validated['password']);

        return redirect()->route('profile.edit')
            ->with('success', 'Your password has been updated successfully.')
            ->with('active_tab', 'security');
    }

    /**
     * Upload an avatar using stream method to avoid PHP 8.5 Windows temp file issues.
     */
    private function handleAvatarUpload(Request $request, User $user): string
    {
        $this->deleteStoredAvatar($user);

        $avatarFile = $request->file('avatar');
        $rawExtension = strtolower($avatarFile->getClientOriginalExtension());
        $isSvg = $rawExtension === 'svg' || str_contains((string) $avatarFile->getMimeType(), 'svg');
        $extension = $isSvg ? 'svg' : ($avatarFile->guessExtension() ?: 'png');
        $filename = Str::random(40).'.'.$extension;
        $relativeDir = 'avatars/'.$filename;

        if ($isSvg) {
            try {
                $rawContent = file_get_contents($avatarFile->getPathname());
                $sanitized = SvgSanitizer::sanitize($rawContent ?: '');
                Storage::disk('public')->put($relativeDir, $sanitized);
            } catch (\Throwable $e) {
                abort(422, 'The uploaded SVG avatar is invalid or contains unsafe elements.');
            }
        } else {
            // Stream from getPathname() to avoid PHP 8.5 Windows getRealPath() returning false on temp files
            $stream = fopen($avatarFile->getPathname(), 'r');
            Storage::disk('public')->put($relativeDir, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return Storage::url($relativeDir);
    }

    /**
     * Delete stored avatar file if it exists locally in storage.
     */
    private function deleteStoredAvatar(User $user): void
    {
        if ($user->avatar_url && str_contains($user->avatar_url, '/storage/avatars/')) {
            $oldPath = str_replace('/storage/', '', $user->avatar_url);
            Storage::disk('public')->delete($oldPath);
        }
    }
}
