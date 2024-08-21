<?php

namespace App\Http\Middleware\Auth;

use App\Classes\Caronte\CarontePermissionValidator;
use App\Classes\Caronte\CaronteHelper;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Closure;

class ValidateRoles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (CarontePermissionValidator::hasRoles(roles: $roles)) {
            return $next($request);
        }

        CaronteHelper::handleFailedResponse(
            'Insufficient permissions',
            403
        );
    }
}
