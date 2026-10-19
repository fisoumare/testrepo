<?php

namespace Tests\Feature;

use App\Livewire\QuoteBuilder;
use App\Mail\InquiryConfirmation;
use App\Mail\InquiryReceived;
use App\Models\Inquiry;
use App\Models\Service;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

/** Configurateur d'estimation (App\Livewire\QuoteBuilder) : sélection, totaux, validation, envoi. */
class QuoteBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /** Prestation à prix fixe utilisée dans la plupart des scénarios (5 000 000 GNF HT, forfait). */
    private const SERVICE_SLUG = 'site-vitrine-jusqua-5-pages';

    private function service(string $slug = self::SERVICE_SLUG): Service
    {
        return Service::query()->active()->where('slug', $slug)->firstOrFail();
    }

    /** Composant à l'étape 2 avec une prestation dans la sélection et des coordonnées valides. */
    private function readyToSubmit(Service $service): Testable
    {
        return Livewire::test(QuoteBuilder::class)
            ->call('add', $service->id)
            ->call('goToDetails')
            ->assertSet('step', QuoteBuilder::STEP_DETAILS)
            ->set('name', 'Prénom Nom')
            ->set('organization', 'Organisation de test')
            ->set('email', 'client@example.com')
            ->set('phone', '+224 600 00 00 00')
            ->set('message', 'Merci de me rappeler dans la journée.');
    }

    public function test_quote_page_renders_the_component(): void
    {
        $this->get('/devis')->assertOk()->assertSeeLivewire(QuoteBuilder::class);
    }

    public function test_catalogue_starts_empty_and_paginated(): void
    {
        $component = Livewire::test(QuoteBuilder::class)
            ->assertSet('step', QuoteBuilder::STEP_COMPOSE)
            ->assertSet('items', [])
            ->assertSet('limit', QuoteBuilder::PER_PAGE)
            ->assertSee('Votre sélection est vide')
            ->assertSee('Afficher les '.QuoteBuilder::PER_PAGE.' prestations suivantes');

        $this->assertCount(QuoteBuilder::PER_PAGE, $component->instance()->visibleServices);
        $this->assertSame(Service::query()->active()->count(), $component->instance()->services->count());

        $component->call('showMore')->assertSet('limit', 2 * QuoteBuilder::PER_PAGE);
        $this->assertCount(2 * QuoteBuilder::PER_PAGE, $component->instance()->visibleServices);
    }

    public function test_search_ignores_accents_and_case(): void
    {
        $service = $this->service();

        $component = Livewire::test(QuoteBuilder::class)->set('search', $service->number);
        $this->assertCount(1, $component->instance()->services);
        $this->assertTrue($component->instance()->services->first()->is($service));

        $accented = Livewire::test(QuoteBuilder::class)->set('search', 'Hébergement')->instance()->services->count();
        $plain = Livewire::test(QuoteBuilder::class)->set('search', 'hebergement')->instance()->services->count();

        $this->assertGreaterThan(0, $accented);
        $this->assertSame($accented, $plain);

        Livewire::test(QuoteBuilder::class)->set('search', 'zzzz-aucun-resultat')->assertSee('Afficher tout le catalogue');
    }

    public function test_category_filter_limits_the_catalogue(): void
    {
        $component = Livewire::test(QuoteBuilder::class)->call('filter', 'formation')->assertSet('category', 'formation');

        $services = $component->instance()->services;
        $this->assertGreaterThan(0, $services->count());
        $this->assertTrue($services->every(fn (Service $service) => $service->category->slug === 'formation'));

        $component->set('search', 'laravel')->assertSet('limit', QuoteBuilder::PER_PAGE)
            ->call('resetFilters')->assertSet('category', null)->assertSet('search', '');
    }

    public function test_adding_a_service_computes_ht_vat_and_ttc(): void
    {
        $service = $this->service();

        $component = Livewire::test(QuoteBuilder::class)
            ->call('add', $service->id)
            ->assertSet('items', [$service->id => 1])
            ->assertSee($service->name)
            ->assertSee('Total HT')
            ->assertSee(gnf($service->price))
            ->assertSee(gnf(Money::vat($service->price)))
            ->assertSee(gnf(Money::ttc($service->price)));

        $cart = $component->instance()->cart;
        $this->assertSame(1, $cart['count']);
        $this->assertSame($service->price, $cart['total_ht']);
        $this->assertSame((int) round($service->price * 0.18), $cart['vat']);
        $this->assertSame($cart['total_ht'] + $cart['vat'], $cart['total_ttc']);
        $this->assertSame(0, $cart['on_quote']);

        // Ajouter de nouveau la même prestation incrémente la quantité.
        $component->call('add', $service->id)->assertSet("items.{$service->id}", 2)->assertSee(gnf(2 * $service->price));
        $this->assertSame(2 * $service->price, $component->instance()->cart['total_ht']);
    }

    public function test_quantities_are_bounded_and_lines_can_be_removed(): void
    {
        $service = $this->service();

        Livewire::test(QuoteBuilder::class)
            ->call('add', $service->id)
            ->call('increment', $service->id)->assertSet("items.{$service->id}", 2)
            ->call('decrement', $service->id)->assertSet("items.{$service->id}", 1)
            ->call('decrement', $service->id)->assertSet('items', [])
            ->call('add', $service->id)
            ->set("items.{$service->id}", 250)->assertSet("items.{$service->id}", QuoteBuilder::MAX_QUANTITY)
            ->set("items.{$service->id}", 0)->assertSet("items.{$service->id}", 1)
            ->call('remove', $service->id)->assertSet('items', [])
            ->call('add', $service->id)
            ->call('clear')->assertSet('items', []);
    }

    public function test_unknown_or_inactive_services_cannot_be_added(): void
    {
        $inactive = $this->service();
        $inactive->update(['is_active' => false]);

        Livewire::test(QuoteBuilder::class)
            ->call('add', 999_999)->assertSet('items', [])
            ->call('add', $inactive->id)->assertSet('items', []);
    }

    /** « items » est public : une requête forgée peut le remplacer en entier (updatedItems reçoit alors une clé nulle). */
    public function test_a_forged_items_payload_is_sanitized_instead_of_crashing(): void
    {
        $service = $this->service();
        $inactive = $this->service('eduges-mise-en-service');
        $inactive->update(['is_active' => false]);

        Livewire::test(QuoteBuilder::class)
            ->set('items', [$service->id => 250, $inactive->id => 1, 999_999 => 1, 'abc' => 1, -4 => 1, 0 => 3])
            ->assertSet('items', [$service->id => QuoteBuilder::MAX_QUANTITY])
            ->set('items', [])
            ->assertSet('items', []);
    }

    public function test_a_selection_cannot_exceed_max_lines(): void
    {
        $ids = Service::query()->active()->orderBy('id')->limit(QuoteBuilder::MAX_LINES + 5)->pluck('id');
        $this->assertCount(QuoteBuilder::MAX_LINES + 5, $ids);

        $component = Livewire::test(QuoteBuilder::class)
            ->set('items', $ids->mapWithKeys(fn (int $id) => [$id => 1])->all());

        $this->assertCount(QuoteBuilder::MAX_LINES, $component->get('items'));
        $this->assertSame($ids->take(QuoteBuilder::MAX_LINES)->all(), array_keys($component->get('items')));

        $component
            ->call('add', $ids->last())
            ->assertHasErrors('items')
            ->assertSee('limitée à '.QuoteBuilder::MAX_LINES.' prestations')
            // Une prestation déjà présente peut toujours être incrémentée.
            ->call('add', $ids->first())
            ->assertHasNoErrors('items')
            ->assertSet('items.'.$ids->first(), 2);

        $this->assertCount(QuoteBuilder::MAX_LINES, $component->get('items'));
    }

    public function test_on_quote_services_are_counted_but_not_totalled(): void
    {
        $onQuote = Service::query()->active()->where('price', 0)->firstOrFail();
        $fixed = $this->service();

        $component = Livewire::test(QuoteBuilder::class)
            ->call('add', $onQuote->id)
            ->call('add', $fixed->id)
            ->assertSee('sur devis');

        $cart = $component->instance()->cart;
        $this->assertSame(2, $cart['count']);
        $this->assertSame(1, $cart['on_quote']);
        $this->assertSame($fixed->price, $cart['total_ht']);
    }

    public function test_service_from_query_string_is_preselected_and_kept_in_session(): void
    {
        $service = $this->service();

        Livewire::withQueryParams(['service' => $service->slug])
            ->test(QuoteBuilder::class)
            ->assertSet('items', [$service->id => 1]);

        // La sélection est conservée en session (#[Session]) : une nouvelle visite la retrouve.
        $this->assertSame([$service->id => 1], session('quote.items'));
        Livewire::test(QuoteBuilder::class)->assertSet('items', [$service->id => 1]);

        session()->forget('quote.items');

        Livewire::withQueryParams(['service' => 'slug-inconnu'])
            ->test(QuoteBuilder::class)
            ->assertSet('items', []);
    }

    public function test_details_step_requires_a_non_empty_selection(): void
    {
        Livewire::test(QuoteBuilder::class)
            ->call('goToDetails')
            ->assertHasErrors('items')
            ->assertSet('step', QuoteBuilder::STEP_COMPOSE)
            ->assertSee('Ajoutez au moins une prestation');
    }

    public function test_contact_details_are_validated_with_french_messages(): void
    {
        $service = $this->service();

        Livewire::test(QuoteBuilder::class)
            ->call('add', $service->id)
            ->call('goToDetails')
            ->assertDispatched('scroll-to', id: 'coordonnees-titre')
            ->set('email', 'pas-une-adresse')
            ->call('submit')
            ->assertHasErrors(['name' => 'required', 'email' => 'email', 'phone' => 'required'])
            ->assertSet('step', QuoteBuilder::STEP_DETAILS)
            ->assertSee('Le champ nom est obligatoire.')
            ->assertSee('Le champ téléphone est obligatoire.')
            ->assertSee('Indiquez une adresse e-mail valide')
            ->assertSee('aria-invalid="true"', false)
            ->assertDispatched('scroll-to', id: 'devis-formulaire', invalid: true);

        $this->assertDatabaseCount('inquiries', 0);
    }

    /** Les champs sont en wire:model.live.blur : chaque mise à jour valide le champ seul et efface son erreur une fois corrigé. */
    public function test_each_contact_field_is_validated_as_soon_as_it_is_updated(): void
    {
        Livewire::test(QuoteBuilder::class)
            ->call('add', $this->service()->id)
            ->call('goToDetails')
            ->set('email', 'pas-une-adresse')
            ->assertHasErrors(['email' => 'email'])
            ->assertHasNoErrors(['name', 'phone'])
            ->set('email', 'client@example.com')
            ->assertHasNoErrors()
            ->assertSet('step', QuoteBuilder::STEP_DETAILS);
    }

    public function test_submission_creates_a_quote_inquiry_and_sends_both_emails(): void
    {
        Mail::fake();

        $first = $this->service();
        $second = $this->service('eduges-mise-en-service');

        $component = $this->readyToSubmit($first)
            ->call('backToCatalog')->assertSet('step', QuoteBuilder::STEP_COMPOSE)
            ->call('add', $first->id)
            ->call('add', $second->id)
            ->call('goToDetails')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('step', QuoteBuilder::STEP_SENT)
            ->assertSet('items', []);

        $inquiry = Inquiry::query()->sole();
        $expectedReference = sprintf('DEM-%s-0001', now()->format('Y'));
        $expectedTotal = 2 * $first->price + $second->price;

        $this->assertSame($expectedReference, $inquiry->reference);
        $this->assertMatchesRegularExpression('/^DEM-\d{4}-\d{4}$/', $inquiry->reference);
        $this->assertSame(Inquiry::TYPE_QUOTE, $inquiry->type);
        $this->assertSame(Inquiry::STATUS_NEW, $inquiry->status);
        $this->assertSame('Prénom Nom', $inquiry->name);
        $this->assertSame('client@example.com', $inquiry->email);
        $this->assertSame('Demande de devis en ligne', $inquiry->subject);
        $this->assertSame($expectedTotal, $inquiry->total_ht);
        $this->assertSame('127.0.0.1', $inquiry->ip);

        $this->assertCount(2, $inquiry->items);
        $this->assertSame($first->id, $inquiry->items[0]['service_id']);
        $this->assertSame(2, $inquiry->items[0]['quantity']);
        $this->assertSame($first->price, $inquiry->items[0]['unit_price']);
        $this->assertSame(2 * $first->price, $inquiry->items[0]['total']);
        $this->assertSame($second->number, $inquiry->items[1]['number']);

        $component
            ->assertSet('reference', $expectedReference)
            ->assertSet('sent.total_ht', $expectedTotal)
            ->assertSet('sent.total_ttc', Money::ttc($expectedTotal))
            ->assertSee($expectedReference)
            ->assertSee(gnf($expectedTotal))
            ->assertSee('Nouvelle estimation')
            ->assertDispatched('scroll-to', id: 'envoye-titre');

        $this->assertStringContainsString($expectedReference, $component->instance()->whatsappUrl());

        Mail::assertSent(InquiryReceived::class, fn (InquiryReceived $mail) => $mail->hasTo(setting('notification_email')) && $mail->inquiry->is($inquiry));
        Mail::assertSent(InquiryConfirmation::class, fn (InquiryConfirmation $mail) => $mail->hasTo('client@example.com') && $mail->inquiry->is($inquiry));
        Mail::assertSentCount(2);
    }

    public function test_references_are_numbered_sequentially_per_type(): void
    {
        Mail::fake();

        $service = $this->service();

        $this->readyToSubmit($service)->call('submit')->assertSet('step', QuoteBuilder::STEP_SENT);
        $this->readyToSubmit($service)->call('submit')->assertSet('step', QuoteBuilder::STEP_SENT);

        $this->assertSame(
            [sprintf('DEM-%s-0001', now()->format('Y')), sprintf('DEM-%s-0002', now()->format('Y'))],
            Inquiry::query()->orderBy('id')->pluck('reference')->all(),
        );
    }

    public function test_honeypot_fakes_success_without_saving_or_mailing(): void
    {
        Mail::fake();

        $this->readyToSubmit($this->service())
            ->set('website', 'https://robot.example')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('step', QuoteBuilder::STEP_SENT)
            ->assertSet('reference', sprintf('DEM-%s-0001', now()->format('Y')));

        $this->assertDatabaseCount('inquiries', 0);
        Mail::assertNothingSent();
    }

    public function test_submissions_are_rate_limited_per_ip(): void
    {
        Mail::fake();

        foreach (range(1, QuoteBuilder::MAX_ATTEMPTS) as $attempt) {
            RateLimiter::hit('inquiry:127.0.0.1', QuoteBuilder::DECAY_SECONDS);
        }

        $this->readyToSubmit($this->service())
            ->call('submit')
            ->assertHasErrors('form')
            ->assertSet('step', QuoteBuilder::STEP_DETAILS)
            ->assertSee('Trop de demandes');

        $this->assertDatabaseCount('inquiries', 0);
        Mail::assertNothingSent();
    }

    public function test_restart_clears_everything_after_sending(): void
    {
        Mail::fake();

        $this->readyToSubmit($this->service())
            ->call('submit')
            ->assertSet('step', QuoteBuilder::STEP_SENT)
            ->call('restart')
            ->assertSet('step', QuoteBuilder::STEP_COMPOSE)
            ->assertSet('items', [])
            ->assertSet('reference', null)
            ->assertSet('sent', [])
            ->assertSet('name', '')
            ->assertSet('email', '');
    }
}
