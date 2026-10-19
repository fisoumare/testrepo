<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Inquiry extends Model
{
    public const TYPE_CONTACT = 'contact';

    public const TYPE_QUOTE = 'quote';

    /** Nombre d'essais d'enregistrement quand deux demandes simultanées calculent la même référence. */
    public const REFERENCE_ATTEMPTS = 3;

    public const STATUS_NEW = 'new';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_ANSWERED = 'answered';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'type', 'reference', 'name', 'organization', 'email', 'phone', 'subject', 'message',
        'items', 'total_ht', 'status', 'internal_notes', 'ip',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'total_ht' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $inquiry) {
            $inquiry->reference = $inquiry->reference ?: static::nextReference($inquiry->type);
            $inquiry->status = $inquiry->status ?: self::STATUS_NEW;
        });
    }

    /**
     * Enregistre une demande en lui attribuant la référence suivante (DEM-2026-0001, MSG-2026-0001).
     *
     * Le calcul de nextReference() puis l'insertion ne sont pas atomiques : deux envois simultanés
     * peuvent obtenir le même numéro. Chaque essai se fait dans une transaction ; si l'index unique
     * sur `reference` est violé, on recommence avec une référence recalculée (au plus REFERENCE_ATTEMPTS).
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function createWithReference(array $attributes): static
    {
        unset($attributes['reference']);

        for ($attempt = 1; ; $attempt++) {
            try {
                return DB::transaction(fn () => static::query()->create($attributes));
            } catch (UniqueConstraintViolationException $e) {
                if ($attempt >= self::REFERENCE_ATTEMPTS) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Référence suivante pour un type : préfixe, année, numéro d'ordre sur quatre chiffres.
     * Le numéro repart du nombre de demandes de l'année et saute les références déjà prises ;
     * la protection contre deux calculs simultanés est assurée par createWithReference().
     */
    public static function nextReference(string $type): string
    {
        $prefix = $type === self::TYPE_QUOTE ? 'DEM' : 'MSG';
        $year = now()->format('Y');
        $count = static::query()->where('type', $type)->whereYear('created_at', $year)->count() + 1;

        do {
            $reference = sprintf('%s-%s-%04d', $prefix, $year, $count++);
        } while (static::query()->where('reference', $reference)->exists());

        return $reference;
    }

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            self::STATUS_NEW => 'Nouvelle',
            self::STATUS_IN_PROGRESS => 'En cours',
            self::STATUS_ANSWERED => 'Répondue',
            self::STATUS_CLOSED => 'Clôturée',
        ];
    }

    public function isQuote(): bool
    {
        return $this->type === self::TYPE_QUOTE;
    }

    public function totalTtc(float $vatRate = 0.18): int
    {
        return (int) round(($this->total_ht ?? 0) * (1 + $vatRate));
    }

    public function excerpt(int $length = 80): string
    {
        return Str::limit((string) $this->message, $length);
    }
}
