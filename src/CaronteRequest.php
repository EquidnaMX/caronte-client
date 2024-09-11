<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.0.0
 *
 */

namespace Equidna\Caronte;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Equidna\Toolkit\Helpers\RouteHelper;
use Equidna\Toolkit\Helpers\ResponseHelper;
use Equidna\Caronte\Facades\Caronte;
use Exception;

/**
 * This class is responsible for making basic requests to the Caronte server.
 */

class CaronteRequest
{
    private function __construct()
    {
        //ONLY STATIC METHODS ALLOWED
    }

    /**
     * Logs in a user with their password.
     *
     * @param Request $request The request object containing the user's email, password, and callback URL.
     * @return Response|RedirectResponse The response object or a redirect response.
     */
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
                config('caronte.URL') . 'api/' . config('caronte.VERSION') . '/login',
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

    /**
     * Sends a two-factor token request.
     *
     * @param Request $request The request object containing the callback URL and email.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse The response from the server or a redirect response.
     *
     * @throws RequestException If the request to the server fails.
     */
    public static function twoFactorTokenRequest(Request $request): Response|RedirectResponse
    {
        try {
            $caronte_response = HTTP::withOptions(
                [
                    'verify' => !config('caronte.ALLOW_HTTP_REQUESTS')
                ]
            )->post(
                config('caronte.URL') . 'api/' . config('caronte.VERSION') . '/2fa',
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

    /**
     * Logs in the user using a two-factor authentication token.
     *
     * @param Request $request The HTTP request object.
     * @param string $token The two-factor authentication token.
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse The response from the server or a redirect response.
     */
    public static function twoFactorTokenLogin(Request $request, $token): Response|RedirectResponse
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
            )->get(
                config('caronte.URL') . 'api/' . config('caronte.VERSION') . '/2fa/' . $token
            );

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

    /**
     * Logs out the user.
     *
     * @param Request $request The request object.
     * @param bool $logout_all_sessions (optional) Whether to logout from all sessions. Default is false.
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse The response object or redirect response.
     */
    public static function logout(Request $request, $logout_all_sessions = false): Response|RedirectResponse
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
            )->get(
                config('caronte.URL') . 'api/' . config('caronte.VERSION') . '/logout' . ($logout_all_sessions ? 'All' : '')
            );

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
