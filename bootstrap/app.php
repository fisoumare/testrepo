<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Les proxys de confiance sont réglés ci-dessous, une fois la configuration chargée : ce rappel
        // s'exécute avant la lecture de .env, où config('app.trusted_proxies') n'est pas encore disponible.
    })
    ->booted(function (Application $app): void {
        // TRUSTED_PROXIES (config/app.php) : en-têtes X-Forwarded-* lus derrière un reverse proxy ou un CDN,
        // pour l'adresse IP réelle du visiteur et le schéma https. Vide : aucun proxy n'est pris en compte.
        $proxies = trim((string) $app['config']->get('app.trusted_proxies'));

        if ($proxies !== '') {
            TrustProxies::at($proxies);
        }

        // APP_URL en https : toutes les URL générées (canoniques, plan du site, ressources) le sont aussi,
        // même si le proxy TLS n'envoie pas X-Forwarded-Proto.
        if (str_starts_with((string) $app['config']->get('app.url'), 'https://')) {
            URL::forceHttps();
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
