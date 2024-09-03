<?php

namespace Equidna\Caronte\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Equidna\Caronte\Tools\ResponseHelper;
use Equidna\Caronte\Tools\RouteHelper;
use Equidna\Caronte\Tools\PermissionHelper;
use Exception;
use Closure;
use Caronte;

class ValidateSession
{
    public function handle(Request $request, Closure $next): Response
    {
        dd("ValidateSession");
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
