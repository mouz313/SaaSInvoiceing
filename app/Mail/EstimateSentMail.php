<?php

namespace App\Mail;

use App\Models\Estimate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EstimateSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Estimate $estimate,
        public string $customMessage = '',
        public bool $attachPdf = true
    ) {
        $this->estimate->loadMissing(['client', 'items', 'user', 'logo']);
    }

    public function envelope(): Envelope
    {
        $senderName = $this->estimate->user->company_name ?: $this->estimate->user->name;

        return new Envelope(
            subject: "Quotation / Estimate #{$this->estimate->estimate_number} from {$senderName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.estimate_sent',
        );
    }

    public function attachments(): array
    {
        if (! $this->attachPdf) {
            return [];
        }

        $viewName = match ($this->estimate->style) {
            'corporate' => 'estimates.templates.corporate',
            'creative' => 'estimates.templates.creative',
            'grid' => 'estimates.templates.grid',
            default => 'estimates.templates.minimalist',
        };

        $pdf = Pdf::loadView($viewName, [
            'estimate' => $this->estimate,
            'isPdf' => true,
        ]);

        return [
            Attachment::fromData(fn () => $pdf->output(), "Estimate-{$this->estimate->estimate_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
