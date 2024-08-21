<?php

namespace Gruelas\Caronte\Tools;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ResponseHelper
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Returns a bad request response.
     *
     * @param Request $request The HTTP request object.
     * @param string $message The error message.
     * @param string|null $forward_url The URL to redirect to.
     * @return Response|RedirectResponse The response object.
     */
    public static function badRequest(Request $request, string $message, string $forward_url = null): Response|RedirectResponse
    {
        if (RouteHelper::isAPI(request: $request) || RouteHelper::isHook(request: $request)) {
            return response($message, 400);
        }

        if (is_null($forward_url)) {
            return back()->withErrors([$message])->withInput();
        }

        return redirect($forward_url)->withErrors([$message])->withInput();
    }
}
