<?php

namespace Equidna\Caronte;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cookie;
use Lcobucci\JWT\Token\Plain;
use Equidna\Caronte\Models\CaronteUser;
use Equidna\Toolkit\Helpers\RouteHelper;
use Exception;
use stdClass;

class Caronte
{
    public const COOKIE_NAME = 'caronte_token';
    private bool $new_token  = false;

    public function __construct()
    {
        //
    }

    /**
     * Retrieves the token.
     *
     * @return Plain The decoded token.
     * @throws Exception If the token is not found.
     */
    public function getToken(): Plain
    {
        $token_str = RouteHelper::isAPI() ? request()->bearerToken() : $this->webToken();

        if (is_null($token_str) || empty($token_str)) {
            throw new Exception('Token not found');
        }

        return CaronteToken::validateToken(raw_token: $token_str);
    }

    /**
     * Retrieves the user object from the token claims.
     *
     * @return stdClass The user object decoded from the token claims.
     */
    public function getUser(): stdClass|null
    {
        try {
            return json_decode($this->getToken()->claims()->get('user'));
        } catch (Exception $e) {
            return null;
        }
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

    /**
     * Saves the token string and associates it with a randomly generated token ID.
     *
     * @param string $token_str The token string to be saved.
     * @return void
     */
    public function saveToken(string $token_str): void
    {
        //If cookie doesn't have an existent token id value, we generate one new random string
        $token_id = Cookie::get(static::COOKIE_NAME) ?: Str::random(20);
        //Cookie gets queued with the token_id
        Cookie::queue(Cookie::forever(static::COOKIE_NAME, $token_id));

        //if an old token isstored we clear the file first
        if (Storage::disk('local')->exists('tokens/' . $token_id)) {
            Storage::disk('local')->delete('tokens/' . $token_id);
        }
        Storage::disk('local')->put('tokens/' . $token_id, $token_str);
    }

    /**
     * Clears the token.
     *
     * @return void
     */
    public function clearToken(): void
    {
        $this->forgetCookie();
    }

    /**
     * Echoes the given message.
     *
     * @param string $message The message to be echoed.
     * @return string The echoed message.
     */
    public function echo(string $message): string
    {
        return $message;
    }

    public function setTokenWasExchanged(): void
    {
        $this->new_token = true;
    }

    /**
     * Check if the token is new.
     *
     * @return bool Returns true if the token is new, false otherwise.
     */
    public function tokenWasExchanged(): bool
    {
        return $this->new_token;
    }

    /**
     * Get the web token from storage.
     *
     * @return null|string
     */
    private function webToken(): ?string
    {
        if (Storage::disk('local')->exists('tokens/' . Cookie::get(static::COOKIE_NAME))) {
            return Storage::disk('local')->get('tokens/' . Cookie::get(static::COOKIE_NAME));
        }

        return  null;
    }

    /**
     * Deletes the token cookie and removes the corresponding token file from the local storage.
     *
     * @return void
     */
    private function forgetCookie(): void
    {
        if (Storage::disk('local')->exists('tokens/' . Cookie::get(static::COOKIE_NAME))) {
            Storage::disk('local')->delete('tokens/' . Cookie::get(static::COOKIE_NAME));
        }

        Cookie::queue(Cookie::forget(static::COOKIE_NAME));
    }

    /**
     * Update local user data.
     *
     * @param stdClass $user The user object containing updated data.
     * @return void
     */
    public static function updateUserData(stdClass|string $user): void
    {
        if (is_string($user)) {
            $user = json_decode($user);
        }

        try {
            $local_user = CaronteUser::updateOrCreate(
                [
                    'uri_user' => $user->uri_user
                ],
                [
                    'name'  => $user->name,
                    'email' => $user->email
                ]
            );

            foreach ($user->metadata as $metadata) {
                $local_user->metadata()->updateOrCreate(
                    [
                        'uri_user'  => $user->uri_user,
                        'key'       => $metadata->key,
                    ],
                    [
                        'value'     => $metadata->value,
                        'scope'     => $metadata->scope ?: config('carone.APP_ID')
                    ]
                );
            }
        } catch (Exception $e) {
            // TODO log error
        }
    }
}
