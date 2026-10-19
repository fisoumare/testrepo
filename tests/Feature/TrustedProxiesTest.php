<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Proxys de confiance (TRUSTED_PROXIES, « * » par défaut) : derrière un reverse proxy, l'adresse IP
 * du visiteur et le schéma https viennent des en-têtes X-Forwarded-*.
 *
 * Un cas par test : le client de test construit l'URL de chaque requête avec url(), qui reflète le
 * schéma de la requête précédente.
 */
class TrustedProxiesTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_forwarded_client_ip_is_used_behind_a_proxy(): void
    {
        Route::get('/_test/ip', fn () => request()->ip());

        $this->get('/_test/ip', ['X-Forwarded-For' => '41.10.20.30'])
            ->assertOk()
            ->assertSee('41.10.20.30');
    }

    public function test_forwarded_https_scheme_is_used_for_generated_urls(): void
    {
        $this->get('/', ['X-Forwarded-Proto' => 'https'])
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://', false)
            ->assertSee('<meta property="og:url" content="https://', false);
    }

    public function test_plain_http_request_keeps_http_urls(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="http://', false);
    }
}
