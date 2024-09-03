<?php

namespace Equidna\Caronte\Facades;

use Illuminate\Support\Facades\Facade;
use Equidna\Caronte\Caronte;

class CaronteFacade extends Facade
{
    /**
     * Get the accessor for the facade.
     *
     * @return string The class name of the accessor.
     */
    protected static function getFacadeAccessor(): string
    {
        return Caronte::class;
    }
}
