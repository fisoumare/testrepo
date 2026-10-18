<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Route;

/** Notification interne : une nouvelle demande (devis ou message) vient d'arriver. */
class InquiryReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Inquiry $inquiry) {}

    public function envelope(): Envelope
    {
        $label = $this->inquiry->isQuote() ? 'Nouvelle demande de devis' : 'Nouveau message';

        return new Envelope(
            subject: sprintf('[%s] %s — %s', $this->inquiry->reference, $label, $this->inquiry->name),
            replyTo: [new Address($this->inquiry->email, $this->inquiry->name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.inquiry-received',
            with: [
                'adminUrl' => $this->adminUrl(),
                'totalTtc' => $this->inquiry->totalTtc(),
                'receivedAt' => $this->receivedAt(),
            ],
        );
    }

    /** Date et heure de réception à Conakry, en toutes lettres : « 17 octobre 2026 à 9 h 05 ». */
    protected function receivedAt(): string
    {
        $at = ($this->inquiry->created_at ?? now())->copy()->timezone('Africa/Conakry');

        return $at->translatedFormat('j F Y').' à '.$at->format('G').' h '.$at->format('i');
    }

    /** Lien vers la fiche dans le back-office Filament, ou vers la liste si la ressource n'est pas (encore) déclarée. */
    protected function adminUrl(): string
    {
        foreach (['filament.admin.resources.inquiries.view', 'filament.admin.resources.inquiries.edit'] as $name) {
            if (Route::has($name)) {
                return route($name, ['record' => $this->inquiry]);
            }
        }

        return url('/admin/inquiries/'.$this->inquiry->getKey());
    }
}
