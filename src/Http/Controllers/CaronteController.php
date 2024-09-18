<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.0.0
 */

namespace Equidna\Caronte\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Equidna\Caronte\CaronteRequest;

class CaronteController extends Controller
{
    /**
     * Displays the login form.
     *
     * @param \Illuminate\Http\Request $request The HTTP request object.
     * @return \Illuminate\Contracts\View\View The login form view.
     */
    public function loginForm(Request $request): View
    {
        $login_view = config('caronte.USE_2FA') ? '2fa_login' : 'login';

        return View('caronte::' . $login_view)
            ->with(
                [
                    'callback_url' => $request->callback_url,
                    'showFilters' =>  'onlyTitle',
                ]
            );
    }

    /**
     * Logs in the user.
     *
     * @param Request $request The HTTP request object.
     *
     * @return Response|RedirectResponse The response object or a redirect response.
     */
    public function login(Request $request): Response|RedirectResponse
    {
        if (config('caronte.USE_2FA')) {
            return CaronteRequest::twoFactorTokenRequest(request: $request);
        }

        return CaronteRequest::userPasswordLogin(request: $request);
    }

    /**
     * Logs in the user using a two-factor authentication token.
     *
     * @param \Illuminate\Http\Request $request The HTTP request object.
     * @param string $token The two-factor authentication token.
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse The HTTP response or a redirect response.
     */
    public function twoFactorTokenLogin(Request $request, string $token): Response|RedirectResponse
    {
        return CaronteRequest::twoFactorTokenLogin(request: $request, token: $token);
    }

    /**
     * Logout the user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request): Response|RedirectResponse
    {
        return CaronteRequest::logout(
            request: $request,
            logout_all_sessions: $request->filled('all')
        );
    }

    /**
     * Display the password recovery request form.
     *
     * @param \Illuminate\Http\Request $request The HTTP request instance.
     * @return \Illuminate\View\View The view for the password recovery request form.
     */
    public function passwordRecoverRequestForm(Request $request): View
    {
        return view('caronte::password-recover-request');
    }

    /**
     * Handles the password recovery request.
     *
     * @param Request $request The HTTP request object containing the password recovery details.
     * @return Response|RedirectResponse The response object or a redirect response.
     */
    public function passwordRecoverRequest(Request $request): Response|RedirectResponse
    {
        return CaronteRequest::passwordRecoverRequest(request: $request);
    }

    public function passwordRecoverTokenValidation(Request $request, string $token): Response|View
    {
        return CaronteRequest::passwordRecoverTokenValidation(request: $request, token: $token);
    }

    public function passwordRecover(Request $request, string $token): Response|RedirectResponse
    {
        return CaronteRequest::passwordRecover(request: $request, token: $token);
    }
}
