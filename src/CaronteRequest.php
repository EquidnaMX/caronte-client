<?php

namespace Equidna\Caronte;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Equidna\Caronte\Tools\RouteHelper;
use Equidna\Caronte\Tools\ResponseHelper;
use Equidna\Caronte\Facades\Caronte;
use Exception;
use Illuminate\Support\Facades\Cookie;

class CaronteRequest
{
    private function __construct()
    {
        //ONLY STATIC METHODS ALLOWED
    }

    public static function userPasswordLogin(Request $request): Response|RedirectResponse
    {
        $decoded_url  = base64_decode($request->callback_url);

        if (!empty($decoded_url) && $decoded_url !== '\\') {
            $callback_url = $decoded_url;
        } else {
            $callback_url = config('caronte.SUCCESS_URL');
        }

        try {
            $caronte_response = HTTP::withOptions(
                [
                    'verify' => !config('caronte.ALLOW_HTTP_REQUESTS')
                ]
            )->post(
                config('caronte.URL') . 'api/v2/login',
                [
                    'email'    => $request->email,
                    'password' => $request->password,
                    'app_id'   => config('caronte.APP_ID')
                ]
            );



            if ($caronte_response->failed()) {
                throw new RequestException(response: $caronte_response);
            }

            $token  = CaronteToken::validateToken(raw_token: $caronte_response->body());
        } catch (Exception $e) {
            return ResponseHelper::badRequest(message: $e->getMessage());
        }

        if (RouteHelper::isAPI()) {
            return response($token->toString(), 200);
        }

        Caronte::saveToken($token->toString());

        return redirect($callback_url)->with(['success' => 'Sesión iniciada con éxito']);
    }

    public static function twoFactorTokenRequest(Request $request)
    {
        try {
            $caronte_response = HTTP::withOptions(
                [
                    'verify' => !config('caronte.ALLOW_HTTP_REQUESTS')
                ]
            )->post(
                config('caronte.URL') . 'api/2fa',
                [
                    'application_url' => config('app.url'),
                    'app_id'          => config('caronte.APP_ID'),
                    'callback_url'    => $request->callback_url,
                    'email'           => $request->email
                ]
            );

            if ($caronte_response->failed()) {
                throw new RequestException($caronte_response);
            }

            if (RouteHelper::isAPI()) {
                return response("Authentication email sent to " . $request->email, 200);
            }

            return back()->with(['success' => $caronte_response->body()]);
        } catch (Exception $e) {
            return ResponseHelper::badRequest($e->getMessage());
        }
    }

    public static function twoFactorTokenLogin(Request $request, $token)
    {
        $decoded_url  = base64_decode($request->callback_url);

        if (!empty($decoded_url) && $decoded_url !== '\\') {
            $callback_url = $decoded_url;
        } else {
            $callback_url = config('caronte.SUCCESS_URL');
        }

        try {
            $caronte_response = HTTP::withOptions(
                [
                    'verify' => !config('caronte.ALLOW_HTTP_REQUESTS')
                ]
            )->get(config('caronte.URL') . 'api/2fa/' . $token);

            if ($caronte_response->failed()) {
                throw new RequestException($caronte_response);
            }

            $token = CaronteToken::validateToken(raw_token: $caronte_response->body());
        } catch (RequestException $e) {
            return ResponseHelper::badRequest($e->getMessage());
        }

        if (RouteHelper::isAPI()) {
            return response($token->toString(), 200);
        }

        Caronte::saveToken($token->toString());

        return redirect($callback_url)->with('success', 'Sesión iniciada con éxito');
    }

    public static function logout(Request $request, $logout_all_sessions = false)
    {
        try {
            $caronte_response = HTTP::withOptions(
                [
                    'verify' => !config('caronte.ALLOW_HTTP_REQUESTS')
                ]
            )->withHeaders(
                [
                    'Authorization' => "Bearer " . Caronte::getToken()->toString()
                ]
            )->get(config('caronte.URL') . 'api/logout' . ($logout_all_sessions ? 'All' : ''));

            if ($caronte_response->failed()) {
                throw new RequestException($caronte_response);
            }

            $response_array = ['success' => 'Sesión cerrada con éxito'];
        } catch (RequestException $e) {
            $response_array = ['error' => $e->getMessage()];
        }

        Caronte::clearToken();

        if (RouteHelper::isAPI()) {
            return response('Logout complete', 200);
        }

        return redirect(config('caronte.LOGIN_URL'))->with($response_array);
    }
}
