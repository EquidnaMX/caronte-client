<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.1.0
 * gruelas@gruelasjr
 *
 */

namespace Equidna\Caronte\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Equidna\Caronte\Caronte;
use Equidna\Caronte\Console\Commands\NotifyClientConfigurationCommand;

class CaronteServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            Caronte::class,
            fn() => new Caronte()
        );

        $this->mergeConfigFrom(__DIR__ . '/../config/caronte.php', 'caronte');
        $this->mergeConfigFrom(__DIR__ . '/../config/caronte-roles.php', 'caronte-roles');
    }

    public function boot(Router $router)
    {
        //Registers the Caronte alias and facade.
        $loader = AliasLoader::getInstance();
        $loader->alias('Equidna\Caronte', \Equidna\Caronte\Facades\Caronte::class);

        //Registers the middleware
        $router->aliasMiddleware('Caronte.ValidateSession', \Equidna\Caronte\Http\Middleware\ValidateSession::class);
        $router->aliasMiddleware('Caronte.ValidateRoles', \Equidna\Caronte\Http\Middleware\ValidateRoles::class);

        //Registers the base Routes for clients
        Route::prefix(config('caronte.ROUTES_PREFIX'))->middleware(['web'])->group(
            function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
            }
        );

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'caronte');
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        //Config for roles
        $this->publishes(
            [
                __DIR__ . '/../config/caronte-roles.php' => config_path('caronte-roles.php'),
            ],
            [
                'caronte:roles',
                'caronte',
            ]
        );

        //Views
        $this->publishes(
            [
                __DIR__ . '/../resources/views' => resource_path('views/vendor/caronte'),
            ],
            [
                'caronte:views',
                'caronte',
            ]
        );

        //Assets
        $this->publishes(
            [
                __DIR__ . '/../resources/assets' => public_path('vendor/caronte'),
            ],
            [
                'caronte:assets',
                'caronte',
            ]
        );

        //Migrations
        $this->publishes(
            [
                __DIR__ . '/../migrations' => database_path('migrations'),
            ],
            [
                'caronte:migrations',
                'caronte'
            ]
        );

        //Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                NotifyClientConfigurationCommand::class
            ]);
        }
    }
}
