<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.0.0
 * gruelas@gruelasjr
 *
 */

namespace Equidna\Caronte\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Equidna\Caronte\Caronte;

class CaronteServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            Caronte::class,
            fn() => new Caronte()
        );

        $this->mergeConfigFrom(__DIR__ . '/../config/caronte.php', 'caronte');
    }

    public function boot(Router $router)
    {
        //Registers the Caronte alias and facade.
        $loader = AliasLoader::getInstance();
        $loader->alias('Equidna\Caronte', \Equidna\Caronte\Facades\Caronte::class);

        //Registers the middleware
        $router->aliasMiddleware('Caronte.ValidateSession', \Equidna\Caronte\Http\Middleware\ValidateSession::class);
        $router->aliasMiddleware('Caronte.ValidateRoles', \Equidna\Caronte\Http\Middleware\ValidateRoles::class);

        Route::prefix(config('caronte.ROUTES_PREFIX'))->middleware(['web'])->group(
            function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
            }
        );

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'caronte');
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        //Views
        $this->publishes(
            [
                __DIR__ . '/../resources/views' => resource_path('views/vendor/caronte'),
            ],
            [
                'views',
            ]
        );

        //Assets
        $this->publishes(
            [
                __DIR__ . '/../resources/assets' => public_path('vendor/caronte'),
            ],
            [
                'assets',
            ]
        );

        //Migrations
        $this->publishes(
            [
                __DIR__ . '/../migrations' => database_path('migrations'),
            ],
            [
                'migrations'
            ]
        );
    }
}
