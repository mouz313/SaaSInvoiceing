<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\Fbr\FbrApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncInvoiceWithFbrJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(public Invoice $invoice) {}

    /**
     * Execute the job.
     */
    public function handle(FbrApiService $fbrService): void
    {
        $this->invoice->refresh();

        if ($this->invoice->fbr_status === 'synced') {
            return;
        }

        $merchant = $this->invoice->user;
        if (! $merchant || ! $merchant->fbr_enabled) {
            $this->invoice->update([
                'fbr_status' => 'not_applicable',
            ]);

            return;
        }

        $this->invoice->update([
            'fbr_status' => 'pending',
            'fbr_error_message' => null,
        ]);

        $result = $fbrService->syncInvoice($this->invoice);

        if ($result['success']) {
            $this->invoice->update([
                'fbr_status' => 'synced',
                'fbr_invoice_number' => $result['fbr_invoice_number'],
                'fbr_qr_code_data' => $result['qr_data'],
                'fbr_synced_at' => now(),
                'fbr_error_message' => null,
            ]);

            Log::info("Invoice #{$this->invoice->invoice_number} synced with FBR. FBR Fiscal ID: {$result['fbr_invoice_number']}");
        } else {
            $this->invoice->update([
                'fbr_status' => 'failed',
                'fbr_error_message' => $result['error'] ?? 'FBR sync failed.',
            ]);

            Log::warning("Invoice #{$this->invoice->invoice_number} FBR sync failed: ".($result['error'] ?? 'Unknown error'));
        }
    }
}
