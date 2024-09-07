<?php

namespace Equidna\Caronte\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Equidna\Caronte\Tools\PermissionHelper;
use Equidna\Caronte\Tools\ResponseHelper;
use Closure;

class ValidateRoles
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {

        if (!PermissionHelper::hasRoles(roles: $roles)) {
            return ResponseHelper::forbidden('User does not have access access to this feature');
        }

        return $next($request);
    }
}
