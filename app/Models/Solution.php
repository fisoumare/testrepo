<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Solution extends Model
{
    use HasSlug;

    protected $fillable = [
        'name', 'slug', 'code', 'tagline', 'audience', 'summary', 'description', 'features', 'included',
        'website_url', 'accent', 'image', 'position', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'included' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $solution) {
            $solution->slug = $solution->slug ?: static::slugFrom($solution->name);
        });
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class)->orderBy('position')->orderBy('name');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
