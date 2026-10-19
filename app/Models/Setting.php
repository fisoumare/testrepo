<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public const CACHE_KEY = 'site.settings';

    protected static function booted(): void
    {
        static::saved(fn () => static::forget());
        static::deleted(fn () => static::forget());
    }

    /**
     * Tous les paramètres, lus une seule fois par requête.
     *
     * Le cache applicatif (base de données en production) conserve les valeurs d'une requête à l'autre ;
     * Cache::memo() les mémoïse pour la durée de la requête, sinon chaque setting() de la page relirait
     * la table `cache` (une cinquantaine de lectures sur l'accueil). La mémoïsation est liée à l'instance
     * de l'application : elle ne survit ni à la requête, ni à un test.
     *
     * @return array<string, string|null>
     */
    public static function all_(): array
    {
        return Cache::memo()->rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all());
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::all_()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => is_array($value) ? json_encode($value) : $value]);
    }

    /** Oublie les paramètres mémoïsés et mis en cache : la prochaine lecture repart de la base. */
    public static function forget(): void
    {
        Cache::memo()->forget(self::CACHE_KEY);
    }
}
