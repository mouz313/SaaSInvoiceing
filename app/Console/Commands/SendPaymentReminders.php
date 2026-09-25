<?php

namespace App\Console\Commands;

use App\Mail\PaymentReminderMail;
use App\Models\Invoice;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automated 3-tier payment reminders for upcoming, due, and overdue invoices';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $enabled = Setting::get('cron_reminders_enabled', true);
        if (! $enabled) {
            $this->warn('Automated payment reminders are currently disabled in Settings.');
            Setting::set('cron_last_reminders_run_at', now()->toDateTimeString(), 'cron', 'string');
            Setting::set('cron_last_reminders_result', 'Skipped: reminders disabled in Settings at '.now()->format('M d, Y H:i:s'), 'cron', 'string');

            return Command::SUCCESS;
        }

        $today = Carbon::today();
        $this->info("Scanning invoices for payment reminders on {$today->toDateString()}...");

        // Auto-update all sent invoices that have passed their due date to overdue
        $autoOverdueCount = Invoice::where('status', 'sent')
            ->where('balance_due', '>', 0)
            ->whereNotNull('due_date')
            ->where('due_date', '<', $today)
            ->update(['status' => 'overdue']);

        if ($autoOverdueCount > 0) {
            $this->info("Marked {$autoOverdueCount} past-due invoice(s) as overdue.");
        }

        $upcomingDays = (int) Setting::get('cron_reminder_upcoming_days', 3);
        $overdueInterval = (int) Setting::get('cron_reminder_overdue_interval', 3);

        $invoices = Invoice::with(['client', 'user', 'items', 'logo'])
            ->whereIn('status', ['sent', 'partially_paid', 'overdue'])
            ->where('balance_due', '>', 0)
            ->whereNotNull('due_date')
            ->whereHas('client', function ($q) {
                $q->whereNotNull('email')->where('email', '!=', '');
            })
            ->get();

        $sentCount = 0;

        foreach ($invoices as $invoice) {
            $dueDate = Carbon::parse($invoice->due_date)->startOfDay();
            $diffDays = (int) $today->diffInDays($dueDate, false); // negative means past due

            $reminderType = null;
            $daysOverdue = 0;

            if ($diffDays === $upcomingDays) {
                // Tier 1: Configured days before due date (default: 3)
                $reminderType = 'upcoming';
            } elseif ($diffDays === 0) {
                // Tier 2: On due date
                $reminderType = 'due_today';
            } elseif ($diffDays < 0) {
                // Tier 3: Overdue
                $daysOverdue = abs($diffDays);
                $reminderType = 'overdue';

                // Automatically flag as overdue
                if ($invoice->status === 'sent') {
                    $invoice->update(['status' => 'overdue']);
                }
            }

            if (! $reminderType) {
                continue;
            }

            // Anti-spam check: prevent sending more than once per day, and overdue reminders spaced at least configured days
            if ($invoice->last_reminder_sent_at) {
                $lastSent = Carbon::parse($invoice->last_reminder_sent_at);
                if ($lastSent->isSameDay($today)) {
                    continue;
                }
                if ($reminderType === 'overdue' && $lastSent->diffInDays($today) < $overdueInterval) {
                    continue;
                }
            }

            try {
                Mail::to($invoice->client->email)->send(
                    new PaymentReminderMail($invoice, $reminderType, $daysOverdue, true)
                );

                $invoice->update([
                    'last_reminder_sent_at' => now(),
                    'reminder_count' => ($invoice->reminder_count ?? 0) + 1,
                ]);

                $sentCount++;
                $this->line("<info>Sent [{$reminderType}]</info> reminder for Invoice #{$invoice->invoice_number} to {$invoice->client->email}");
            } catch (\Throwable $e) {
                $this->error("Failed sending reminder for Invoice #{$invoice->invoice_number}: {$e->getMessage()}");
                Log::error("Reminder failed for #{$invoice->invoice_number}: {$e->getMessage()}");
            }
        }

        $resultMsg = "Completed. {$sentCount} payment reminders dispatched on ".now()->format('M d, Y H:i:s');
        $this->info($resultMsg);

        Setting::set('cron_last_reminders_run_at', now()->toDateTimeString(), 'cron', 'string');
        Setting::set('cron_last_reminders_result', $resultMsg, 'cron', 'string');
        Setting::set('cron_last_heartbeat_at', now()->toDateTimeString(), 'cron', 'string');

        return Command::SUCCESS;
    }
}
