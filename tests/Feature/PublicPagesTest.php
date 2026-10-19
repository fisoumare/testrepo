<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\Solution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Pages publiques du site, rendues à partir des données des seeders. */
class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private const POST_SLUG = 'soumatic-accompagne-la-dgsst-sur-la-plateforme-nationale-de-sante-au-travail';

    private const SERVICE_SLUG = 'site-vitrine-jusqua-5-pages';

    /**
     * Chaque page publique avec quelques extraits de contenu attendus.
     *
     * @return array<string, array{string, list<string>}>
     */
    public static function publicPages(): array
    {
        return [
            'accueil' => ['/', ['SOUMATIC', 'Des logiciels qui tiennent la charge.']],
            'solutions' => ['/solutions', ['EduGes', 'MediGes SST', 'MediGes', 'Compiniya']],
            'solution EduGes' => ['/solutions/eduges', ['EduGes', 'Demander une démonstration']],
            'prestations' => ['/prestations', ['Conception', 'Hébergement & Noms de domaine', 'Formation']],
            'catégorie Conception' => ['/prestations/conception', ['Conception']],
            'fiche prestation' => ['/prestation/'.self::SERVICE_SLUG, ["Site vitrine – jusqu'à 5 pages", 'PREST-2026-0015']],
            'références' => ['/references', ['DGSST', 'Plateforme nationale de santé au travail']],
            'actualités' => ['/actualites', ['Journal de bord', 'Compiniya']],
            'article' => ['/actualites/'.self::POST_SLUG, ['SOUMATIC accompagne la DGSST sur la plateforme nationale de santé au travail']],
            'à propos' => ['/a-propos', ['SOUMATIC', 'Conakry']],
            'contact' => ['/contact', ['Parlons de votre projet.', 'Formulaire de contact']],
            'devis' => ['/devis', ['Estimation en ligne', 'Catalogue des prestations']],
            'mentions légales' => ['/mentions-legales', ['Mentions légales', 'GN.TCC.2026.A.06358']],
        ];
    }

    /** @param  list<string>  $expected */
    #[DataProvider('publicPages')]
    public function test_public_pages_respond_with_their_content(string $url, array $expected): void
    {
        $response = $this->get($url)->assertOk();

        foreach ($expected as $text) {
            $response->assertSee($text);
        }
    }

    #[DataProvider('publicPages')]
    public function test_public_pages_have_exactly_one_h1(string $url): void
    {
        $html = $this->get($url)->assertOk()->getContent();

        $this->assertSame(1, preg_match_all('/<h1\b/i', $html), "La page {$url} doit contenir un seul <h1>.");
    }

    #[DataProvider('publicPages')]
    public function test_public_pages_never_misspell_the_product_names(string $url): void
    {
        $this->get($url)->assertOk()->assertDontSee('EduGest');
    }

    #[DataProvider('publicPages')]
    public function test_public_pages_embed_valid_json_ld_and_no_raw_php(string $url): void
    {
        $html = $this->get($url)->assertOk()->getContent();

        $this->assertStringNotContainsString('<?php', $html, "La page {$url} contient du PHP non compilé.");

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
        $this->assertNotEmpty($matches[1], "La page {$url} ne déclare aucune donnée structurée.");

        foreach ($matches[1] as $json) {
            $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
            $this->assertSame('https://schema.org', $data['@context'] ?? null);
            $this->assertArrayHasKey('@type', $data);
        }
    }

    public function test_home_page_uses_the_site_settings(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(setting('hero_title'))
            ->assertSee(setting('phone'))
            ->assertSee('<title>SOUMATIC — Numérique de confiance</title>', false)
            ->assertSee('<html lang="fr"', false);
    }

    public function test_solutions_index_lists_the_four_active_solutions(): void
    {
        $response = $this->get('/solutions')->assertOk();

        Solution::query()->where('is_active', true)->get()->each(function (Solution $solution) use ($response) {
            $response->assertSee($solution->name)->assertSee(route('solutions.show', $solution));
        });
    }

    public function test_services_index_lists_every_active_category(): void
    {
        $response = $this->get('/prestations')->assertOk();

        ServiceCategory::query()->where('is_active', true)->get()->each(
            fn (ServiceCategory $category) => $response->assertSee($category->name)->assertSee($category->code),
        );
    }

    public function test_service_page_shows_the_formatted_price(): void
    {
        $service = Service::query()->where('slug', self::SERVICE_SLUG)->firstOrFail();

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSee($service->name)
            ->assertSee($service->number)
            ->assertSee(gnf($service->price, false))
            ->assertSee('GNF HT')
            ->assertSee($service->category->name);
    }

    public function test_service_page_shows_on_quote_when_the_price_is_zero(): void
    {
        $service = Service::query()->active()->where('price', 0)->firstOrFail();

        $this->get(route('services.show', $service))->assertOk()->assertSee('Sur devis');
    }

    public function test_service_page_terms_follow_the_pricing_case(): void
    {
        $onQuote = Service::query()->active()->where('price', 0)->firstOrFail();
        $monthly = Service::query()->active()->where('unit', 'Mois')->where('price', '>', 0)->firstOrFail();
        $fixed = Service::query()->active()->where('unit', 'Forfait')->where('price', '>', 0)->firstOrFail();

        // « Prix fixes » figure dans le menu de l'en-tête : on vise la phrase de la fiche technique.
        $this->get(route('services.show', $onQuote))->assertOk()
            ->assertSee('fixés sur devis après cadrage')
            ->assertDontSee('Prix fixe.')
            ->assertDontSee('Prix fixe,');

        $this->get(route('services.show', $monthly))->assertOk()
            ->assertSee("payable d'avance")
            ->assertDontSee('solde à la validation');

        $this->get(route('services.show', $fixed))->assertOk()
            ->assertSee('Prix fixe.')
            ->assertSee('solde à la validation');
    }

    public function test_service_page_offers_a_single_set_of_actions(): void
    {
        $html = $this->get('/prestation/'.self::SERVICE_SLUG)->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'Ajouter à mon estimation'));
        $this->assertSame(1, substr_count($html, 'Demander par WhatsApp'));
    }

    public function test_solution_without_subscription_or_services_still_renders(): void
    {
        $solution = Solution::query()->where('slug', 'eduges')->firstOrFail();
        $solution->services()->where('name', 'like', '% – Abonnement%')->update(['is_active' => false]);

        $this->get(route('solutions.show', $solution))->assertOk()->assertSee('EduGes')->assertDontSee('À partir de');

        $empty = Solution::query()->create(['name' => 'Solution de test', 'code' => 'S99', 'tagline' => 'Sans prestation', 'summary' => 'Une solution créée depuis le back-office, sans prestation rattachée.', 'is_active' => true]);

        $this->get(route('solutions.show', $empty))->assertOk()->assertSee('Solution de test')->assertSee('Sur devis');
    }

    public function test_services_index_previews_six_cards_per_family_and_keeps_the_rest_in_the_page(): void
    {
        $category = ServiceCategory::query()->where('slug', 'conception')->firstOrFail();
        $services = $category->activeServices()->get();
        $this->assertGreaterThan(6, $services->count());

        $response = $this->get('/prestations')->assertOk()
            ->assertSee('Afficher les '.($services->count() - 6).' autres prestations')
            ->assertSee(setting('payment_terms'))
            ->assertSee('à prix affichés');

        $services->each(fn (Service $service) => $response->assertSee($service->name));
    }

    public function test_category_page_has_a_breadcrumb_above_the_title_and_a_heading_before_the_grid(): void
    {
        $html = $this->get('/prestations/hebergement-noms-de-domaine')->assertOk()->getContent();

        $this->assertLessThan(strpos($html, '<h1'), strpos($html, 'aria-label="Fil d\'Ariane"'), 'Le fil d\'Ariane précède le titre.');
        $this->assertMatchesRegularExpression('#<h2[^>]*>Les \d+ prestations U03</h2>#', $html, 'Un h2 introduit la grille.');
        $this->assertLessThan(strpos($html, '<h3'), strpos($html, 'prestations U03</h2>'), 'Le h2 précède les h3 des cartes.');
        $this->assertStringContainsString('dont 1 sur devis', $html);
        $this->assertStringNotContainsString('Ajoutez des prestations', $html, 'L\'encart doublon a disparu : la bande d\'appel suit la grille.');
    }

    /**
     * Pages du catalogue : title ≤ 60 caractères (suffixe « · SOUMATIC » compris) et meta description de 120 à 160 caractères.
     *
     * @return array<string, array{string}>
     */
    public static function cataloguePages(): array
    {
        $pages = ['/solutions', '/prestations'];

        foreach (['eduges', 'mediges-sst', 'mediges', 'compiniya'] as $slug) {
            $pages[] = '/solutions/'.$slug;
        }

        foreach (['conception', 'solutions-cles-en-main', 'hebergement-noms-de-domaine', 'api-integrations', 'maintenance-support', 'analyse-cahier-des-charges', 'conseil-audit', 'formation'] as $slug) {
            $pages[] = '/prestations/'.$slug;
        }

        return array_combine($pages, array_map(fn (string $page) => [$page], $pages));
    }

    #[DataProvider('cataloguePages')]
    public function test_catalogue_pages_have_sized_titles_and_meta_descriptions(string $url): void
    {
        $this->assertTitleAndDescriptionAreSized($this->get($url)->assertOk()->getContent(), $url);
    }

    public function test_every_service_page_has_sized_title_and_meta_description(): void
    {
        Service::query()->active()->get()->each(function (Service $service) {
            $url = route('services.show', $service);

            $this->assertTitleAndDescriptionAreSized($this->get($url)->assertOk()->getContent(), $url);
        });
    }

    private function assertTitleAndDescriptionAreSized(string $html, string $url): void
    {
        $this->assertSame(1, preg_match('#<title>(.*?)</title>#s', $html, $title), "La page {$url} n'a pas de <title>.");
        $this->assertSame(1, preg_match('#<meta name="description" content="(.*?)">#s', $html, $description), "La page {$url} n'a pas de meta description.");

        $title = html_entity_decode($title[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $description = html_entity_decode($description[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $this->assertLessThanOrEqual(60, mb_strlen($title), "Title de {$url} trop long : « {$title} »");
        $this->assertGreaterThanOrEqual(120, mb_strlen($description), "Meta description de {$url} trop courte : « {$description} »");
        $this->assertLessThanOrEqual(160, mb_strlen($description), "Meta description de {$url} trop longue : « {$description} »");
        $this->assertStringNotContainsString('…', mb_substr($description, 0, -1), "Meta description de {$url} tronquée en plein milieu : « {$description} »");
    }

    public function test_references_page_shows_the_published_reference_only(): void
    {
        $this->get('/references')
            ->assertOk()
            ->assertSee('DGSST')
            ->assertDontSee('PREVENTICA')
            ->assertDontSee('FONIJ');
    }

    public function test_posts_index_lists_published_posts(): void
    {
        $response = $this->get('/actualites')->assertOk();

        Post::query()->published()->get()->each(
            fn (Post $post) => $response->assertSee($post->title)->assertSee(route('posts.show', $post)),
        );
    }

    public function test_sitemap_is_valid_xml_listing_the_public_urls(): void
    {
        $response = $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml');

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml, 'Le plan du site doit être un document XML valide.');
        $this->assertSame('urlset', $xml->getName());

        $locations = [];
        foreach ($xml->url as $url) {
            $locations[] = (string) $url->loc;
        }

        $this->assertCount(count(array_unique($locations)), $locations, 'Le plan du site contient des doublons.');

        foreach ([
            route('home'),
            route('solutions.index'),
            route('services.index'),
            route('quote'),
            route('contact'),
            route('solutions.show', 'eduges'),
            route('services.category', 'conception'),
            route('services.show', self::SERVICE_SLUG),
            route('posts.show', self::POST_SLUG),
        ] as $expected) {
            $this->assertContains($expected, $locations);
        }

        $this->assertNotContains(route('legal'), $locations);
    }

    public function test_sitemap_omits_inactive_services_and_unpublished_posts(): void
    {
        $service = Service::query()->active()->firstOrFail();
        $service->update(['is_active' => false]);

        $draft = Post::query()->create(['title' => 'Brouillon de test', 'body' => '<p>Brouillon.</p>', 'is_published' => false]);

        $content = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringNotContainsString(route('services.show', $service), $content);
        $this->assertStringNotContainsString(route('posts.show', $draft), $content);
    }

    public function test_robots_file_allows_the_site_but_not_the_admin(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('User-agent: *')
            ->assertSee('Disallow: /admin')
            ->assertSee('Disallow: /livewire')
            ->assertSee('Sitemap: '.route('sitemap'));
    }

    public function test_inactive_service_returns_404(): void
    {
        $service = Service::query()->active()->firstOrFail();
        $service->update(['is_active' => false]);

        $this->get(route('services.show', $service))->assertNotFound();
    }

    public function test_inactive_category_and_solution_return_404(): void
    {
        $category = ServiceCategory::query()->where('slug', 'formation')->firstOrFail();
        $category->update(['is_active' => false]);

        $solution = Solution::query()->where('slug', 'compiniya')->firstOrFail();
        $solution->update(['is_active' => false]);

        $this->get(route('services.category', $category))->assertNotFound();
        $this->get(route('solutions.show', $solution))->assertNotFound();
    }

    public function test_unpublished_or_scheduled_post_returns_404(): void
    {
        $draft = Post::query()->create(['title' => 'Brouillon', 'body' => '<p>Brouillon.</p>', 'is_published' => false]);
        $scheduled = Post::query()->create(['title' => 'Programmé', 'body' => '<p>Bientôt.</p>', 'is_published' => true, 'published_at' => now()->addDay()]);

        $this->get(route('posts.show', $draft))->assertNotFound();
        $this->get(route('posts.show', $scheduled))->assertNotFound();
        $this->get('/actualites')->assertOk()->assertDontSee('Brouillon')->assertDontSee('Programmé');
    }

    public function test_unknown_slugs_return_404(): void
    {
        $this->get('/solutions/inconnue')->assertNotFound();
        $this->get('/prestations/inconnue')->assertNotFound();
        $this->get('/prestation/inconnue')->assertNotFound();
        $this->get('/actualites/inconnue')->assertNotFound();
    }

    public function test_quote_page_preselects_a_service_from_the_query_string(): void
    {
        $service = Service::query()->where('slug', self::SERVICE_SLUG)->firstOrFail();

        $this->get(route('quote', ['service' => $service->slug]))
            ->assertOk()
            ->assertSee($service->name)
            ->assertSee(gnf($service->price));
    }

    public function test_contact_page_prefills_a_demo_request(): void
    {
        $this->get(route('contact', ['sujet' => 'demo', 'solution' => 'eduges']))
            ->assertOk()
            ->assertSee("Je souhaite une démonstration d'EduGes.");
    }

    public function test_json_ld_cannot_be_closed_by_a_script_tag_in_the_content(): void
    {
        $payload = '</script><script>alert(1)</script>';

        Setting::set('company_name', 'SOUMATIC'.$payload);
        $post = Post::query()->create(['title' => 'Titre'.$payload, 'body' => '<p>Corps.</p>', 'is_published' => true, 'published_at' => now()->subDay()]);

        $html = $this->get(route('posts.show', $post))->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html, 'Un contenu ne doit jamais fermer le bloc JSON-LD.');
        $this->assertSame(3, preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches), 'Organisation, article et fil d\'Ariane.');

        foreach ($matches[1] as $json) {
            $this->assertStringContainsString('\u003C/script\u003E', $json);
            json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        }
    }
}
