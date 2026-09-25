<?php

namespace App\Http\Controllers;

use App\Models\UserLogo;
use App\Services\SvgSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserLogoController extends Controller
{
    public function index(): JsonResponse
    {
        $logos = Auth::user()->logos()->latest()->get()->map(fn (UserLogo $logo) => [
            'id' => $logo->id,
            'filename' => $logo->filename,
            'url' => $logo->url,
        ]);

        return response()->json($logos);
    }

    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->logos()->count() >= UserLogo::MAX_LOGOS_PER_USER) {
            return response()->json([
                'error' => 'You have reached the maximum of '.UserLogo::MAX_LOGOS_PER_USER.' logos. Please delete an existing logo first.',
            ], 422);
        }

        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
        ]);

        $file = $request->file('logo');
        $originalName = strip_tags($file->getClientOriginalName());
        $rawExtension = strtolower($file->getClientOriginalExtension());
        $isSvg = $rawExtension === 'svg' || str_contains((string) $file->getMimeType(), 'svg');
        $extension = $isSvg ? 'svg' : ($file->guessExtension() ?: 'png');
        $filename = Str::random(40).'.'.$extension;
        $relativePath = 'logos/'.$user->id.'/'.$filename;

        if ($isSvg) {
            try {
                $rawContent = file_get_contents($file->getPathname());
                $sanitized = SvgSanitizer::sanitize($rawContent ?: '');
                Storage::disk('public')->put($relativePath, $sanitized);
            } catch (\Throwable $e) {
                return response()->json([
                    'error' => 'The uploaded SVG file is invalid or contains unsafe elements.',
                ], 422);
            }
        } else {
            // Stream from getPathname() to avoid PHP 8.5 Windows getRealPath() returning false on temp files
            $stream = fopen($file->getPathname(), 'r');
            Storage::disk('public')->put($relativePath, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        $logo = $user->logos()->create([
            'filename' => $originalName,
            'path' => $relativePath,
        ]);

        return response()->json([
            'id' => $logo->id,
            'filename' => $logo->filename,
            'url' => $logo->url,
        ], 201);
    }

    public function destroy(UserLogo $logo): JsonResponse
    {
        if ($logo->user_id !== Auth::id()) {
            abort(403);
        }

        Storage::disk('public')->delete($logo->path);
        $logo->delete();

        return response()->json(['success' => true]);
    }
}
