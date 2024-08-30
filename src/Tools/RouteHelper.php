<?php

namespace Gruelas\Caronte\Tools;

use Illuminate\Http\Request;

class RouteHelper
{
    private function __construct()
    {
        //
    }

    /**
     * Determine if the request is an API request.
     *
     * @return bool
     */
    public static function isAPI(): bool
    {
        return request()->is('api/*');
    }

    /**
     * Determine if the request is a hook request.
     *
     * @return bool
     */
    public static function isHook(): bool
    {
        return request()->is('hooks/*');
    }

    /**
     * Determine if the request is an IoT request.
     *
     * @return bool
     */
    public static function isIoT(): bool
    {
        return request()->is('iot/*');
    }
}
