<?php

namespace Gruelas\Caronte;

use stdClass;
use Exception;

class Caronte
{
    public function __construct()
    {
        //
    }


    

    /**
     * Get the user object from the request.
     *
     * @return stdClass The user object decoded from JSON or directly if already an object.
     * @throws Exception If no user data is provided.
     */
    public static function getUser(): stdClass
    {
        $user = request()->get('user');

        if (is_null($user)) {
            throw new Exception('No user provided');
        }

        return is_string($user) ? json_decode($user) : $user;
    }

    /**
     * Get the hashed URI application identifier.
     *
     * @param string|null $application_id Optional application ID to hash.
     * @return string The hashed URI application identifier.
     */
    public static function getUriApplication(string $application_id = null): string
    {
        return sha1($application_id ?? config('caronte.APP_ID'));
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
     * Get the login fail URL with callback.
     *
     * @return string The login fail URL with encoded callback URL.
     */
    public static function getFailURL(): string
    {
        return config('caronte.LOGIN_URL') . '?callback_url=' . base64_encode(request()->url());
    }

    /**
     * Handle a failed response, either for API or web requests.
     *
     * @param string $apiMessage The message for API response. Default is an empty string
     * @param int $statusCode The HTTP status code. Default is 400
     * @return void
     * @throws HttpResponseException
     */
    public static function handleFailedResponse(string $message = '', int $statusCode = 400): void
    {
        if (CaronteHelper::isAPI()) {
            throw new HttpResponseException(response($message, $statusCode));
        }

        throw new HttpResponseException(back()->with('error', $message));
    }

    /**
     * Get user metadata by key.
     *
     * @param stdClass $user
     * @param string $key
     * @return mixed
     */
    public static function getUserMetadata(stdClass $user, string $key): mixed
    {
        return $user->metadata->pluck('value', 'key')->get($key);
    }

    /**
     * Check if the user has specified roles.
     *
     * @param mixed $roles
     * @return bool
     */
    public static function userHasRoles(mixed $roles): bool
    {
        return CarontePermissionValidator::hasRoles(roles: $roles);
    }

    /**
     * Get the web token from storage.
     *
     * @return null|string
     */
    public static function webToken(): null|string
    {
        if (Storage::disk('local')->exists('tokens/' . Cookie::get(CaronteToken::COOKIE_NAME))) {
            return Storage::disk('local')->get('tokens/' . Cookie::get(CaronteToken::COOKIE_NAME));
        }

        return "";
    }
}
