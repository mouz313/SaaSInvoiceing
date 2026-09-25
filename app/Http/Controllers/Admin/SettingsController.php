<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $activeTab = $request->query('tab', 'general');

        $allSettings = Setting::all()->keyBy('key');

        $grouped = [
            'general' => [
                'app_name' => $allSettings['app_name']->value ?? config('app.name', 'InvoiceHub'),
                'support_email' => $allSettings['support_email']->value ?? 'support@invoicehub.test',
                'support_phone' => $allSettings['support_phone']->value ?? '+1 (800) 555-0199',
                'office_address' => $allSettings['office_address']->value ?? '100 Market St, Suite 400, San Francisco, CA 94105',
                'copyright_text' => $allSettings['copyright_text']->value ?? '© 2026 InvoiceHub Inc. All rights reserved.',
                'app_logo' => $allSettings['app_logo']->value ?? '',
                'app_favicon' => $allSettings['app_favicon']->value ?? '',
            ],
            'social' => [
                'social_twitter' => $allSettings['social_twitter']->value ?? '',
                'social_linkedin' => $allSettings['social_linkedin']->value ?? '',
                'social_github' => $allSettings['social_github']->value ?? '',
                'social_facebook' => $allSettings['social_facebook']->value ?? '',
                'social_instagram' => $allSettings['social_instagram']->value ?? '',
                'social_youtube' => $allSettings['social_youtube']->value ?? '',
            ],
            'stripe' => [
                'stripe_mode' => $allSettings['stripe_mode']->value ?? 'test',
                'stripe_publishable_key' => $allSettings['stripe_publishable_key']->value ?? config('services.stripe.key', ''),
                'stripe_secret_key' => $allSettings['stripe_secret_key']->value ?? config('services.stripe.secret', ''),
                'stripe_webhook_secret' => $allSettings['stripe_webhook_secret']->value ?? config('services.stripe.webhook_secret', ''),
                'stripe_currency' => $allSettings['stripe_currency']->value ?? 'usd',
            ],
            'firebase' => [
                'firebase_api_key' => $allSettings['firebase_api_key']->value ?? '',
                'firebase_auth_domain' => $allSettings['firebase_auth_domain']->value ?? '',
                'firebase_project_id' => $allSettings['firebase_project_id']->value ?? '',
                'firebase_storage_bucket' => $allSettings['firebase_storage_bucket']->value ?? '',
                'firebase_messaging_sender_id' => $allSettings['firebase_messaging_sender_id']->value ?? '',
                'firebase_app_id' => $allSettings['firebase_app_id']->value ?? '',
                'firebase_measurement_id' => $allSettings['firebase_measurement_id']->value ?? '',
            ],
            'cron' => [
                'cron_recurring_enabled' => Setting::get('cron_recurring_enabled', true),
                'cron_recurring_time' => Setting::get('cron_recurring_time', '06:00'),
                'cron_reminders_enabled' => Setting::get('cron_reminders_enabled', true),
                'cron_reminders_time' => Setting::get('cron_reminders_time', '08:00'),
                'cron_reminder_upcoming_days' => (int) Setting::get('cron_reminder_upcoming_days', 3),
                'cron_reminder_overdue_interval' => (int) Setting::get('cron_reminder_overdue_interval', 3),
                'cron_last_heartbeat_at' => Setting::get('cron_last_heartbeat_at', ''),
                'cron_last_recurring_run_at' => Setting::get('cron_last_recurring_run_at', ''),
                'cron_last_recurring_result' => Setting::get('cron_last_recurring_result', ''),
                'cron_last_reminders_run_at' => Setting::get('cron_last_reminders_run_at', ''),
                'cron_last_reminders_result' => Setting::get('cron_last_reminders_result', ''),
                'cron_secret_token' => Setting::get('cron_secret_token') ?: tap(Str::random(32), fn ($t) => Setting::set('cron_secret_token', $t, 'cron', 'string')),
                'cron_last_web_run_at' => Setting::get('cron_last_web_run_at', ''),
            ],
        ];

        return view('admin.settings.index', compact('grouped', 'activeTab'));
    }

    public function update(Request $request): RedirectResponse
    {
        $group = $request->input('group', 'general');

        $rules = match ($group) {
            'general' => [
                'app_name' => ['required', 'string', 'max:100'],
                'support_email' => ['required', 'email', 'max:255'],
                'support_phone' => ['nullable', 'string', 'max:50'],
                'office_address' => ['nullable', 'string', 'max:500'],
                'copyright_text' => ['nullable', 'string', 'max:255'],
                'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
                'favicon' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,webp,svg', 'max:1024'],
                'remove_logo' => ['nullable', 'boolean'],
                'remove_favicon' => ['nullable', 'boolean'],
            ],
            'social' => [
                'social_twitter' => ['nullable', 'url', 'max:255'],
                'social_linkedin' => ['nullable', 'url', 'max:255'],
                'social_github' => ['nullable', 'url', 'max:255'],
                'social_facebook' => ['nullable', 'url', 'max:255'],
                'social_instagram' => ['nullable', 'url', 'max:255'],
                'social_youtube' => ['nullable', 'url', 'max:255'],
            ],
            'stripe' => [
                'stripe_mode' => ['required', 'in:test,live'],
                'stripe_publishable_key' => ['required', 'string', 'max:255'],
                'stripe_secret_key' => ['required', 'string', 'max:255'],
                'stripe_webhook_secret' => ['nullable', 'string', 'max:255'],
                'stripe_currency' => ['required', 'string', 'max:10'],
            ],
            'firebase' => [
                'firebase_api_key' => ['required', 'string', 'max:255'],
                'firebase_auth_domain' => ['required', 'string', 'max:255'],
                'firebase_project_id' => ['required', 'string', 'max:255'],
                'firebase_storage_bucket' => ['nullable', 'string', 'max:255'],
                'firebase_messaging_sender_id' => ['nullable', 'string', 'max:255'],
                'firebase_app_id' => ['required', 'string', 'max:255'],
                'firebase_measurement_id' => ['nullable', 'string', 'max:255'],
            ],
            'cron' => [
                'cron_recurring_enabled' => ['nullable', 'boolean'],
                'cron_recurring_time' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
                'cron_reminders_enabled' => ['nullable', 'boolean'],
                'cron_reminders_time' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
                'cron_reminder_upcoming_days' => ['required', 'integer', 'min:1', 'max:30'],
                'cron_reminder_overdue_interval' => ['required', 'integer', 'min:1', 'max:30'],
            ],
            default => [],
        };

        $validated = $request->validate($rules);

        if ($group === 'cron') {
            $validated['cron_recurring_enabled'] = $request->boolean('cron_recurring_enabled');
            $validated['cron_reminders_enabled'] = $request->boolean('cron_reminders_enabled');
        }

        if ($group === 'general') {
            if ($request->boolean('remove_logo')) {
                $oldLogo = Setting::get('app_logo');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }
                Setting::set('app_logo', '', 'general');
            } elseif ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $oldLogo = Setting::get('app_logo');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }
                $extension = $file->getClientOriginalExtension() ?: 'png';
                $filename = 'logo_'.time().'_'.Str::random(8).'.'.$extension;
                $relativePath = 'site/'.$filename;
                $stream = fopen($file->getPathname(), 'r');
                Storage::disk('public')->put($relativePath, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                Setting::set('app_logo', $relativePath, 'general');
            }

            if ($request->boolean('remove_favicon')) {
                $oldFavicon = Setting::get('app_favicon');
                if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                    Storage::disk('public')->delete($oldFavicon);
                }
                Setting::set('app_favicon', '', 'general');
            } elseif ($request->hasFile('favicon')) {
                $file = $request->file('favicon');
                $oldFavicon = Setting::get('app_favicon');
                if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                    Storage::disk('public')->delete($oldFavicon);
                }
                $extension = $file->getClientOriginalExtension() ?: 'ico';
                $filename = 'favicon_'.time().'_'.Str::random(8).'.'.$extension;
                $relativePath = 'site/'.$filename;
                $stream = fopen($file->getPathname(), 'r');
                Storage::disk('public')->put($relativePath, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                Setting::set('app_favicon', $relativePath, 'general');
            }

            unset($validated['logo'], $validated['favicon'], $validated['remove_logo'], $validated['remove_favicon']);
        }

        foreach ($validated as $key => $value) {
            $type = is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : 'string');
            Setting::set($key, $value, $group, $type);
        }

        Setting::purgeCache();

        return redirect()->route('admin.settings.index', ['tab' => $group])
            ->with('success', ucfirst($group).' settings have been updated successfully.');
    }

    public function runCronTask(Request $request): RedirectResponse
    {
        $request->validate([
            'task' => ['required', 'in:recurring,reminders,all'],
        ]);

        $task = $request->input('task');

        try {
            switch ($task) {
                case 'recurring':
                    Artisan::call('invoices:process-recurring');
                    $output = Artisan::output();
                    $message = 'Recurring Invoices task executed: '.trim($output);
                    break;
                case 'reminders':
                    Artisan::call('invoices:send-reminders');
                    $output = Artisan::output();
                    $message = 'Payment Reminders task executed: '.trim($output);
                    break;
                case 'all':
                default:
                    Artisan::call('schedule:run');
                    $output = Artisan::output();
                    $message = 'Scheduler executed: '.($output ? trim($output) : 'All cron tasks checked. No pending tasks at this moment.');
                    break;
            }

            Setting::set('cron_last_heartbeat_at', now()->toDateTimeString(), 'cron', 'string');

            return redirect()->route('admin.settings.index', ['tab' => 'cron'])
                ->with('success', $message);
        } catch (\Throwable $e) {
            return redirect()->route('admin.settings.index', ['tab' => 'cron'])
                ->with('error', 'Cron execution failed: '.$e->getMessage());
        }
    }

    public function regenerateCronToken(Request $request): RedirectResponse
    {
        $newToken = Str::random(32);
        Setting::set('cron_secret_token', $newToken, 'cron', 'string');
        Setting::purgeCache();

        return redirect()->route('admin.settings.index', ['tab' => 'cron'])
            ->with('success', 'Web Cron Secret Key has been successfully regenerated.');
    }
}
