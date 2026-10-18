<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Service;
use App\Models\Solution;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SolutionController extends Controller
{
    /**
     * Familles de formules d'une solution, dans l'ordre d'affichage. La famille est déduite
     * du nom de la prestation après le tiret (« EduGes – Abonnement… »).
     *
     * @var array<string, array{prefix: string, label: string, note: string}>
     */
    private const OFFER_TYPES = [
        'subscription' => ['prefix' => 'abonnement', 'label' => 'Abonnement', 'note' => 'Hébergé par SOUMATIC, payable au mois ou à l\'année'],
        'licence' => ['prefix' => 'licence installée', 'label' => 'Licence installée', 'note' => 'Achetée une seule fois, installée sur votre serveur'],
        'setup' => ['prefix' => 'mise en service', 'label' => 'Mise en service', 'note' => 'Facturée une seule fois, formation comprise'],
        'maintenance' => ['prefix' => 'maintenance licence', 'label' => 'Maintenance de la licence', 'note' => 'Mises à jour et assistance, par an'],
        'migration' => ['prefix' => 'reprise des données', 'label' => 'Reprise des données', 'note' => 'Import de votre historique'],
        'other' => ['prefix' => '', 'label' => 'Options', 'note' => ''],
    ];

    public function index(): View
    {
        $solutions = Solution::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->with(['services' => fn ($q) => $q->where('is_active', true)])
            ->get();

        return view('pages.solutions.index', [
            'solutions' => $solutions,
            'offers' => $solutions->mapWithKeys(fn (Solution $solution) => [$solution->id => self::groupOffers($solution->services)]),
            'faqs' => Faq::query()->active()->whereIn('group', ['Solutions', 'Prix et paiement'])->get(),
        ]);
    }

    public function show(Solution $solution): View
    {
        abort_unless($solution->is_active, 404);

        $solution->load(['services' => fn ($q) => $q->where('is_active', true)]);

        $offers = self::groupOffers($solution->services);

        return view('pages.solutions.show', [
            'solution' => $solution,
            'offers' => $offers,
            'setupServices' => $offers->get('setup')['services'] ?? collect(),
            'fromMonthly' => $offers->get('subscription')['services']?->where('unit', 'Mois')->where('price', '>', 0)->min('price'),
            'others' => Solution::query()->where('is_active', true)->whereKeyNot($solution->id)->orderBy('position')->get(),
        ]);
    }

    /**
     * Regroupe les prestations d'une solution par famille de formule (familles vides omises).
     *
     * @param  Collection<int, Service>  $services
     * @return Collection<string, array{key: string, label: string, note: string, services: Collection<int, Service>}>
     */
    public static function groupOffers(Collection $services): Collection
    {
        $grouped = $services->groupBy(function (Service $service): string {
            $formula = Str::lower(Str::after($service->name, ' – '));

            foreach (self::OFFER_TYPES as $key => $type) {
                if ($type['prefix'] !== '' && Str::startsWith($formula, $type['prefix'])) {
                    return $key;
                }
            }

            return 'other';
        });

        return collect(self::OFFER_TYPES)
            ->filter(fn (array $type, string $key) => $grouped->has($key))
            ->map(fn (array $type, string $key) => [
                'key' => $key,
                'label' => $type['label'],
                'note' => $type['note'],
                'services' => $grouped->get($key)->values(),
            ]);
    }
}
