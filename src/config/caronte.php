<?php

return [
    'URL'                   => env('CARONTE_URL', 'https://dev.caronte.sistemas-teleurban.com/'),
    'TOKEN_KEY'             => env('CARONTE_TOKEN_KEY', 'NO TOKEN KEY PROVIDED'),

    'ALLOW_HTTP_REQUESTS'   => env('CARONTE_ALLOW_HTTP_REQUESTS', false),

    'ISSUER_ID'      => env('CARONTE_ISSUER_ID', 'com.grupoteleurban.caronte'),
    'ENFORCE_ISSUER' => env('CARONTE_ENFORCE_ISSUER', true),

    'APP_ID'         => env('CARONTE_APP_ID', 'com.femaseisa.hela'),
    'APP_SECRET'     => env('CARONTE_APP_SECRET', '2ff5cbbe1de565e664adc970f4ad34ca6aa50f6a'),

    'USE_2FA'        => env('CARONTE_2FA', false),

    'SUCCESS_URL'    => env('CARONTE_SUCCESS_URL', '/'),
    'LOGIN_URL'      => env('CARONTE_LOGIN_URL', '/login'),
];
