<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.3.2
 *
 */

return [
    'URL'       => env('CARONTE_URL', ''),
    'VERSION'   => env('CARONTE_VERSION', 'v2'),
    'TOKEN_KEY' => env('CARONTE_TOKEN_KEY', ''), //!WILL BE DEPRECATED IN FUTURE VERSIONS

    'ALLOW_HTTP_REQUESTS'   => env('CARONTE_ALLOW_HTTP_REQUESTS', false),

    'ISSUER_ID'      => env('CARONTE_ISSUER_ID', ''),
    'ENFORCE_ISSUER' => env('CARONTE_ENFORCE_ISSUER', true),

    'APP_ID'         => env('CARONTE_APP_ID', ''),
    'APP_SECRET'     => env('CARONTE_APP_SECRET', ''),

    'USE_2FA'        => env('CARONTE_2FA', false),

    'ROUTES_PREFIX'  => env('CARONTE_ROUTES_PREFIX', ''),
    'SUCCESS_URL'    => env('CARONTE_SUCCESS_URL', '/'),
    'LOGIN_URL'      => env('CARONTE_LOGIN_URL', '/login'),

    'UPDATE_LOCAL_USER' => env('CARONTE_UPDATE_USER', false),

    'USE_INERTIA' => env('CARONTE_USE_INERTIA', false),
];
