<?php

namespace Gruelas\Caronte\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Closure;

class ValidateApplication
{

    public function handle(Request $request, Closure $next): Response
    {
        if (CarontePermissionValidator::hasApplication()) {
            return $next($request);
        }

        CaronteHelper::handleFailedResponse(
            'The user does not have permissions for this application',
            403
        );
    }
}
