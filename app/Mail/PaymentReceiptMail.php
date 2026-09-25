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

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string $paymentMethod = 'Stripe / Online'
    ) {
        $this->invoice->loadMissing(['client', 'items', 'user', 'logo']);
    }

    public function envelope(): Envelope
    {
        $senderName = $this->invoice->user->company_name ?: $this->invoice->user->name;

        return new Envelope(
            subject: "Payment Receipt: Invoice #{$this->invoice->invoice_number} Paid [{$senderName}]",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment_receipt',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $viewName = view()->exists("invoices.templates.{$this->invoice->style}")
            ? "invoices.templates.{$this->invoice->style}"
            : 'invoices.templates.minimalist';

        $pdf = Pdf::loadView($viewName, [
            'invoice' => $this->invoice,
            'isPdf' => true,
        ]);

        return [
            Attachment::fromData(fn () => $pdf->output(), "Receipt-Invoice-{$this->invoice->invoice_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
