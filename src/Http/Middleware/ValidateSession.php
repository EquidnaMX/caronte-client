<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.0.0
 *
 */

namespace Equidna\Caronte\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Equidna\Caronte\Facades\Caronte;
use Equidna\Caronte\Helpers\PermissionHelper;
use Equidna\Toolkit\Helpers\ResponseHelper;
use Equidna\Toolkit\Helpers\RouteHelper;
use Exception;
use Closure;

//This class validates the presence of a caronte token in the request and checks if the user has access to the application.
class ValidateSession
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $token = Caronte::getToken();
        } catch (Exception $e) {
            return ResponseHelper::unautorized(
                message: $e->getMessage(),
                forward_url: config('caronte.LOGIN_URL')
            );
        }

        if (PermissionHelper::hasApplication()) {
            $response = $next($request);
        } else {
            $response = ResponseHelper::forbidden(
                message: 'User does not have access to this application',
                forward_url: config('caronte.LOGIN_URL')
            );
        }

        if (Caronte::tokenWasExchanged() && RouteHelper::isAPI()) {
            $response->headers->set('new_token', $token->toString());
        }

        return $response;
    }
}
