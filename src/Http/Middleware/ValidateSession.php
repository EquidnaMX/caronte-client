<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.3.2
 *
 */

namespace Equidna\Caronte\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Equidna\Caronte\Facades\Caronte;
use Equidna\Caronte\Helpers\PermissionHelper;
use Equidna\Toolkit\Helpers\ResponseHelper;
use Equidna\Toolkit\Helpers\RouteHelper;
use Equidna\Toolkit\Exceptions\UnauthorizedException;
use Exception;
use Closure;

/**
 * Middleware to validate the presence of a Caronte token and user access to the application.
 *
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.3.1
 */
class ValidateSession
{
    /**
     * Handle an incoming request and check for a valid Caronte token and application access.
     *
     * @param Request $request HTTP request instance.
     * @param Closure $next Next middleware closure.
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $token = Caronte::getToken();

            if (PermissionHelper::hasApplication()) {
                $response = $next($request);
            } else {
                $response = ResponseHelper::forbidden(
                    message: 'User does not have access to this application',
                    errors: [
                        'User does not have access to this application'
                    ],
                    forward_url: config('caronte.LOGIN_URL')
                );
            }
        } catch (Exception | UnauthorizedException $e) {
            return ResponseHelper::unauthorized(
                message: $e->getMessage(),
                forward_url: config('caronte.LOGIN_URL')
            );
        }

        if (Caronte::tokenWasExchanged() && RouteHelper::isAPI()) {
            $response->headers->set('new_token', $token->toString());
        }

        return $response;
    }
}
