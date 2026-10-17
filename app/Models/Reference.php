<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Reference extends Model
{
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
            $reference->slug = $reference->slug ?: Str::slug($reference->acronym ?: $reference->name);
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
