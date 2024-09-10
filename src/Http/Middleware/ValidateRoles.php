<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.0.0
 *
 */

namespace Equidna\Caronte\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Equidna\Toolkit\Helpers\ResponseHelper;
use Equidna\Caronte\Helpers\PermissionHelper;
use Closure;

//This class validates if the user has the necessary roles to access a feature.
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
