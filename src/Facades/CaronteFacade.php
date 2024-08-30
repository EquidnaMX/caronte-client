<?php

namespace Gruelas\Caronte\Facades;

use Illuminate\Support\Facades\Facade;
use Gruelas\Caronte\Caronte;

class CaronteFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Caronte::class;
    }
}
