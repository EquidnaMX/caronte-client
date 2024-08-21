<?php

namespace Gruelas\Caronte;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Gruelas\Caronte\Tools\RouteHelper;
use Exception;

class CaronteRequest
{
    private function __construct()
    {
        //ONLY STATIC METHODS ALLOWED
    }

    public static function userPasswordLogin(Request $request): Response
    {
        $decoded_url  = base64_decode($request->callback_url);

        if (!empty($decoded_url) && $decoded_url !== '\\') {
            $callback_url = $decoded_url;
        } else {
            $callback_url = config('caronte.SUCCESS_URL');
        }

        try {
            $caronte_response = HTTP::post(
                config('caronte.URL') . 'api/login',
                [
                    'email'    => $request->email,
                    'password' => $request->password,
                    'app_id'   => config('caronte.APP_ID')
                ]
            );

            if ($caronte_response->failed()) {
                //TODO VALIDATE IF EXCEPTION IS THROWN OR ONLY A BAD REQUEST IS RETURNED
                throw new RequestException(response: $caronte_response);
            }

            $token_str = $caronte_response->body();

            CaronteToken::validateToken(raw_token: $token_str); //TODO

            if (RouteHelper::isAPI(request: $request)) {
                return response($token_str, 200);
            }

            $token_id = Str::random(20);
            CaronteToken::setFileToken(token_id: $token_id, token_str: $token_str);
            CaronteToken::setCookie(token_id: $token_id);
        } catch (Exception $e) {
            return CaronteHelper::badRequest($e->getMessage());
        }

        return redirect($callback_url)->with(['success' => 'Sesión iniciada con éxito']);
    }

    public static function twoFactorTokenRequest(Request $request)
    {
        try {
            $caronte_response = HTTP::post(
                config('caronte.URL') . 'api/2fa',
                [
                    'application_url' => config('app.url'),
                    'callback_url'    => $request->callback_url,
                    'app_id'          => config('caronte.APP_ID'),
                    'email'           => $request->email,
                ]
            );

            if ($caronte_response->failed()) {
                throw new RequestException($caronte_response);
            }

            if (CaronteHelper::isAPI()) {
                return response("Authentication email sent to " . $request->email, 200);
            }

            return back()->with(['success' => $caronte_response->body()]);
        } catch (Exception $e) {
            return CaronteHelper::badRequest($e->getMessage());
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
            $caronte_response = HTTP::get(config('caronte.URL') . 'api/2fa/' . $token);

            if ($caronte_response->failed()) {
                throw new RequestException($caronte_response);
            }

            $token_str = $caronte_response->body();

            CaronteToken::validateToken(raw_token: $token_str);

            if (CaronteHelper::isAPI()) {
                return response($token_str, 200);
            }

            $token_id = Str::random(20);
            CaronteToken::setFileToken(token_id: $token_id, token_str: $token_str);
            CaronteToken::setCookie(token_id: $token_id);
        } catch (RequestException $e) {
            return CaronteHelper::badRequest($e->getMessage());
        }

        return redirect($callback_url)->with('success', 'Sesión iniciada con éxito');
    }

    public static function logout(Request $request, $logout_all_sessions = false)
    {
        $token_str = CaronteHelper::webToken();

        try {
            $caronte_response = HTTP::withHeaders(
                [
                    'Authorization' => "Bearer " . $token_str
                ]
            )->get(config('caronte.URL') . 'api/logout' . ($logout_all_sessions ? 'All' : ''));

            if ($caronte_response->failed()) {
                throw new RequestException($caronte_response);
            }

            $response_array = ['success' => 'Sesión cerrada con éxito'];
        } catch (RequestException $e) {
            $response_array = ['error' => $e->getMessage()];
        }

        CaronteToken::forgetCookie();

        return redirect(config('caronte.LOGIN_URL'))->with($response_array);
    }
}
