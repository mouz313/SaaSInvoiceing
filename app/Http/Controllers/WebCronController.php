<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class WebCronController extends Controller
{
    /**
     * Handle external web-based cron requests (e.g. from cron-job.org, EasyCron, curl).
     */
    public function run(Request $request, ?string $token = null): JsonResponse
    {
        // Accept token from URL parameter, query string, request body, or header
        $providedToken = $token
            ?: $request->query('key')
            ?: $request->query('token')
            ?: $request->input('key')
            ?: $request->header('X-Cron-Key');

        $savedToken = Setting::get('cron_secret_token');

        // Auto-generate a secret token if one has never been set
        if (empty($savedToken)) {
            $savedToken = Str::random(32);
            Setting::set('cron_secret_token', $savedToken, 'cron', 'string');
        }

        // Validate secret token
        if (! $providedToken || ! hash_equals($savedToken, (string) $providedToken)) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized: Invalid or missing cron secret key.',
                'hint' => 'Ensure your request URL matches the Web Cron URL provided in Admin Settings > Cron & Automations.',
            ], 403);
        }

        $now = now();
        $task = $request->query('task', 'all');
        $results = [];

        try {
            // 1. Scheduler standard run
            Artisan::call('schedule:run');
            $scheduleOutput = trim(Artisan::output());
            $results['scheduler'] = $scheduleOutput ?: 'No pending tasks at exact minute.';

            // 2. Process Recurring Invoices (if requested or by default)
            if (in_array($task, ['all', 'recurring'], true)) {
                Artisan::call('invoices:process-recurring');
                $results['recurring_invoices'] = trim(Artisan::output());
            }

            // 3. Process Payment Reminders & Overdue Sync (if requested or by default)
            if (in_array($task, ['all', 'reminders'], true)) {
                Artisan::call('invoices:send-reminders');
                $results['payment_reminders'] = trim(Artisan::output());
            }

            // Record heartbeat & web run timestamps
            Setting::set('cron_last_heartbeat_at', $now->toDateTimeString(), 'cron', 'string');
            Setting::set('cron_last_web_run_at', $now->toDateTimeString(), 'cron', 'string');

            return response()->json([
                'success' => true,
                'message' => 'Automated cron tasks executed successfully.',
                'triggered_at' => $now->toDateTimeString(),
                'ip' => $request->ip(),
                'results' => $results,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Execution error: '.$e->getMessage(),
                'timestamp' => $now->toDateTimeString(),
            ], 500);
        }
    }
}
