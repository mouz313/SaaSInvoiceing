<?php

use App\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Heartbeat pulse to verify active cron execution
Schedule::call(function () {
    Setting::set('cron_last_heartbeat_at', now()->toDateTimeString(), 'cron', 'string');
})->everyMinute()->name('cron:heartbeat');

// Scheduled & Recurring Invoices Generator & Reminders
try {
    $recurringTime = Setting::get('cron_recurring_time', '06:00');
    $remindersTime = Setting::get('cron_reminders_time', '08:00');
} catch (Throwable) {
    $recurringTime = '06:00';
    $remindersTime = '08:00';
}

Schedule::command('invoices:process-recurring')
    ->dailyAt($recurringTime)
    ->name('invoices:process-recurring');

Schedule::command('invoices:send-reminders')
    ->dailyAt($remindersTime)
    ->name('invoices:send-reminders');
