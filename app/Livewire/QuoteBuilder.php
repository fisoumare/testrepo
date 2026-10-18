<?php

namespace App\Livewire;

use App\Livewire\Concerns\SubmitsInquiries;
use App\Models\Inquiry;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\Money;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Session;
use Livewire\Component;

/**
 * Configurateur d'estimation en trois étapes : composer la sélection,
 * renseigner ses coordonnées, confirmation avec la référence de la demande.
 */
class QuoteBuilder extends Component
{
    use SubmitsInquiries;

    public const STEP_COMPOSE = 1;

    public const STEP_DETAILS = 2;

    public const STEP_SENT = 3;

    public const MAX_QUANTITY = 99;

    #[Locked]
    public int $step = self::STEP_COMPOSE;

    public string $search = '';

    /** Slug de la catégorie filtrée ; null pour tout afficher. */
    public ?string $category = null;

    /**
     * Sélection courante : identifiant de prestation => quantité.
     * Conservée en session pour survivre à la navigation.
     *
     * @var array<int, int>
     */
    #[Session(key: 'quote.items')]
    public array $items = [];

    public string $name = '';

    public string $organization = '';

    public string $email = '';

    public string $phone = '';

    public string $message = '';

    /** Référence de la demande enregistrée (étape 3). */
    #[Locked]
    public ?string $reference = null;

    /**
     * Récapitulatif figé de la demande envoyée (étape 3) :
     * ['items' => [...], 'total_ht' => int, 'vat' => int, 'total_ttc' => int, 'on_quote' => int].
     *
     * @var array<string, mixed>
     */
    #[Locked]
    public array $sent = [];

    public function mount(): void
    {
        $this->items = $this->sanitizeItems($this->items);

        $slug = request()->query('service');

        if (is_string($slug) && $slug !== '') {
            $service = Service::query()->active()->where('slug', $slug)->first();

            if ($service && ! isset($this->items[$service->id])) {
                $this->items[$service->id] = 1;
            }
        }
    }

    /** @return array<string, array<int, string>> */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'organization' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** Validation champ par champ dès la perte de focus (wire:model.blur). */
    public function updated(string $property): void
    {
        if (array_key_exists($property, $this->rules())) {
            $this->validateOnly($property);
        }
    }

    /** Quantité saisie dans le panier : on borne entre 1 et MAX_QUANTITY, entiers seulement. */
    public function updatedItems(mixed $value, string $key): void
    {
        $id = (int) $key;

        if (isset($this->items[$id])) {
            $this->items[$id] = $this->clampQuantity($value);
        }

        unset($this->cart);
    }

    // ----------------------------------------------------------------- Étape 1

    public function filter(?string $slug = null): void
    {
        $this->category = $slug ?: null;
    }

    public function add(int $serviceId): void
    {
        if (! Service::query()->active()->whereKey($serviceId)->exists()) {
            return;
        }

        $this->items[$serviceId] = $this->clampQuantity(($this->items[$serviceId] ?? 0) + 1);
        $this->resetErrorBag('items');
        unset($this->cart);
    }

    public function increment(int $serviceId): void
    {
        if (isset($this->items[$serviceId])) {
            $this->items[$serviceId] = $this->clampQuantity($this->items[$serviceId] + 1);
            unset($this->cart);
        }
    }

    public function decrement(int $serviceId): void
    {
        if (! isset($this->items[$serviceId])) {
            return;
        }

        if ($this->items[$serviceId] <= 1) {
            $this->remove($serviceId);

            return;
        }

        $this->items[$serviceId]--;
        unset($this->cart);
    }

    public function remove(int $serviceId): void
    {
        unset($this->items[$serviceId], $this->cart);
    }

    public function clear(): void
    {
        $this->items = [];
        unset($this->cart);
    }

    public function goToDetails(): void
    {
        if ($this->cart['count'] === 0) {
            $this->addError('items', 'Ajoutez au moins une prestation avant de demander un devis.');

            return;
        }

        $this->resetErrorBag();
        $this->step = self::STEP_DETAILS;
    }

    // ----------------------------------------------------------------- Étape 2

    public function backToCatalog(): void
    {
        $this->resetErrorBag();
        $this->step = self::STEP_COMPOSE;
    }

    public function submit(): void
    {
        $cart = $this->cart;

        if ($cart['count'] === 0) {
            $this->addError('items', 'Votre sélection est vide : ajoutez au moins une prestation.');
            $this->step = self::STEP_COMPOSE;

            return;
        }

        $this->validate();

        if ($throttled = $this->throttleMessage()) {
            $this->addError('form', $throttled);

            return;
        }

        $this->sent = $this->recap($cart);
        $this->rememberAttempt();

        // Pot-de-miel rempli : on affiche la confirmation sans rien enregistrer ni envoyer.
        if ($this->isTrapped()) {
            $this->reference = Inquiry::nextReference(Inquiry::TYPE_QUOTE);
            $this->finish();

            return;
        }

        $inquiry = Inquiry::query()->create([
            'type' => Inquiry::TYPE_QUOTE,
            'name' => trim($this->name),
            'organization' => trim($this->organization) ?: null,
            'email' => trim($this->email),
            'phone' => trim($this->phone),
            'subject' => 'Demande de devis en ligne',
            'message' => trim($this->message) ?: null,
            'items' => $this->sent['items'],
            'total_ht' => $this->sent['total_ht'],
            'ip' => request()->ip(),
        ]);

        $this->notify($inquiry);

        $this->reference = $inquiry->reference;
        $this->finish();
    }

    // ----------------------------------------------------------------- Étape 3

    public function restart(): void
    {
        $this->reset('search', 'category', 'name', 'organization', 'email', 'phone', 'message', 'website', 'reference', 'sent');
        $this->items = [];
        $this->resetErrorBag();
        unset($this->cart);
        $this->step = self::STEP_COMPOSE;
    }

    public function whatsappUrl(): string
    {
        $total = (int) ($this->sent['total_ht'] ?? 0);

        return whatsapp_url(sprintf(
            "Bonjour SOUMATIC, je viens d'envoyer la demande %s (total HT\u{00A0}: %s).",
            $this->reference,
            gnf($total),
        ));
    }

    // -------------------------------------------------------------- Calculés

    /** @return Collection<int, ServiceCategory> */
    #[Computed]
    public function categories(): Collection
    {
        return ServiceCategory::query()
            ->where('is_active', true)
            ->withCount(['services as services_count' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('position')
            ->get();
    }

    /**
     * Catalogue complet (120 lignes), chargé une fois par requête ; le filtrage se fait
     * en mémoire pour ignorer les accents et la casse.
     *
     * @return Collection<int, Service>
     */
    #[Computed]
    public function catalogue(): Collection
    {
        return Service::query()
            ->active()
            ->with('category')
            ->orderBy(ServiceCategory::query()->select('position')->whereColumn('service_categories.id', 'services.service_category_id'))
            ->orderBy('position')
            ->orderBy('name')
            ->get();
    }

    /** @return Collection<int, Service> */
    #[Computed]
    public function services(): Collection
    {
        $term = $this->normalize($this->search);

        return $this->catalogue
            ->when($this->category, fn (Collection $c, string $slug) => $c->filter(fn (Service $s) => $s->category->slug === $slug))
            ->when($term !== '', fn (Collection $c) => $c->filter(fn (Service $s) => str_contains(
                $this->normalize($s->number.' '.$s->name.' '.$s->summary.' '.$s->category->name),
                $term,
            )))
            ->values();
    }

    /**
     * Panier : lignes (avec le modèle Service), totaux et nombre de prestations « sur devis ».
     *
     * @return array{lines: list<array{service: Service, quantity: int, total: int}>, count: int, total_ht: int, vat: int, total_ttc: int, on_quote: int}
     */
    #[Computed]
    public function cart(): array
    {
        $lines = [];
        $totalHt = 0;
        $onQuote = 0;

        if ($this->items !== []) {
            $services = Service::query()->active()->whereIn('id', array_keys($this->items))->get()->keyBy('id');

            foreach ($this->items as $id => $quantity) {
                $service = $services->get($id);

                if (! $service) {
                    continue;
                }

                $quantity = $this->clampQuantity($quantity);
                $total = $service->isOnQuote() ? 0 : $service->price * $quantity;

                $lines[] = ['service' => $service, 'quantity' => $quantity, 'total' => $total];
                $totalHt += $total;
                $onQuote += $service->isOnQuote() ? 1 : 0;
            }
        }

        return [
            'lines' => $lines,
            'count' => count($lines),
            'total_ht' => $totalHt,
            'vat' => Money::vat($totalHt),
            'total_ttc' => Money::ttc($totalHt),
            'on_quote' => $onQuote,
        ];
    }

    // -------------------------------------------------------------- Interne

    /**
     * Récapitulatif sérialisable, figé au moment de l'envoi (enregistré dans Inquiry::items).
     *
     * @param  array{lines: list<array{service: Service, quantity: int, total: int}>, total_ht: int, vat: int, total_ttc: int, on_quote: int}  $cart
     * @return array{items: list<array{service_id: int, number: ?string, name: string, unit: string, quantity: int, unit_price: int, total: int}>, total_ht: int, vat: int, total_ttc: int, on_quote: int}
     */
    protected function recap(array $cart): array
    {
        return [
            'items' => array_map(fn (array $line) => [
                'service_id' => $line['service']->id,
                'number' => $line['service']->number,
                'name' => $line['service']->name,
                'unit' => $line['service']->unit,
                'quantity' => $line['quantity'],
                'unit_price' => $line['service']->price,
                'total' => $line['total'],
            ], $cart['lines']),
            'total_ht' => $cart['total_ht'],
            'vat' => $cart['vat'],
            'total_ttc' => $cart['total_ttc'],
            'on_quote' => $cart['on_quote'],
        ];
    }

    protected function finish(): void
    {
        // Vide la sélection : l'attribut Session réécrit la valeur en session à la fin de la requête.
        $this->items = [];
        unset($this->cart);
        $this->step = self::STEP_SENT;
    }

    /**
     * Ne garde que des identifiants de prestations actives avec une quantité valide.
     *
     * @param  array<int|string, mixed>  $items
     * @return array<int, int>
     */
    protected function sanitizeItems(array $items): array
    {
        $clean = [];

        foreach ($items as $id => $quantity) {
            if (is_numeric($id) && (int) $id > 0) {
                $clean[(int) $id] = $this->clampQuantity($quantity);
            }
        }

        if ($clean === []) {
            return [];
        }

        $existing = Service::query()->active()->whereIn('id', array_keys($clean))->pluck('id')->all();

        return array_intersect_key($clean, array_flip($existing));
    }

    protected function clampQuantity(mixed $quantity): int
    {
        return max(1, min(self::MAX_QUANTITY, (int) $quantity));
    }

    protected function normalize(?string $text): string
    {
        return Str::of((string) $text)->ascii()->lower()->squish()->toString();
    }

    public function render(): View
    {
        return view('livewire.quote-builder');
    }
}
