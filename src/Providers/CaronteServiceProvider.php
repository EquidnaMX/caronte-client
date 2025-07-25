<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.3.2
 * gruelas@gruelasjr
 *
 */

namespace Equidna\Caronte\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Equidna\Caronte\Caronte;
use Equidna\Caronte\Console\Commands\NotifyClientConfigurationCommand;
use Equidna\Toolkit\Exceptions\ConflictException;

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
        $this->validateCaronteConfig();

        //Registers the Caronte alias and facade.
        $loader = AliasLoader::getInstance();
        $loader->alias('Caronte', \Equidna\Caronte\Facades\Caronte::class);
        $loader->alias('PermissionHelper', \Equidna\Caronte\Helpers\PermissionHelper::class);

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

    /**
     * Validates required Caronte config values and fails early with a clear message.
     *
     * @throws \RuntimeException
     */
    protected function validateCaronteConfig(): void
    {
        $required = [
            'caronte.URL',
            'caronte.VERSION',
            'caronte.APP_ID',
            'caronte.APP_SECRET',
            'caronte.LOGIN_URL',
        ];

        $missing = [];
        foreach ($required as $key) {
            $value = config($key);
            if (is_null($value) || $value === '') {
                $missing[] = $key;
            }
        }

        if (config('caronte.ENFORCE_ISSUER')) {
            if (empty(config('caronte.ISSUER_ID'))) {
                $missing[] = 'caronte.ISSUER_ID';
            }
        }

        if (!empty($missing)) {
            $msg = "Caronte: Missing required configuration: " . implode(', ', $missing) . ". Please check your .env and config/caronte.php.";
            throw new ConflictException($msg);
        }
    }
}
