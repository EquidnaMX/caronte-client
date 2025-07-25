<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.3.2
 */

namespace Equidna\Caronte\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Equidna\Caronte\CaronteRequest;
use Equidna\Caronte\Facades\Caronte;
use Exception;

class CaronteController extends Controller
{
    /**
     * Show the login form view.
     *
     * @param Request $request HTTP request object.
     * @return View Login form view.
     */
    public function loginForm(Request $request): View
    {
        $login_view = config('caronte.USE_2FA') ? '2fa-login' : 'login';

        try {
            dd('caronte::' . $login_view); // This line is for debugging purposes, remove it in production.
            return View('caronte::' . $login_view);/*
                ->with(
                    [
                        'callback_url' => $request->callback_url,
                        'showFilters' =>  'onlyTitle',
                    ]
                );*/
        } catch (Exception  $e) {
            dd($e->getMessage());
        }
    }

    /**
     * Handle the login request, using 2FA if enabled.
     *
     * @param Request $request HTTP request instance.
     * @return Response|RedirectResponse
     */
    public function login(Request $request): Response|RedirectResponse
    {
        if (config('caronte.USE_2FA')) {
            return CaronteRequest::twoFactorTokenRequest(request: $request);
        }

        return CaronteRequest::userPasswordLogin(request: $request);
    }

    /**
     * Log in the user using a two-factor authentication token.
     *
     * @param Request $request HTTP request object.
     * @param string $token Two-factor authentication token.
     * @return Response|RedirectResponse
     */
    public function twoFactorTokenLogin(Request $request, string $token): Response|RedirectResponse
    {
        return CaronteRequest::twoFactorTokenLogin(request: $request, token: $token);
    }

    /**
     * Show the password recovery request form.
     *
     * @return View Password recovery request form view.
     */
    public function passwordRecoverRequestForm(): View
    {
        return view('caronte::password-recover-request');
    }

    /**
     * Handle the password recovery request.
     *
     * @param Request $request HTTP request with recovery details.
     * @return Response|RedirectResponse
     */
    public function passwordRecoverRequest(Request $request): Response|RedirectResponse
    {
        return CaronteRequest::passwordRecoverRequest(request: $request);
    }

    /**
     * Validate the password recovery token.
     *
     * @param Request $request HTTP request instance.
     * @param string $token Password recovery token.
     * @return Response|RedirectResponse|View
     */
    public function passwordRecoverTokenValidation(Request $request, string $token): Response|RedirectResponse|View
    {
        return CaronteRequest::passwordRecoverTokenValidation(token: $token);
    }

    /**
     * Handle the password recovery process.
     *
     * @param Request $request HTTP request instance.
     * @param string $token Password recovery token.
     * @return Response|RedirectResponse
     */
    public function passwordRecover(Request $request, string $token): Response|RedirectResponse
    {
        return CaronteRequest::passwordRecover(request: $request, token: $token);
    }

    /**
     * Log out the user.
     *
     * @param Request $request HTTP request object.
     * @return Response|RedirectResponse
     */
    public function logout(Request $request): Response|RedirectResponse
    {
        return CaronteRequest::logout(logout_all_sessions: $request->filled('all'));
    }

    /**
     * Retrieve a token from the Caronte service.
     *
     * @return Response Response containing the token.
     */
    public function getToken(): Response
    {
        return Response(Caronte::getToken()->toString(), 200);
    }
}
