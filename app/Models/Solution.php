<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Solution extends Model
{
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
            $solution->slug = $solution->slug ?: Str::slug($solution->name);
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
