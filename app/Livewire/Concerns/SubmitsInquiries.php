<?php

namespace App\Livewire\Concerns;

use App\Mail\InquiryConfirmation;
use App\Mail\InquiryReceived;
use App\Models\Inquiry;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Comportements communs aux formulaires publics (estimation et contact) :
 * pot-de-miel, limitation du nombre d'envois par adresse IP et notifications e-mail.
 */
trait SubmitsInquiries
{
    /** Nombre d'envois autorisés par adresse IP pendant la fenêtre de limitation. */
    public const MAX_ATTEMPTS = 5;

    /** Durée de la fenêtre de limitation, en secondes (10 minutes). */
    public const DECAY_SECONDS = 600;

    /** Pot-de-miel : caché aux humains, rempli par les robots. Doit rester vide. */
    public string $website = '';

    protected function throttleKey(): string
    {
        return 'inquiry:'.request()->ip();
    }

    /** Vrai si le pot-de-miel a été rempli (envoi automatisé probable). */
    protected function isTrapped(): bool
    {
        return trim($this->website) !== '';
    }

    /** Message d'erreur si la limite d'envois est atteinte, null sinon. */
    protected function throttleMessage(): ?string
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return null;
        }

        $minutes = max(1, (int) ceil(RateLimiter::availableIn($this->throttleKey()) / 60));

        return sprintf(
            "Trop de demandes ont été envoyées depuis votre connexion. Réessayez dans %d minute%s ou écrivez-nous directement par WhatsApp\u{00A0}: %s.",
            $minutes,
            $minutes > 1 ? 's' : '',
            setting('whatsapp', setting('phone', ''))
        );
    }

    protected function rememberAttempt(): void
    {
        RateLimiter::hit($this->throttleKey(), self::DECAY_SECONDS);
    }

    /**
     * Envoi synchrone des deux e-mails (SOUMATIC puis le client).
     * Une panne du courrier est signalée mais ne bloque jamais l'enregistrement.
     */
    protected function notify(Inquiry $inquiry): void
    {
        try {
            if ($to = setting('notification_email', setting('email'))) {
                Mail::to($to)->send(new InquiryReceived($inquiry));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            Mail::to($inquiry->email, $inquiry->name)->send(new InquiryConfirmation($inquiry));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
