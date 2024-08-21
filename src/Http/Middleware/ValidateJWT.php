<?php

namespace App\Http\Middleware\Auth;

use App\Classes\Caronte\CaronteHelper;
use App\Classes\Caronte\CaronteToken;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Exception;
use Closure;

class ValidateJWT
{
    /**
     * Handle an incoming request.
     * Checks for a valid JWT token in the request. If the token is valid, it proceeds
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token_str = CaronteHelper::isAPI() ? $request->bearerToken() : CaronteHelper::webToken();
        $token_str = !is_null($token_str) ? $token_str : '';

        try {
            $validation_response = CaronteToken::validateToken(raw_token: $token_str);
        } catch (Exception $e) {
            CaronteHelper::handleFailedResponse(
                $e->getMessage(),
                $e->getCode(),
            );
        }

        $request->attributes->add(['user' => $validation_response->user]);
        $response = $next($request);

        foreach ($validation_response->headers as $header => $value) {
            $response->headers->set($header, $value);
        }

        return $response;
    }
}
