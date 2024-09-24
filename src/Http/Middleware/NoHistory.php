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
class NoHistory
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $response = $next($request);
        return $response;
    }
}
