<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Inquiry extends Model
{
    public const TYPE_CONTACT = 'contact';

    public const TYPE_QUOTE = 'quote';

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
