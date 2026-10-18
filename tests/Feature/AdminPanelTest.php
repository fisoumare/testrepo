<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Service;
use App\Models\User;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Back-office Filament (/admin) : accès, authentification et pages principales. */
class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /** @return array<string, array{string}> */
    public static function adminPages(): array
    {
        return [
            'tableau de bord' => ['/admin'],
            'prestations' => ['/admin/services'],
            'catégories' => ['/admin/service-categories'],
            'solutions' => ['/admin/solutions'],
            'références' => ['/admin/references'],
            'actualités' => ['/admin/posts'],
            'FAQ' => ['/admin/faqs'],
            'demandes' => ['/admin/inquiries'],
            'utilisateurs' => ['/admin/users'],
            'paramètres' => ['/admin/parametres'],
            'nouvelle prestation' => ['/admin/services/create'],
            'nouvelle actualité' => ['/admin/posts/create'],
        ];
    }

    public function test_login_page_is_public(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('SOUMATIC')
            ->assertSee('type="password"', false);
    }

    #[DataProvider('adminPages')]
    public function test_guests_are_redirected_to_the_login_page(string $url): void
    {
        $this->get($url)->assertRedirect('/admin/login');
    }

    #[DataProvider('adminPages')]
    public function test_authenticated_users_can_open_every_page(string $url): void
    {
        $this->actingAs(User::factory()->create())->get($url)->assertOk();
    }

    public function test_dashboard_and_settings_page_show_their_titles(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertOk()->assertSee('Voir le site');
        $this->actingAs($user)->get('/admin/parametres')->assertOk()->assertSee('Paramètres du site')->assertSee(setting('company_name'));
    }

    public function test_inquiry_can_be_viewed_but_not_created_or_edited(): void
    {
        $inquiry = Inquiry::query()->create([
            'type' => Inquiry::TYPE_QUOTE,
            'name' => 'Prénom Nom',
            'email' => 'client@example.com',
            'phone' => '+224 600 00 00 00',
            'subject' => 'Demande de devis en ligne',
            'items' => [['service_id' => 1, 'number' => 'PREST-2026-0015', 'name' => 'Site vitrine', 'unit' => 'Forfait', 'quantity' => 1, 'unit_price' => 5_000_000, 'total' => 5_000_000]],
            'total_ht' => 5_000_000,
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/inquiries/'.$inquiry->id)
            ->assertOk()
            ->assertSee($inquiry->reference)
            ->assertSee('client@example.com')
            ->assertSee(gnf(5_000_000));

        $this->actingAs($user)->get('/admin/inquiries/create')->assertNotFound();
        $this->actingAs($user)->get('/admin/inquiries/'.$inquiry->id.'/edit')->assertNotFound();
    }

    public function test_service_edit_page_uses_the_numeric_identifier(): void
    {
        $service = Service::query()->active()->firstOrFail();

        $this->actingAs(User::factory()->create())
            ->get('/admin/services/'.$service->id.'/edit')
            ->assertOk()
            ->assertSee($service->number);
    }

    public function test_seeded_administrator_account_exists(): void
    {
        $this->assertDatabaseHas('users', ['email' => env('ADMIN_EMAIL', 'fisoumare@soumatic.com')]);
    }

    public function test_user_can_log_in_from_the_filament_login_page(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);

        Filament::setCurrentPanel('admin');

        Livewire::test(Login::class)
            ->fillForm(['email' => 'admin@example.com', 'password' => 'password'])
            ->call('authenticate')
            ->assertHasNoFormErrors()
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_is_rejected(): void
    {
        User::factory()->create(['email' => 'admin@example.com']);

        Filament::setCurrentPanel('admin');

        Livewire::test(Login::class)
            ->fillForm(['email' => 'admin@example.com', 'password' => 'mauvais-mot-de-passe'])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }

    public function test_logout_returns_to_the_login_page(): void
    {
        $this->actingAs(User::factory()->create())->post('/admin/logout')->assertRedirect('/admin/login');

        $this->assertGuest();
    }
}
