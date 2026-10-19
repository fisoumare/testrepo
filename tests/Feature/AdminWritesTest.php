<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteSettings;
use App\Filament\Resources\Inquiries\Pages\ViewInquiry;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Filament\Resources\Solutions\Pages\ListSolutions;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\Inquiry;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Solution;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Écritures du back-office Filament : formulaires des ressources, page des paramètres, actions
 * et effets immédiats sur le site public (caches invalidés).
 */
class AdminWritesTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private const SERVICE_SLUG = 'site-vitrine-jusqua-5-pages';

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel('admin');
    }

    public function test_a_service_can_be_created_and_appears_on_the_site(): void
    {
        $category = ServiceCategory::query()->where('slug', 'conception')->firstOrFail();

        Livewire::test(CreateService::class)
            ->assertOk()
            ->fillForm([
                'service_category_id' => $category->id,
                'number' => 'PREST-2026-0999',
                'name' => 'Prestation de test',
                'slug' => 'prestation-de-test',
                'unit' => 'Forfait',
                'price' => 1_250_000,
                'summary' => 'Résumé de test.',
                'description' => 'Description de test.',
                'is_featured' => false,
                'is_active' => true,
                'position' => 99,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        $this->assertDatabaseHas('services', [
            'slug' => 'prestation-de-test',
            'number' => 'PREST-2026-0999',
            'price' => 1_250_000,
            'service_category_id' => $category->id,
        ]);

        $this->get('/prestation/prestation-de-test')
            ->assertOk()
            ->assertSee('Prestation de test')
            ->assertSee('PREST-2026-0999')
            ->assertSee(gnf(1_250_000, false));
    }

    public function test_service_form_rejects_a_duplicate_slug_and_a_negative_price(): void
    {
        $existing = Service::query()->active()->firstOrFail();

        Livewire::test(CreateService::class)
            ->fillForm([
                'service_category_id' => $existing->service_category_id,
                'name' => 'Doublon',
                'slug' => $existing->slug,
                'unit' => 'Forfait',
                'price' => -1,
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique', 'price' => 'min'])
            ->assertNotNotified()
            ->assertNoRedirect();

        $this->assertDatabaseMissing('services', ['name' => 'Doublon']);
    }

    public function test_a_service_can_be_edited_and_the_public_page_follows(): void
    {
        $service = Service::query()->where('slug', self::SERVICE_SLUG)->firstOrFail();

        Livewire::test(EditService::class, ['record' => $service->id])
            ->assertOk()
            ->assertSchemaStateSet(['name' => $service->name, 'slug' => $service->slug])
            ->fillForm(['price' => 6_000_000, 'summary' => 'Nouveau résumé de test.'])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas('services', ['id' => $service->id, 'price' => 6_000_000, 'summary' => 'Nouveau résumé de test.']);

        $this->get(route('services.show', $service->fresh()))
            ->assertOk()
            ->assertSee(gnf(6_000_000, false))
            ->assertSee('Nouveau résumé de test.');
    }

    public function test_bulk_disable_hides_services_from_the_site(): void
    {
        $services = Service::query()->active()->where('slug', self::SERVICE_SLUG)->get();

        Livewire::test(ListServices::class)
            ->assertOk()
            ->assertCanSeeTableRecords($services)
            ->selectTableRecords($services->pluck('id')->all())
            ->callAction(TestAction::make('disable')->table()->bulk());

        $this->assertDatabaseHas('services', ['slug' => self::SERVICE_SLUG, 'is_active' => false]);
        $this->get('/prestation/'.self::SERVICE_SLUG)->assertNotFound();
    }

    public function test_bulk_disable_of_a_solution_clears_the_navigation_cache(): void
    {
        $solution = Solution::query()->where('slug', 'compiniya')->firstOrFail();

        $this->get('/')->assertOk();
        $this->assertTrue(Cache::has('nav.solutions'), 'Le menu met les solutions en cache.');

        Livewire::test(ListSolutions::class)
            ->selectTableRecords([$solution->id])
            ->callAction(TestAction::make('disable')->table()->bulk());

        $this->assertFalse(Cache::has('nav.solutions'), 'Le cache du menu doit être invalidé.');
        $this->get(route('solutions.show', $solution))->assertNotFound();
        $this->get('/')->assertOk()->assertDontSee(route('solutions.show', $solution));
    }

    public function test_site_settings_are_saved_and_visible_on_the_site_at_once(): void
    {
        Livewire::test(SiteSettings::class)
            ->assertOk()
            ->assertSchemaStateSet(['company_name' => setting('company_name'), 'phone' => setting('phone')])
            ->fillForm(['tagline' => 'Signature de test', 'hero_title' => 'Titre de test.'])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas('settings', ['key' => 'tagline', 'value' => 'Signature de test']);

        // Mémoïsation et cache invalidés par l'enregistrement : la valeur est lue sans redémarrage.
        $this->assertSame('Signature de test', setting('tagline'));

        $this->get('/')
            ->assertOk()
            ->assertSee('Titre de test.')
            ->assertSee('<title>SOUMATIC — Signature de test</title>', false);
    }

    public function test_site_settings_form_validates_emails_and_links(): void
    {
        Livewire::test(SiteSettings::class)
            ->fillForm(['notification_email' => 'pas-un-e-mail', 'facebook_url' => 'pas-un-lien', 'company_name' => ''])
            ->call('save')
            ->assertHasFormErrors(['notification_email' => 'email', 'facebook_url' => 'url', 'company_name' => 'required'])
            ->assertNotNotified();

        $this->assertDatabaseMissing('settings', ['key' => 'notification_email', 'value' => 'pas-un-e-mail']);
    }

    public function test_inquiry_status_and_notes_are_changed_from_the_view_page(): void
    {
        $inquiry = $this->createInquiry();

        Livewire::test(ViewInquiry::class, ['record' => $inquiry->id])
            ->assertOk()
            ->assertActionExists('changeStatus')
            ->callAction('changeStatus', data: [
                'status' => Inquiry::STATUS_ANSWERED,
                'internal_notes' => 'Devis envoyé par e-mail.',
            ])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas('inquiries', [
            'id' => $inquiry->id,
            'status' => Inquiry::STATUS_ANSWERED,
            'internal_notes' => 'Devis envoyé par e-mail.',
        ]);
    }

    public function test_inquiry_status_must_be_one_of_the_known_statuses(): void
    {
        $inquiry = $this->createInquiry();

        Livewire::test(ViewInquiry::class, ['record' => $inquiry->id])
            ->callAction('changeStatus', data: ['status' => 'inconnu'])
            ->assertHasFormErrors(['status']);

        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id, 'status' => Inquiry::STATUS_NEW]);
    }

    public function test_a_user_is_created_with_a_hashed_password_and_can_log_in(): void
    {
        Livewire::test(CreateUser::class)
            ->assertOk()
            ->fillForm(['name' => 'Nouvel utilisateur', 'email' => 'nouveau@example.com', 'password' => 'mot-de-passe-solide'])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $user = User::query()->where('email', 'nouveau@example.com')->firstOrFail();

        $this->assertNotSame('mot-de-passe-solide', $user->password, 'Le mot de passe ne doit jamais être stocké en clair.');
        $this->assertTrue(Hash::check('mot-de-passe-solide', $user->password), 'Le mot de passe ne doit être haché qu\'une seule fois.');
    }

    public function test_user_form_requires_a_unique_email_and_an_eight_character_password(): void
    {
        $existing = User::query()->firstOrFail();

        Livewire::test(CreateUser::class)
            ->fillForm(['name' => 'Doublon', 'email' => $existing->email, 'password' => 'court'])
            ->call('create')
            ->assertHasFormErrors(['email' => 'unique', 'password' => 'min'])
            ->assertNotNotified();

        $this->assertSame(1, User::query()->where('email', $existing->email)->count());
    }

    private function createInquiry(): Inquiry
    {
        return Inquiry::query()->create([
            'type' => Inquiry::TYPE_QUOTE,
            'name' => 'Prénom Nom',
            'email' => 'client@example.com',
            'phone' => '+224 600 00 00 00',
            'subject' => 'Demande de devis en ligne',
            'items' => [['service_id' => 1, 'number' => 'PREST-2026-0015', 'name' => 'Site vitrine', 'unit' => 'Forfait', 'quantity' => 1, 'unit_price' => 5_000_000, 'total' => 5_000_000]],
            'total_ht' => 5_000_000,
        ]);
    }
}
