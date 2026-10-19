<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

/** Pages d'erreur : 404 sur le layout du site, 500 et 503 autonomes (sans base de données). */
class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_unknown_url_renders_the_site_404_page(): void
    {
        $this->get('/page-inexistante')
            ->assertNotFound()
            ->assertSee('<html lang="fr"', false)
            ->assertSee('<meta name="robots" content="noindex">', false)
            ->assertSee('ERR-404')
            ->assertSee("Cette unité n'existe pas.")
            ->assertSee('led-amber')
            ->assertSee(route('home'))
            ->assertSee(route('services.index'))
            ->assertSee(route('contact'))
            // En-tête et pied de page du site : coordonnées lues dans les paramètres.
            ->assertSee(setting('phone'));
    }

    public function test_unknown_records_render_the_same_404_page(): void
    {
        foreach (['/prestation/inconnue', '/solutions/inconnue', '/actualites/inconnue'] as $url) {
            $this->get($url)->assertNotFound()->assertSee('ERR-404');
        }
    }

    public function test_404_is_json_for_api_clients(): void
    {
        $this->getJson('/page-inexistante')->assertNotFound()->assertJsonStructure(['message']);
    }

    public function test_server_error_and_maintenance_pages_render_without_the_database(): void
    {
        Route::get('/_test/erreur-500', fn () => abort(500));
        Route::get('/_test/erreur-503', fn () => abort(503));

        // Toute requête SQL pendant le rendu est une régression : la base peut être la cause de l'incident.
        DB::beforeExecuting(function (string $query): void {
            throw new RuntimeException("Requête SQL pendant le rendu d'une page d'erreur serveur : {$query}");
        });

        $this->get('/_test/erreur-500')
            ->assertStatus(500)
            ->assertSee('<html lang="fr"', false)
            ->assertSee('ERR-500')
            ->assertSee('Cette unité ne répond pas.')
            ->assertSee(url('/'));

        $this->get('/_test/erreur-503')
            ->assertStatus(503)
            ->assertSee('<html lang="fr"', false)
            ->assertSee('ERR-503')
            ->assertSee('Mise à jour en cours.');
    }
}
