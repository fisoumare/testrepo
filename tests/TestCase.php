<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Les tests vident et recréent toutes les tables (RefreshDatabase) : ils ne doivent
     * jamais viser la base de développement « soumatic » ni une base de production.
     * Cas typiques : `php artisan test --env=testing` sans fichier .env.testing, ou une
     * configuration mise en cache (`php artisan optimize`) qui ignore phpunit.xml.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $config = $app['config'];
        $connection = (string) $config->get('database.default');
        $driver = (string) $config->get("database.connections.{$connection}.driver");
        $database = (string) $config->get("database.connections.{$connection}.database");

        $isSafe = $driver === 'sqlite'
            ? ($database === ':memory:' || str_contains(basename($database), 'test'))
            : str_ends_with($database, '_test');

        if (! $isSafe) {
            throw new RuntimeException(
                "Tests refusés : ils viseraient la base « {$database} » ({$driver}), qui serait entièrement vidée. "
                .'Lancez `php artisan test` (SQLite en mémoire) ou, pour MySQL, créez `.env.testing` '
                .'avec DB_DATABASE=soumatic_test (README § Tests), puis `php artisan optimize:clear` si la configuration est en cache.'
            );
        }

        return $app;
    }
}
