<?php

namespace App\Mail;

use App\Models\Client;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientStatementMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $statementData
     */
    public function __construct(
        public Client $client,
        public array $statementData,
        public string $customMessage = '',
        public bool $attachPdf = true
    ) {
        $this->client->loadMissing('user');
    }

    public function envelope(): Envelope
    {
        $senderName = $this->client->user->company_name ?: $this->client->user->name;

        return new Envelope(
            subject: "Account Statement from {$senderName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client_statement',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->attachPdf) {
            return [];
        }

        $pdf = Pdf::loadView('clients.statement_pdf', [
            'statement' => $this->statementData,
            'isPdf' => true,
        ]);

        $startDateStr = $this->statementData['startDate'] ? $this->statementData['startDate']->format('Y-m-d') : 'All';
        $endDateStr = $this->statementData['endDate'] ? $this->statementData['endDate']->format('Y-m-d') : 'Time';
        $fileName = "Statement_{$this->client->id}_{$startDateStr}_{$endDateStr}.pdf";

        return [
            Attachment::fromData(fn () => $pdf->output(), $fileName)
                ->withMime('application/pdf'),
        ];
    }
}
