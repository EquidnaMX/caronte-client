<?php

namespace Gruelas\Caronte\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
//
use Gruelas\Caronte\Caronte;
use Gruelas\Caronte\Facades\CaronteFacade;

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

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'caronte');

        $this->publishes(
            [
                __DIR__ . '/../resources/views' => resource_path('views/vendor/caronte'),
            ],
            'views'
        );

        $loader = AliasLoader::getInstance();
        $loader->alias('Caronte', CaronteFacade::class);
    }
}
