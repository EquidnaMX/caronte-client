<?php

namespace Gruelas\Caronte\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Gruelas\Caronte\Tools\PermissionHelper;
use Gruelas\Caronte\Tools\ResponseHelper;
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

        if (!PermissionHelper::hasRoles(roles: $roles)) {
            return ResponseHelper::forbidden('User does not have access access to this feature');
        }

        return $next($request);
    }
}
