<?php

namespace Gruelas\Caronte\Facades;

use Illuminate\Support\Facades\Facade;
use Gruelas\Caronte\Caronte as CaronteService;

class Caronte extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CaronteService::class;
    }
}
