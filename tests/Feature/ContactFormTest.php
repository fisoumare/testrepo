<?php

namespace Tests\Feature;

use App\Livewire\ContactForm;
use App\Mail\InquiryConfirmation;
use App\Mail\InquiryReceived;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

/** Formulaire de contact (App\Livewire\ContactForm) : préremplissage, validation, envoi. */
class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private const MESSAGE = 'Bonjour, je souhaite un rendez-vous pour présenter notre projet de site.';

    private function filled(): Testable
    {
        return Livewire::test(ContactForm::class)
            ->set('name', 'Prénom Nom')
            ->set('organization', 'Organisation de test')
            ->set('email', 'client@example.com')
            ->set('phone', '+224 600 00 00 00')
            ->set('subject', ContactForm::SUBJECTS['devis'])
            ->set('message', self::MESSAGE);
    }

    public function test_contact_page_renders_the_component(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSeeLivewire(ContactForm::class)
            ->assertSee('Envoyer le message');
    }

    public function test_form_offers_every_subject(): void
    {
        $component = Livewire::test(ContactForm::class)->assertSet('sent', false)->assertSet('subject', '');

        foreach (ContactForm::SUBJECTS as $label) {
            $component->assertSee($label);
        }
    }

    public function test_subject_and_solution_are_prefilled_from_the_query_string(): void
    {
        Livewire::withQueryParams(['sujet' => 'assistance'])
            ->test(ContactForm::class)
            ->assertSet('subject', ContactForm::SUBJECTS['assistance']);

        Livewire::withQueryParams(['sujet' => 'demo', 'solution' => 'eduges'])
            ->test(ContactForm::class)
            ->assertSet('subject', ContactForm::SUBJECTS['demo'])
            ->assertSet('message', 'Je souhaite une démonstration de EduGes.');

        Livewire::withQueryParams(['sujet' => 'inconnu', 'solution' => 'inconnue'])
            ->test(ContactForm::class)
            ->assertSet('subject', '')
            ->assertSet('message', '');
    }

    public function test_required_fields_are_validated_with_french_messages(): void
    {
        Livewire::test(ContactForm::class)
            ->call('submit')
            ->assertHasErrors(['name' => 'required', 'email' => 'required', 'subject' => 'required', 'message' => 'required'])
            ->assertHasNoErrors(['phone', 'organization'])
            ->assertSet('sent', false)
            ->assertSee('Le champ nom est obligatoire.')
            ->assertSee('Le champ adresse e-mail est obligatoire.')
            ->assertSee('Choisissez l\'objet de votre message.')
            ->assertSee('Le champ message est obligatoire.');

        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_email_subject_and_message_length_are_checked(): void
    {
        $this->filled()
            ->set('email', 'pas-une-adresse')
            ->set('subject', 'Objet hors liste')
            ->set('message', 'Trop court.')
            ->call('submit')
            ->assertHasErrors(['email' => 'email', 'subject' => 'in', 'message' => 'min'])
            ->assertSee('Indiquez une adresse e-mail valide')
            ->assertSee('Choisissez l\'objet de votre message dans la liste.')
            ->assertSee('Décrivez votre besoin en au moins 20 caractères');

        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_submission_creates_a_contact_inquiry_and_sends_both_emails(): void
    {
        Mail::fake();

        $component = $this->filled()
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('sent', true);

        $inquiry = Inquiry::query()->sole();
        $expectedReference = sprintf('MSG-%s-0001', now()->format('Y'));

        $this->assertSame($expectedReference, $inquiry->reference);
        $this->assertMatchesRegularExpression('/^MSG-\d{4}-\d{4}$/', $inquiry->reference);
        $this->assertSame(Inquiry::TYPE_CONTACT, $inquiry->type);
        $this->assertSame(Inquiry::STATUS_NEW, $inquiry->status);
        $this->assertSame('Prénom Nom', $inquiry->name);
        $this->assertSame('Organisation de test', $inquiry->organization);
        $this->assertSame('client@example.com', $inquiry->email);
        $this->assertSame('+224 600 00 00 00', $inquiry->phone);
        $this->assertSame(ContactForm::SUBJECTS['devis'], $inquiry->subject);
        $this->assertSame(self::MESSAGE, $inquiry->message);
        $this->assertNull($inquiry->items);
        $this->assertNull($inquiry->total_ht);

        $component
            ->assertSet('reference', $expectedReference)
            ->assertSee($expectedReference)
            ->assertSee('Merci Prénom Nom')
            ->assertSee('Envoyer un autre message');

        $this->assertStringContainsString($expectedReference, $component->instance()->whatsappUrl());

        Mail::assertSent(InquiryReceived::class, fn (InquiryReceived $mail) => $mail->hasTo(setting('notification_email')) && $mail->inquiry->is($inquiry));
        Mail::assertSent(InquiryConfirmation::class, fn (InquiryConfirmation $mail) => $mail->hasTo('client@example.com') && $mail->inquiry->is($inquiry));
        Mail::assertSentCount(2);
    }

    public function test_emails_carry_the_reference_in_their_subject(): void
    {
        Mail::fake();

        $this->filled()->call('submit')->assertSet('sent', true);

        $inquiry = Inquiry::query()->sole();

        Mail::assertSent(InquiryReceived::class, fn (InquiryReceived $mail) => str_contains($mail->envelope()->subject, '['.$inquiry->reference.'] Nouveau message'));
        Mail::assertSent(InquiryConfirmation::class, fn (InquiryConfirmation $mail) => str_contains($mail->envelope()->subject, 'votre message '.$inquiry->reference));
    }

    public function test_optional_fields_are_stored_as_null_when_empty(): void
    {
        Mail::fake();

        $this->filled()->set('organization', '')->set('phone', '')->call('submit')->assertSet('sent', true);

        $inquiry = Inquiry::query()->sole();
        $this->assertNull($inquiry->organization);
        $this->assertNull($inquiry->phone);
    }

    public function test_honeypot_fakes_success_without_saving_or_mailing(): void
    {
        Mail::fake();

        $this->filled()
            ->set('website', 'https://robot.example')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('sent', true)
            ->assertSet('reference', sprintf('MSG-%s-0001', now()->format('Y')));

        $this->assertDatabaseCount('inquiries', 0);
        Mail::assertNothingSent();
    }

    public function test_submissions_are_rate_limited_per_ip(): void
    {
        Mail::fake();

        foreach (range(1, ContactForm::MAX_ATTEMPTS) as $attempt) {
            RateLimiter::hit('inquiry:127.0.0.1', ContactForm::DECAY_SECONDS);
        }

        $this->filled()
            ->call('submit')
            ->assertHasErrors('form')
            ->assertSet('sent', false)
            ->assertSee('Trop de demandes');

        $this->assertDatabaseCount('inquiries', 0);
        Mail::assertNothingSent();
    }

    public function test_restart_resets_the_form(): void
    {
        Mail::fake();

        $this->filled()
            ->call('submit')
            ->assertSet('sent', true)
            ->call('restart')
            ->assertSet('sent', false)
            ->assertSet('reference', null)
            ->assertSet('name', '')
            ->assertSet('subject', '')
            ->assertSet('message', '')
            ->assertSee('Envoyer le message');
    }
}
