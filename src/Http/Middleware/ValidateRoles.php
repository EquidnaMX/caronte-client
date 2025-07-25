<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.3.2
 *
 */

namespace Equidna\Caronte\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Equidna\Toolkit\Helpers\ResponseHelper;
use Equidna\Caronte\Helpers\PermissionHelper;
use Equidna\Toolkit\Exceptions\UnauthorizedException;
use Exception;
use Closure;

/**
 * Middleware to validate if the user has the required roles to access a feature.
 * Should always be used after ValidateSession middleware.
 *
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.3.1
 */
class ValidateRoles
{
    /**
     * Handle an incoming request and check user roles.
     *
     * @param Request $request HTTP request instance.
     * @param Closure $next Next middleware closure.
     * @param mixed ...$roles Required roles.
     * @return Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        try {
            if (!PermissionHelper::hasRoles(roles: $roles)) {
                return ResponseHelper::forbidden(
                    message: 'User does not have access access to this feature',
                    errors: [
                        'User does not have the required roles: ' . implode(', ', $roles)
                    ],
                );
            }
        } catch (Exception | UnauthorizedException $e) {
            return ResponseHelper::unauthorized(
                message: $e->getMessage(),
                forward_url: config('caronte.LOGIN_URL')
            );
        }

        return $next($request);
    }
}
