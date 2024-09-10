<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.0.0
 */

namespace Equidna\Caronte\Facades;

use Illuminate\Support\Facades\Facade;
use Equidna\Caronte\Caronte as CaronteClass;

class Caronte extends Facade
{
    /**
     * Get the accessor for the facade.
     *
     * @return string The class name of the accessor.
     */
    protected static function getFacadeAccessor(): string
    {
        return CaronteClass::class;
    }
}
