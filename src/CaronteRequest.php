<?php

/**
 * Handles requests to the Caronte server for authentication and user management.
 *
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.3.2
 */

namespace Equidna\Caronte;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\View\View;
use Equidna\Toolkit\Helpers\RouteHelper;
use Equidna\Toolkit\Helpers\ResponseHelper;
use Equidna\Caronte\Facades\Caronte;
use Equidna\Toolkit\Exceptions\BadRequestException;
use Equidna\Toolkit\Exceptions\UnauthorizedException;
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
     * Log in a user with email and password.
     *
     * @param Request $request HTTP request with user credentials and callback URL.
     * @return JsonResponse|RedirectResponse API response or redirect response.
     * @throws UnauthorizedException If authentication fails.
     */
    public static function userPasswordLogin(Request $request): JsonResponse|RedirectResponse
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
                url: config('caronte.URL') . 'api/' . config('caronte.VERSION') . '/login',
                data: [
                    'email'    => $request->email,
                    'password' => $request->password,
                    'app_id'   => config('caronte.APP_ID')
                ]
            );

            if ($caronte_response->failed()) {
                throw new RequestException(response: $caronte_response);
            }

            $token  = CaronteToken::validateToken(raw_token: $caronte_response->body());
        } catch (RequestException | Exception $e) {
            throw new UnauthorizedException(
                message: $e->getMessage(),
                previous: $e
            );
        }

        if (RouteHelper::isWeb()) {
            Caronte::saveToken($token->toString());
        }

        return ResponseHelper::success(
            message: 'Login successful',
            data: [
                'token' => $token->toString(),
            ],
            forward_url: $callback_url
        );
    }

    /**
     * Send a two-factor authentication token request.
     *
     * @param Request $request HTTP request with email and callback URL.
     * @return JsonResponse|RedirectResponse API response or redirect response.
     * @throws UnauthorizedException If the request fails.
     */
    public static function twoFactorTokenRequest(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $caronte_response = HTTP::withOptions(
                [
                    'verify' => !config('caronte.ALLOW_HTTP_REQUESTS')
                ]
            )->post(
                url: config('caronte.URL') . 'api/' . config('caronte.VERSION') . '/2fa',
                data: [
                    'email'     => $request->email,
                    'app_id'    => config('caronte.APP_ID'),
                    'app_url'   => config('app.url'),
                ]
            );

            if ($caronte_response->failed()) {
                throw new RequestException($caronte_response);
            }

            $response = $caronte_response->body();
        } catch (RequestException | Exception $e) {
            throw new UnauthorizedException(
                message: $e->getMessage(),
                previous: $e
            );
        }

        return ResponseHelper::success(
            message: $response,
            forward_url: config('caronte.LOGIN_URL')
        );
    }

    /**
     * Log in a user using a two-factor authentication token.
     *
     * @param Request $request HTTP request object.
     * @param string $token Two-factor authentication token.
     * @return JsonResponse|RedirectResponse API response or redirect response.
     * @throws UnauthorizedException If authentication fails.
     */
    public static function twoFactorTokenLogin(Request $request, string $token): JsonResponse|RedirectResponse
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
                url: config('caronte.URL') . 'api/' . config('caronte.VERSION') . '/2fa/' . $token,
                data: [
                    'app_id'    => config('caronte.APP_ID'),
                ]
            );

            if ($caronte_response->failed()) {
                throw new RequestException($caronte_response);
            }

            $token = CaronteToken::validateToken(raw_token: $caronte_response->body());
        } catch (RequestException | Exception $e) {
            throw new UnauthorizedException(
                message: $e->getMessage(),
                previous: $e
            );
        }

        if (RouteHelper::isWeb()) {
            Caronte::saveToken($token->toString());
        }

        return ResponseHelper::success(
            message: 'Login successful',
            data: [
                'token' => $token->toString(),
            ],
            forward_url: $callback_url
        );
    }

    /**
     * Initiate password recovery for a user.
     *
     * @param Request $request HTTP request with user email.
     * @return JsonResponse|RedirectResponse API response or redirect response.
     * @throws BadRequestException If the request fails.
     */
    public static function passwordRecoverRequest(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $caronte_response = HTTP::withOptions(
                [
                    'verify' => !config('caronte.ALLOW_HTTP_REQUESTS')
                ]
            )->post(
                url: config('caronte.URL') . 'api/' . config('caronte.VERSION') . '/password/recover',
                data: [
                    'email'   => $request->email,
                    'app_id'  => config('caronte.APP_ID'),
                    'app_url' => config('app.url')
                ]
            );

            if ($caronte_response->failed()) {
                throw new RequestException(response: $caronte_response);
            }

            $response = $caronte_response->body();
        } catch (RequestException | Exception $e) {
            throw new BadRequestException(
                message: $e->getMessage(),
                previous: $e
            );
        }

        return ResponseHelper::success(
            message: $response,
            forward_url: config('caronte.LOGIN_URL')
        );
    }

    /**
     * Validate a password recovery token.
     *
     * @param string $token Password recovery token.
     * @return JsonResponse|View API response or view.
     * @throws UnauthorizedException If validation fails.
     */
    public static function passwordRecoverTokenValidation(string $token): JsonResponse|View
    {
        try {
            $caronte_response = HTTP::withOptions(
                [
                    'verify' => !config('caronte.ALLOW_HTTP_REQUESTS')
                ]
            )->get(
                url: config('caronte.URL') . 'api/' . config('caronte.VERSION') . '/password/recover/' . $token
            );

            if ($caronte_response->failed()) {
                throw new RequestException($caronte_response);
            }

            $response = $caronte_response->body();
        } catch (RequestException | Exception $e) {
            throw new UnauthorizedException(
                message: $e->getMessage(),
                previous: $e
            );
        }

        if (RouteHelper::isAPI()) {
            return ResponseHelper::success(
                message: 'Token validated successfully',
                data: $response
            );
        }

        $token_response = json_decode($response);

        return View('caronte::password-recover')->with(['user' => $token_response->user]);
    }

    /**
     * Complete password recovery for a user.
     *
     * @param Request $request HTTP request with new password.
     * @param string $token Password recovery token.
     * @return JsonResponse|RedirectResponse API response or redirect response.
     * @throws UnauthorizedException If recovery fails.
     */
    public static function passwordRecover(Request $request, string $token): JsonResponse|RedirectResponse
    {
        try {
            $caronte_response = HTTP::withOptions(
                [
                    'verify' => !config('caronte.ALLOW_HTTP_REQUESTS')
                ]
            )->post(
                url: config('caronte.URL') . 'api/' . config('caronte.VERSION') . '/password/recover/' . $token,
                data: [
                    'password'              => $request->password,
                    'password_confirmation' => $request->password
                ]
            );

            if ($caronte_response->failed()) {
                throw new RequestException($caronte_response);
            }

            $response = $caronte_response->body();
        } catch (RequestException | Exception $e) {
            throw new UnauthorizedException(
                message: $e->getMessage(),
                previous: $e
            );
        }

        return ResponseHelper::success(
            message: 'Password recovered successfully',
            data: $response,
            forward_url: config('caronte.LOGIN_URL')
        );
    }

    /**
     * Log out the user and clear the token.
     *
     * @param bool $logout_all_sessions Whether to log out from all sessions (default: false).
     * @return JsonResponse|RedirectResponse API response or redirect response.
     * @throws BadRequestException If logout fails.
     */
    public static function logout(bool $logout_all_sessions = false): JsonResponse|RedirectResponse
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
                url: config('caronte.URL') . 'api/' . config('caronte.VERSION') . '/logout' . ($logout_all_sessions ? 'All' : '')
            );

            if ($caronte_response->failed()) {
                throw new RequestException($caronte_response);
            }

            $response = $caronte_response->body();
        } catch (RequestException | Exception $e) {
            throw new BadRequestException(
                message: $e->getMessage(),
                previous: $e
            );
        }

        Caronte::clearToken();

        return ResponseHelper::success(
            message: 'Logout successful',
            data: $response,
            forward_url: config('caronte.LOGIN_URL')
        );
    }

    /**
     * Notify the Caronte server of the current client configuration and roles.
     *
     * @return string Response body from the Caronte server.
     * @throws RequestException If the request fails.
     */
    public static function notifyClientConfiguration(): string
    {
        $caronte_response = HTTP::withOptions(
            [
                'verify' => !config('caronte.ALLOW_HTTP_REQUESTS')
            ]
        )->withHeaders(
            [
                'Authorization' => "Bearer " . base64_encode(sha1(config('caronte.APP_ID')) . ':' . config('caronte.APP_SECRET'))
            ]
        )->post(
            url: config('caronte.URL') . 'api/A3/' . config('caronte.VERSION') . '/client-configuration',
            data: config('caronte-roles')
        );

        if ($caronte_response->failed()) {
            throw new RequestException($caronte_response);
        }

        return $caronte_response->body();
    }
}
