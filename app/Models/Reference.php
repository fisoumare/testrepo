<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Reference extends Model
{
    use HasSlug;

    protected $fillable = [
        'name', 'slug', 'acronym', 'sector', 'location', 'website_url', 'headline', 'description',
        'services', 'year', 'logo', 'is_featured', 'is_published', 'position',
    ];

    protected function casts(): array
    {
        return [
            'services' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'year' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $reference) {
            $reference->slug = $reference->slug ?: static::slugFrom($reference->acronym ?: $reference->name);
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
