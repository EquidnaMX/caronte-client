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
     * @param Request $request
     * @return bool
     */
    public static function isAPI(Request $request): bool
    {
        return $request->is('api/*');
    }

    /**
     * Determine if the request is a hook request.
     *
     * @param Request $request
     * @return bool
     */
    public static function isHook(Request $request): bool
    {
        return $request->is('hooks/*');
    }

    /**
     * Determine if the request is an IoT request.
     *
     * @param Request $request
     * @return bool
     */
    public static function isIoT(Request $request): bool
    {
        return $request->is('iot/*');
    }
}