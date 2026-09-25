<?php

namespace App\Mail;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string $customMessage = '',
        public bool $attachPdf = true,
        public ?string $tempPassword = null
    ) {
        $this->invoice->loadMissing(['client', 'items', 'user', 'logo']);
        if (empty($this->tempPassword)) {
            if (! empty($this->invoice->client?->temp_password)) {
                $this->tempPassword = $this->invoice->client->temp_password;
            } elseif ($this->invoice->client && $this->invoice->client->must_change_password) {
                $this->tempPassword = $this->invoice->client->generateTemporaryPassword();
            }
        }
    }

    public function envelope(): Envelope
    {
        $senderName = $this->invoice->user->company_name ?: $this->invoice->user->name;

        return new Envelope(
            subject: "Invoice #{$this->invoice->invoice_number} from {$senderName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice_sent',
            with: [
                'tempPassword' => $this->tempPassword,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->attachPdf) {
            return [];
        }

        $viewName = view()->exists("invoices.templates.{$this->invoice->style}")
            ? "invoices.templates.{$this->invoice->style}"
            : 'invoices.templates.minimalist';

        $pdf = Pdf::loadView($viewName, [
            'invoice' => $this->invoice,
            'isPdf' => true,
        ]);

        return [
            Attachment::fromData(fn () => $pdf->output(), "Invoice-{$this->invoice->invoice_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
