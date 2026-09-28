<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\Webhook;
use App\Services\WebhookDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeveloperController extends Controller
{
    /**
     * Display developer settings (API Keys & Webhooks).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $apiKeys = $user->apiKeys()->latest()->get();
        $webhooks = $user->webhooks()->with('logs')->latest()->get();

        return view('settings.developer', compact('apiKeys', 'webhooks'));
    }

    /**
     * Generate a new API key.
     */
    public function storeKey(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $result = ApiKey::generate($request->user(), $validated['name']);

        return redirect()->route('settings.developer')
            ->with('new_api_key', $result['key'])
            ->with('success', '🔑 API Key generated successfully! Make sure to copy it now, you will not see it again.');
    }

    /**
     * Revoke / delete an API key.
     */
    public function destroyKey(Request $request, ApiKey $apiKey): RedirectResponse
    {
        if ($apiKey->user_id !== $request->user()->id) {
            abort(403);
        }

        $apiKey->delete();

        return redirect()->route('settings.developer')
            ->with('success', 'API Key revoked successfully.');
    }

    /**
     * Register a new webhook endpoint.
     */
    public function storeWebhook(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:255'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string', 'in:invoice.created,invoice.paid,invoice.payment_received,invoice.overdue,*'],
        ]);

        $request->user()->webhooks()->create([
            'url' => $validated['url'],
            'events' => $validated['events'],
            'is_active' => true,
        ]);

        return redirect()->route('settings.developer')
            ->with('success', '🌐 Webhook endpoint registered successfully!');
    }

    /**
     * Send test ping to verify webhook endpoint.
     */
    public function pingWebhook(Request $request, Webhook $webhook): RedirectResponse
    {
        if ($webhook->user_id !== $request->user()->id) {
            abort(403);
        }

        $log = WebhookDispatcher::send($webhook, 'invoice.created', [
            'test' => true,
            'message' => 'Test ping event from InvoiceHub Developer Hub',
            'timestamp' => now()->toIso8601String(),
        ]);

        if ($log->successful) {
            return redirect()->route('settings.developer')
                ->with('success', "✅ Test webhook delivered successfully! HTTP {$log->response_status}");
        }

        return redirect()->route('settings.developer')
            ->with('error', "⚠️ Webhook delivery failed: HTTP {$log->response_status} ({$log->response_body})");
    }

    /**
     * Delete webhook endpoint.
     */
    public function destroyWebhook(Request $request, Webhook $webhook): RedirectResponse
    {
        if ($webhook->user_id !== $request->user()->id) {
            abort(403);
        }

        $webhook->delete();

        return redirect()->route('settings.developer')
            ->with('success', 'Webhook endpoint removed.');
    }
}
