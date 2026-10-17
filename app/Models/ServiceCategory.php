<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ServiceCategory extends Model
{
    protected $fillable = ['name', 'slug', 'code', 'description', 'icon', 'position', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $category) {
            $category->slug = $category->slug ?: Str::slug($category->name);
        });
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class)->orderBy('position')->orderBy('name');
    }

    public function activeServices(): HasMany
    {
        return $this->services()->where('is_active', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
