<?php

namespace App\Console\Commands;

use App\Models\RecurringInvoice;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessRecurringInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:process-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process active recurring invoice profiles and generate scheduled invoices';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $enabled = Setting::get('cron_recurring_enabled', true);
        if (! $enabled) {
            $this->warn('Automated recurring invoice generation is currently disabled in Settings.');
            Setting::set('cron_last_recurring_run_at', now()->toDateTimeString(), 'cron', 'string');
            Setting::set('cron_last_recurring_result', 'Skipped: recurring invoices disabled in Settings at '.now()->format('M d, Y H:i:s'), 'cron', 'string');

            return Command::SUCCESS;
        }

        $today = Carbon::today();
        $this->info("Checking for scheduled recurring invoices on {$today->toDateString()}...");

        $profiles = RecurringInvoice::with(['user', 'client', 'items', 'logo'])
            ->where('status', 'active')
            ->where('next_issue_date', '<=', $today)
            ->get();

        $count = 0;

        foreach ($profiles as $profile) {
            try {
                $invoice = $profile->generateInvoice();
                $count++;
                $this->info("Generated Invoice #{$invoice->invoice_number} for profile '{$profile->title}' ({$profile->client->name}). Next run: {$profile->next_issue_date?->toDateString()}");
            } catch (\Throwable $e) {
                $this->error("Failed to generate recurring invoice for profile #{$profile->id}: {$e->getMessage()}");
                Log::error("Recurring invoice failed profile #{$profile->id}: {$e->getMessage()}");
            }
        }

        $resultMsg = "Processed {$count} recurring invoice profiles on ".now()->format('M d, Y H:i:s');
        $this->info($resultMsg);

        Setting::set('cron_last_recurring_run_at', now()->toDateTimeString(), 'cron', 'string');
        Setting::set('cron_last_recurring_result', $resultMsg, 'cron', 'string');
        Setting::set('cron_last_heartbeat_at', now()->toDateTimeString(), 'cron', 'string');

        return Command::SUCCESS;
    }
}
