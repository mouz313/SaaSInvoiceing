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

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string $reminderType = 'upcoming', // upcoming, due_today, overdue
        public int $daysOverdue = 0,
        public bool $attachPdf = true
    ) {
        $this->invoice->loadMissing(['client', 'items', 'user', 'logo']);
    }

    public function envelope(): Envelope
    {
        $senderName = $this->invoice->user->company_name ?: $this->invoice->user->name;

        $subject = match ($this->reminderType) {
            'due_today' => "Reminder: Invoice #{$this->invoice->invoice_number} is due today",
            'overdue' => "Payment Overdue: Invoice #{$this->invoice->invoice_number} is past due ({$this->daysOverdue} days)",
            default => "Upcoming Payment Reminder: Invoice #{$this->invoice->invoice_number} due soon",
        };

        return new Envelope(
            subject: "{$subject} - {$senderName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment_reminder',
        );
    }

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
