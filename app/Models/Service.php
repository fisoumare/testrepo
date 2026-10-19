<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Service extends Model
{
    protected $fillable = [
        'service_category_id', 'solution_id', 'compiniya_id', 'number', 'name', 'slug', 'unit',
        'price', 'summary', 'description', 'is_featured', 'is_active', 'position',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $service) {
            $service->slug = $service->slug ?: Str::slug($service->name);
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function solution(): BelongsTo
    {
        return $this->belongsTo(Solution::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function isOnQuote(): bool
    {
        return $this->price <= 0;
    }

    /** Unités de temps : le prix s'écrit « 600 000 GNF HT / jour » et l'unité n'est pas répétée à côté. */
    public const TIME_UNITS = ['Mois', 'An', 'Jour', 'Heure'];

    public function hasTimeUnit(): bool
    {
        return in_array($this->unit, self::TIME_UNITS, true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
