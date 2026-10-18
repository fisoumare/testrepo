<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Accusé de réception envoyé au client avec la référence de sa demande. */
class InquiryConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Inquiry $inquiry) {}

    public function envelope(): Envelope
    {
        $replyTo = setting('email');

        return new Envelope(
            subject: sprintf(
                '%s — %s %s',
                setting('company_name', 'SOUMATIC'),
                $this->inquiry->isQuote() ? 'votre demande de devis' : 'votre message',
                $this->inquiry->reference,
            ),
            replyTo: $replyTo ? [new Address($replyTo, setting('company_name', 'SOUMATIC'))] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.inquiry-confirmation',
            with: ['totalTtc' => $this->inquiry->totalTtc()],
        );
    }
}
