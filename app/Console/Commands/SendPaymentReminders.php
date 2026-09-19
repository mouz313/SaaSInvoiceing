<?php

namespace App\Console\Commands;

use App\Mail\PaymentReminderMail;
use App\Models\Invoice;
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
        $today = Carbon::today();
        $this->info("Scanning invoices for payment reminders on {$today->toDateString()}...");

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

            if ($diffDays === 3) {
                // Tier 1: 3 days before due date
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

            // Anti-spam check: prevent sending more than once per day, and overdue reminders spaced at least 3 days
            if ($invoice->last_reminder_sent_at) {
                $lastSent = Carbon::parse($invoice->last_reminder_sent_at);
                if ($lastSent->isSameDay($today)) {
                    continue;
                }
                if ($reminderType === 'overdue' && $lastSent->diffInDays($today) < 3) {
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

        $this->info("Completed. {$sentCount} payment reminders dispatched.");

        return Command::SUCCESS;
    }
}
