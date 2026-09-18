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
        public bool $attachPdf = true
    ) {
        $this->invoice->loadMissing(['client', 'items', 'user', 'logo']);
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

        $viewName = match ($this->invoice->style) {
            'corporate' => 'invoices.templates.corporate',
            'creative' => 'invoices.templates.creative',
            'grid' => 'invoices.templates.grid',
            default => 'invoices.templates.minimalist',
        };

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
