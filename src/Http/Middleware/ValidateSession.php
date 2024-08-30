<?php

namespace Gruelas\Caronte\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Gruelas\Caronte\Tools\ResponseHelper;
use Gruelas\Caronte\Tools\RouteHelper;
use Gruelas\Caronte\Tools\PermissionHelper;
use Exception;
use Closure;
use Caronte;

class ValidateSession
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $token = Caronte::getToken();
        } catch (Exception $e) {
            return ResponseHelper::unautorized($e->getMessage());
        }

        if (PermissionHelper::hasApplication()) {
            $response = $next($request);
        } else {
            $response = ResponseHelper::forbidden('User does not have access to this application');
        }

        if (Caronte::tokenWasExchanged() && RouteHelper::isAPI()) {
            $response->headers->set('new_token', $token->toString());
        }

        return $response;
    }
}
