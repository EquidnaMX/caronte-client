<?php

namespace Gruelas\Caronte;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cookie;
use Lcobucci\JWT\Token\Plain;
use Gruelas\Caronte\Tools\RouteHelper;
use Exception;
use stdClass;

class Caronte
{
    public const COOKIE_NAME = 'caronte_token';

    public function __construct()
    {
        //
    }

    public function getToken(): Plain
    {
        $token_str = RouteHelper::isAPI() ? request()->bearerToken() : $this->webToken();

        if (is_null($token_str) || empty($token_str)) {
            throw new Exception('Token not found', 401);
        }

        return CaronteToken::decodeToken(raw_token: $token_str);
    }

    public function getUser(): stdClass
    {
        return json_decode($this->getToken()->claims()->get('user'));
    }

    /**
     * Get the URI user from the current route.
     *
     * @return string The URI user string.
     */
    public static function getRouteUser(): string
    {
        return request()->route('uri_user') ?: '';
    }

    public function saveToken(string $token_str): void
    {
        $token_id = Str::random(20);

        Cookie::queue(Cookie::forever(static::COOKIE_NAME, $token_id));
        Storage::disk('local')->put('tokens/' . $token_id, $token_str);
    }

    public function clearToken(): void
    {
        $this->forgetCookie();
    }

    public function echo(string $message): string
    {
        return $message;
    }


    /**
     * Get the web token from storage.
     *
     * @return null|string
     */
    private function webToken(): null|string
    {
        if (Storage::disk('local')->exists('tokens/' . Cookie::get(static::COOKIE_NAME))) {
            return Storage::disk('local')->get('tokens/' . Cookie::get(static::COOKIE_NAME));
        }

        return "";
    }

    private function forgetCookie(): void
    {
        if (Storage::disk('local')->exists('tokens/' . Cookie::get(static::COOKIE_NAME))) {
            Storage::disk('local')->delete('tokens/' . Cookie::get(static::COOKIE_NAME));
        }

        Cookie::queue(Cookie::forget(static::COOKIE_NAME));
    }
}
