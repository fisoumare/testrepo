<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteSettings;
use App\Filament\Resources\Inquiries\Pages\ViewInquiry;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\References\Pages\CreateReference;
use App\Filament\Resources\References\ReferenceResource;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Schemas\ServiceForm;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\Solutions\SolutionResource;
use App\Filament\Support\CommonFields;
use App\Livewire\ContactForm;
use App\Livewire\QuoteBuilder;
use App\Models\Inquiry;
use App\Models\Post;
use App\Models\Reference;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Solution;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Saisies trop longues ou hors bornes : elles doivent être refusées par la validation (message
 * en français) et ne jamais atteindre la base. SQLite stocke une chaîne de n'importe quelle longueur,
 * mais MySQL en mode strict refuse une valeur plus longue que sa colonne (VARCHAR 255, 32, 8…),
 * une date hors de la plage d'un TIMESTAMP ou un entier hors limites : sans validation, l'envoi
 * finirait en erreur 500. Ces tests passent sur les deux moteurs.
 */
class MySqlLimitsTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function admin(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel('admin');
    }

    // ------------------------------------------------------------ Formulaires publics (Livewire)

    public function test_contact_form_refuses_overlong_fields_without_touching_the_database(): void
    {
        Livewire::test(ContactForm::class)
            ->set('name', str_repeat('a', 300))
            ->set('organization', str_repeat('b', 300))
            ->set('email', str_repeat('c', 290).'@example.com')
            ->set('phone', str_repeat('6', 300))
            ->set('subject', str_repeat('Objet inventé ', 30))
            ->set('message', str_repeat('Message très long. ', 200))
            ->call('submit')
            ->assertHasErrors([
                'name' => 'max',
                'organization' => 'max',
                'email' => 'max',
                'phone' => 'max',
                'subject' => 'in',
                'message' => 'max',
            ])
            ->assertSee('Le champ nom ne doit pas dépasser 120 caractères.')
            ->assertSet('sent', false);

        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_quote_builder_refuses_overlong_contact_details_without_touching_the_database(): void
    {
        $service = Service::query()->active()->where('price', '>', 0)->firstOrFail();

        Livewire::test(QuoteBuilder::class)
            ->call('add', $service->id)
            ->call('goToDetails')
            ->assertSet('step', QuoteBuilder::STEP_DETAILS)
            ->set('name', str_repeat('é', 300))
            ->set('organization', str_repeat('b', 300))
            ->set('email', str_repeat('c', 290).'@example.com')
            ->set('phone', str_repeat('6', 300))
            ->set('message', str_repeat('x', 2001))
            ->call('submit')
            ->assertHasErrors([
                'name' => 'max',
                'organization' => 'max',
                'email' => 'max',
                'phone' => 'max',
                'message' => 'max',
            ])
            ->assertSee('Le champ nom ne doit pas dépasser 120 caractères.')
            ->assertSet('step', QuoteBuilder::STEP_DETAILS);

        $this->assertDatabaseCount('inquiries', 0);
    }

    /** Les limites des formulaires publics restent dans la taille des colonnes `inquiries` (VARCHAR 255). */
    public function test_public_form_limits_fit_the_inquiry_columns(): void
    {
        foreach ([ContactForm::class, QuoteBuilder::class] as $component) {
            $rules = (fn () => $this->rules())->call(new $component);

            foreach (['name', 'organization', 'email', 'phone'] as $field) {
                $max = collect($rules[$field])->first(fn ($rule) => is_string($rule) && str_starts_with($rule, 'max:'));

                $this->assertNotNull($max, "{$component} : {$field} sans limite de longueur");
                $this->assertLessThanOrEqual(CommonFields::STRING_MAX, (int) substr($max, 4), "{$component} : {$field}");
            }
        }

        foreach (ContactForm::SUBJECTS as $subject) {
            $this->assertLessThanOrEqual(CommonFields::STRING_MAX, mb_strlen($subject));
        }
    }

    // ------------------------------------------------------------ Back-office (Filament)

    public function test_service_form_refuses_overlong_texts_and_out_of_range_numbers(): void
    {
        $this->admin();
        $category = ServiceCategory::query()->firstOrFail();

        Livewire::test(CreateService::class)
            ->fillForm([
                'service_category_id' => $category->id,
                'number' => 'PREST-'.str_repeat('9', 40),
                'name' => str_repeat('Prestation ', 30),
                'slug' => str_repeat('prestation-', 30).'test',
                'unit' => 'Unité inventée',
                'price' => ServiceForm::PRICE_MAX + 1,
                'summary' => str_repeat('r', 171),
                'description' => str_repeat('d', 5001),
                'position' => CommonFields::POSITION_MAX + 1,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'number' => 'max',
                'name' => 'max',
                'slug' => 'max',
                'unit' => 'in',
                'price' => 'max',
                'summary' => 'max',
                'description' => 'max',
                'position' => 'max',
            ])
            ->assertNotNotified()
            ->assertNoRedirect();

        $this->assertDatabaseMissing('services', ['service_category_id' => $category->id, 'price' => ServiceForm::PRICE_MAX + 1]);
    }

    public function test_reference_form_refuses_an_overlong_acronym_and_texts(): void
    {
        $this->admin();
        $count = Reference::query()->count();

        Livewire::test(CreateReference::class)
            ->fillForm([
                'name' => 'Organisation de test',
                'slug' => 'organisation-de-test',
                'acronym' => str_repeat('A', 33),
                'sector' => str_repeat('s', 256),
                'location' => str_repeat('l', 256),
                'headline' => str_repeat('h', 256),
                'description' => str_repeat('d', 5001),
                'year' => 2026,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'acronym' => 'max',
                'sector' => 'max',
                'location' => 'max',
                'headline' => 'max',
                'description' => 'max',
            ])
            ->assertNotNotified();

        $this->assertSame($count, Reference::query()->count());
    }

    /** published_at est un TIMESTAMP : MySQL refuse une date après janvier 2038 (« Incorrect datetime value »). */
    public function test_post_form_refuses_a_publication_date_outside_the_timestamp_range(): void
    {
        $this->admin();

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Article programmé trop loin',
                'slug' => 'article-programme-trop-loin',
                'published_at' => '2040-01-01 09:00',
            ])
            ->call('create')
            ->assertHasFormErrors(['published_at' => 'before_or_equal'])
            ->assertNotNotified();

        $this->assertDatabaseMissing('posts', ['slug' => 'article-programme-trop-loin']);
    }

    /** Un slug n'admet que des minuscules ASCII : « Site-Vitrine » ou « café » doublonneraient sous MySQL. */
    public function test_slug_must_be_lowercase_ascii(): void
    {
        $this->admin();
        $category = ServiceCategory::query()->firstOrFail();

        foreach (['Site-Vitrine', 'cafe-créole', 'avec_souligne', 'tiret-final-'] as $slug) {
            Livewire::test(CreateService::class)
                ->fillForm([
                    'service_category_id' => $category->id,
                    'name' => 'Prestation de test',
                    'slug' => $slug,
                    'unit' => 'Forfait',
                    'price' => 1000,
                ])
                ->call('create')
                ->assertHasFormErrors(['slug' => 'regex']);
        }

        $this->assertDatabaseMissing('services', ['name' => 'Prestation de test']);
    }

    public function test_slug_generated_from_a_long_title_is_capped_at_255_characters(): void
    {
        $this->admin();

        // 250 caractères, mais « œ » donne « oe » et « @ » donne « at » : le slug brut dépasse 255.
        $title = mb_substr(str_repeat('Cœur @ Œuvre ', 20), 0, 250);

        $component = Livewire::test(CreatePost::class)->fillForm(['title' => $title]);
        $slug = $component->get('data.slug');

        $this->assertIsString($slug);
        $this->assertLessThanOrEqual(255, strlen($slug));
        $this->assertMatchesRegularExpression(CommonFields::SLUG_REGEX, $slug);

        $component->call('create')->assertHasNoFormErrors();
        $this->assertDatabaseHas('posts', ['slug' => $slug]);
    }

    public function test_models_cap_the_slug_they_generate(): void
    {
        $post = Post::query()->create(['title' => str_repeat('œ', 200)]);
        $this->assertSame(255, strlen($post->slug));

        $post = Post::query()->create(['title' => mb_substr(str_repeat('Cœur @ Œuvre ', 20), 0, 250)]);
        $this->assertLessThanOrEqual(255, strlen($post->slug));
        $this->assertMatchesRegularExpression(CommonFields::SLUG_REGEX, $post->slug);
        $this->assertStringEndsNotWith('-', $post->slug);

        $this->assertSame('site-vitrine', Post::slugFrom('Site vitrine'));
    }

    public function test_site_settings_refuse_overlong_values(): void
    {
        $this->admin();

        Livewire::test(SiteSettings::class)
            ->fillForm([
                'company_name' => str_repeat('S', 256),
                'phone' => str_repeat('6', 33),
                'footer_note' => str_repeat('n', SiteSettings::TEXTAREA_MAX + 1),
                'legal_hosting' => str_repeat('h', SiteSettings::TEXTAREA_MAX + 1),
                'quote_validity_days' => 100000,
            ])
            ->call('save')
            ->assertHasFormErrors([
                'company_name' => 'max',
                'phone' => 'max',
                'footer_note' => 'max',
                'legal_hosting' => 'max',
                'quote_validity_days' => 'max',
            ])
            ->assertNotNotified();

        $this->assertDatabaseMissing('settings', ['key' => 'company_name', 'value' => str_repeat('S', 256)]);
    }

    /**
     * Les ressources dont le modèle expose son slug publiquement se résolvent par identifiant dans
     * l'admin (`$recordRouteKeyName = 'id'`) : les liens doivent donc porter l'identifiant. Avec le slug,
     * la page répondait 404 et, sous MySQL, `WHERE id = '1-bilan'` est converti en `id = 1` et ouvrait
     * un autre enregistrement.
     */
    public function test_admin_edit_links_carry_the_record_id(): void
    {
        $this->admin();

        $first = Post::query()->orderBy('id')->firstOrFail();
        $post = Post::query()->create(['title' => 'Bilan', 'slug' => $first->id.'-bilan']);

        $resources = [
            PostResource::class => $post,
            ReferenceResource::class => Reference::query()->firstOrFail(),
            ServiceResource::class => Service::query()->firstOrFail(),
            SolutionResource::class => Solution::query()->firstOrFail(),
        ];

        foreach ($resources as $resource => $record) {
            $url = $resource::getUrl('edit', ['record' => $record]);

            $this->assertStringEndsWith('/'.$record->getKey().'/edit', $url);
            $this->get($url)->assertOk()->assertSee($record->getAttribute($resource::getRecordTitleAttribute()));
        }

        // Les liens de la liste des prestations portent eux aussi l'identifiant, jamais le slug.
        $list = $this->get(ServiceResource::getUrl('index'))->assertOk()->getContent();
        preg_match_all('#/admin/services/([^/"]+)/edit#', $list, $matches);

        $this->assertNotEmpty($matches[1]);
        $this->assertContainsOnlyNumeric($matches[1]);
    }

    public function test_inquiry_notes_are_limited(): void
    {
        $this->admin();

        $inquiry = Inquiry::createWithReference([
            'type' => Inquiry::TYPE_CONTACT,
            'name' => 'Prénom Nom',
            'email' => 'client@example.com',
            'subject' => ContactForm::SUBJECTS['autre'],
            'message' => 'Message de test suffisamment long.',
        ]);

        Livewire::test(ViewInquiry::class, ['record' => $inquiry->id])
            ->callAction('changeStatus', data: [
                'status' => Inquiry::STATUS_ANSWERED,
                'internal_notes' => str_repeat('n', 5001),
            ])
            ->assertHasFormErrors(['internal_notes' => 'max']);

        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id, 'status' => Inquiry::STATUS_NEW, 'internal_notes' => null]);
    }
}
