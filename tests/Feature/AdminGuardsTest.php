<?php

namespace Tests\Feature;

use App\Filament\Resources\Inquiries\Pages\ListInquiries;
use App\Filament\Resources\Inquiries\Pages\ViewInquiry;
use App\Filament\Resources\ServiceCategories\Pages\ManageServiceCategories;
use App\Filament\Resources\Solutions\Pages\EditSolution;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Widgets\SiteStatsOverview;
use App\Models\Inquiry;
use App\Models\ServiceCategory;
use App\Models\Solution;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Garde-fous du back-office : suppressions dangereuses, validation, libellés et liens filtrés. */
class AdminGuardsTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
        Filament::setCurrentPanel('admin');
    }

    private function createInquiry(array $attributes = []): Inquiry
    {
        return Inquiry::query()->create($attributes + [
            'type' => Inquiry::TYPE_CONTACT,
            'name' => 'Prénom Nom',
            'email' => 'client@example.com',
            'message' => 'Bonjour',
        ]);
    }

    public function test_a_category_with_services_cannot_be_deleted(): void
    {
        $category = ServiceCategory::query()->has('services')->firstOrFail();

        // Le bouton est désactivé (avec explication) : ni l'interface ni l'API de test ne peuvent le déclencher.
        Livewire::test(ManageServiceCategories::class)
            ->assertActionDisabled(TestAction::make(DeleteAction::class)->table($category))
            ->assertActionExists(
                TestAction::make(DeleteAction::class)->table($category),
                fn (DeleteAction $action): bool => str_contains((string) $action->getTooltip(), 'réaffectez-les'),
            );

        $this->assertModelExists($category);
        $this->assertGreaterThan(0, $category->services()->count());
    }

    public function test_an_empty_category_can_be_deleted(): void
    {
        $category = ServiceCategory::query()->create(['name' => 'Catégorie vide', 'code' => 'U99']);

        Livewire::test(ManageServiceCategories::class)
            ->assertActionEnabled(TestAction::make(DeleteAction::class)->table($category))
            ->callAction(TestAction::make(DeleteAction::class)->table($category))
            ->assertNotified();

        $this->assertModelMissing($category);
    }

    public function test_bulk_deletion_of_categories_is_refused_when_one_still_has_services(): void
    {
        $used = ServiceCategory::query()->has('services')->firstOrFail();
        $empty = ServiceCategory::query()->create(['name' => 'Catégorie vide', 'code' => 'U99']);

        Livewire::test(ManageServiceCategories::class)
            ->selectTableRecords([$used->id, $empty->id])
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified('Suppression impossible');

        $this->assertModelExists($used);
        $this->assertModelExists($empty);
    }

    public function test_a_user_cannot_delete_their_own_account(): void
    {
        Livewire::test(EditUser::class, ['record' => $this->admin->id])
            ->assertActionHidden(DeleteAction::class);

        $this->assertModelExists($this->admin);
    }

    public function test_the_last_remaining_account_cannot_be_deleted(): void
    {
        $other = User::factory()->create();
        User::query()->whereKeyNot($other->id)->delete(); // ne reste que « other » (l'admin connecté a disparu)

        Livewire::test(EditUser::class, ['record' => $other->id])
            ->assertActionHidden(DeleteAction::class);

        $this->assertModelExists($other);
    }

    public function test_another_account_can_be_deleted(): void
    {
        $other = User::factory()->create();

        Livewire::test(EditUser::class, ['record' => $other->id])
            ->assertActionVisible(DeleteAction::class)
            ->callAction(DeleteAction::class)
            ->assertNotified()
            ->assertRedirect();

        $this->assertModelMissing($other);
        $this->assertModelExists($this->admin);
    }

    public function test_bulk_deletion_of_users_never_removes_the_connected_account(): void
    {
        $others = User::factory()->count(2)->create();

        // Le compte connecté n'est pas sélectionnable : la sélection ne retient que les autres.
        Livewire::test(ListUsers::class)
            ->selectTableRecords([$this->admin->id, ...$others->modelKeys()])
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified();

        $this->assertModelExists($this->admin);
        $others->each(fn (User $user) => $this->assertModelMissing($user));
    }

    public function test_bulk_deletion_of_users_is_refused_when_it_would_empty_the_table(): void
    {
        // L'admin connecté a disparu : seules les lignes restantes peuvent être sélectionnées.
        $others = User::factory()->count(2)->create();
        User::query()->whereKeyNot($others->modelKeys())->delete();

        Livewire::test(ListUsers::class)
            ->selectTableRecords($others->modelKeys())
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified('Suppression impossible');

        $others->each(fn (User $user) => $this->assertModelExists($user));
    }

    public function test_password_reset_and_profile_pages_are_available(): void
    {
        auth()->logout();

        $this->get('/admin/login')->assertOk()->assertSee('Mot de passe oublié');
        $this->get('/admin/password-reset/request')->assertOk()->assertSee('type="email"', false);
        $this->get('/admin/profile')->assertRedirect('/admin/login');

        $this->actingAs($this->admin)->get('/admin/profile')->assertOk()->assertSee('Nouveau mot de passe');
    }

    public function test_solution_accent_must_be_a_six_digit_hex_color(): void
    {
        $solution = Solution::query()->firstOrFail();

        Livewire::test(EditSolution::class, ['record' => $solution->id])
            ->fillForm(['accent' => '#0E9F6E;color:red'])
            ->call('save')
            ->assertHasFormErrors(['accent' => 'regex']);

        Livewire::test(EditSolution::class, ['record' => $solution->id])
            ->fillForm(['accent' => '#0E9F6E'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('#0E9F6E', $solution->fresh()->accent);
    }

    public function test_inquiry_message_keeps_its_line_breaks_and_escapes_html(): void
    {
        $inquiry = $this->createInquiry([
            'message' => "Première ligne\nDeuxième <b>ligne</b>",
            'internal_notes' => "Note 1\nNote 2",
        ]);

        Livewire::test(ViewInquiry::class, ['record' => $inquiry->id])
            ->assertSeeHtml('Première ligne<br />')
            ->assertSeeHtml('Deuxième &lt;b&gt;ligne&lt;/b&gt;')
            ->assertSeeHtml('Note 1<br />');
    }

    public function test_quote_lines_carry_french_labels_for_the_stacked_mobile_layout(): void
    {
        $inquiry = $this->createInquiry([
            'type' => Inquiry::TYPE_QUOTE,
            'items' => [['service_id' => 1, 'number' => 'PREST-2026-0001', 'name' => 'Prestation test', 'unit' => 'Forfait', 'quantity' => 2, 'unit_price' => 1_000_000, 'total' => 2_000_000]],
            'total_ht' => 2_000_000,
        ]);

        Livewire::test(ViewInquiry::class, ['record' => $inquiry->id])
            ->assertSee('Prix unitaire HT')
            ->assertSee('Qté')
            ->assertDontSee('Unit price')
            ->assertDontSee('Quantity');
    }

    public function test_dashboard_tile_links_to_the_list_filtered_on_new_inquiries(): void
    {
        $new = $this->createInquiry();
        $closed = $this->createInquiry(['status' => Inquiry::STATUS_CLOSED]);

        $html = html_entity_decode(Livewire::test(SiteStatsOverview::class)->html());

        preg_match('~href="([^"]*/admin/inquiries\?[^"]+)"~', $html, $matches);
        $this->assertNotEmpty($matches, 'La tuile « Demandes nouvelles » doit pointer vers la liste filtrée.');

        parse_str((string) parse_url($matches[1], PHP_URL_QUERY), $query);

        $this->assertSame(['status' => ['values' => [Inquiry::STATUS_NEW]]], $query['filters']);

        Livewire::withQueryParams($query)
            ->test(ListInquiries::class)
            ->assertCanSeeTableRecords([$new])
            ->assertCanNotSeeTableRecords([$closed]);
    }

    public function test_empty_states_and_page_titles_are_in_correct_french(): void
    {
        Livewire::test(ListInquiries::class)->assertSee('Aucune demande')->assertDontSee('Aucun(e)');

        $this->get('/admin/services/create')->assertOk()->assertSee('Nouvelle prestation')->assertDontSee('Créer Prestation');
        $this->get('/admin/users/create')->assertOk()->assertSee('Nouvel utilisateur');
        $this->get('/admin/services')->assertOk()->assertSee('Nouvelle prestation');
    }
}
