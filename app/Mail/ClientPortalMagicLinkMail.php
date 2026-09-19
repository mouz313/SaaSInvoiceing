<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientPortalMagicLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Client $client
    ) {
        $this->client->loadMissing('user');
    }

    public function envelope(): Envelope
    {
        $senderName = $this->client->user->company_name ?: $this->client->user->name;

        return new Envelope(
            subject: "Your Client Portal Access Link - {$senderName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client_portal_magic_link',
        );
    }
}
