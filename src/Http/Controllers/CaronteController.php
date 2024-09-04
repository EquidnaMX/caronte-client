<?php

namespace Equidna\Caronte\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Equidna\Caronte\CaronteRequest;

class CaronteController extends Controller
{
    /**
     * Displays the login form.
     *
     * @param \Illuminate\Http\Request $request The HTTP request object.
     * @return \Illuminate\Contracts\View\View The login form view.
     */
    public function loginForm(Request $request)
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
     * @param \Illuminate\Http\Request $request The HTTP request object.
     *
     * @return mixed The response from the login process.
     */
    public function login(Request $request)
    {
        if (config('caronte.USE_2FA')) {
            return CaronteRequest::twoFactorTokenRequest(request: $request);
        }

        return CaronteRequest::userPasswordLogin(request: $request);
    }

    public function twoFactorTokenLogin(Request $request, $token)
    {
        return CaronteRequest::twoFactorTokenLogin(request: $request, token: $token);
    }

    public function logout(Request $request)
    {
        return CaronteRequest::logout(
            request: $request,
            logout_all_sessions: $request->filled('all')
        );
    }
}
