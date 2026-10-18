<?php

namespace App\Livewire;

use App\Livewire\Concerns\SubmitsInquiries;
use App\Models\Inquiry;
use App\Models\Solution;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Formulaire de contact : enregistre une Inquiry de type « contact » et notifie par e-mail. */
class ContactForm extends Component
{
    use SubmitsInquiries;

    /** Objets proposés, indexés par la clé acceptée dans l'URL (?sujet=demo). */
    public const SUBJECTS = [
        'devis' => 'Demande de devis',
        'demo' => "Démonstration d'une solution",
        'assistance' => 'Assistance',
        'partenariat' => 'Partenariat',
        'autre' => 'Autre',
    ];

    public string $name = '';

    public string $organization = '';

    public string $email = '';

    public string $phone = '';

    public string $subject = '';

    public string $message = '';

    #[Locked]
    public bool $sent = false;

    #[Locked]
    public ?string $reference = null;

    public function mount(): void
    {
        $subject = request()->query('sujet');

        if (is_string($subject) && isset(self::SUBJECTS[$subject])) {
            $this->subject = self::SUBJECTS[$subject];
        }

        $slug = request()->query('solution');

        if (is_string($slug) && $slug !== '') {
            $solution = Solution::query()->where('is_active', true)->where('slug', $slug)->first();

            if ($solution) {
                $this->subject = self::SUBJECTS['demo'];
                $this->message = 'Je souhaite une démonstration de '.$solution->name.'.';
            }
        }
    }

    /** @return array<string, array<int, mixed>> */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'organization' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', Rule::in(self::SUBJECTS)],
            'message' => ['required', 'string', 'min:20', 'max:3000'],
        ];
    }

    /** Validation champ par champ dès la perte de focus (wire:model.blur). */
    public function updated(string $property): void
    {
        if (array_key_exists($property, $this->rules())) {
            $this->validateOnly($property);
        }
    }

    public function submit(): void
    {
        $this->validate();

        if ($throttled = $this->throttleMessage()) {
            $this->addError('form', $throttled);

            return;
        }

        $this->rememberAttempt();

        // Pot-de-miel rempli : on affiche la confirmation sans rien enregistrer ni envoyer.
        if ($this->isTrapped()) {
            $this->reference = Inquiry::nextReference(Inquiry::TYPE_CONTACT);
            $this->sent = true;

            return;
        }

        $inquiry = Inquiry::query()->create([
            'type' => Inquiry::TYPE_CONTACT,
            'name' => trim($this->name),
            'organization' => trim($this->organization) ?: null,
            'email' => trim($this->email),
            'phone' => trim($this->phone) ?: null,
            'subject' => $this->subject,
            'message' => trim($this->message),
            'ip' => request()->ip(),
        ]);

        $this->notify($inquiry);

        $this->reference = $inquiry->reference;
        $this->sent = true;
    }

    public function restart(): void
    {
        $this->reset('name', 'organization', 'email', 'phone', 'subject', 'message', 'website', 'sent', 'reference');
        $this->resetErrorBag();
    }

    public function whatsappUrl(): string
    {
        return whatsapp_url(sprintf(
            "Bonjour SOUMATIC, je viens d'envoyer le message %s depuis votre site.",
            $this->reference,
        ));
    }

    public function render(): View
    {
        return view('livewire.contact-form', [
            'subjects' => array_combine(self::SUBJECTS, self::SUBJECTS),
        ]);
    }
}
