<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.0.0
 *
 */

namespace Equidna\Caronte\Helpers;

use Equidna\Caronte\Models\CaronteUser;
use Equidna\Caronte\Models\CaronteUserMetadata;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CaronteUserHelper
{
    public function __construct()
    {
        //
    }

    /**
     * Retrieves the name of a user based on the provided URI.
     *
     * @param string $uri_user The URI of the user.
     * @return string The name of the user.
     */
    public static function getUserName(string $uri_user): string
    {
        try {
            $user = CaronteUser::firstOrFail($uri_user);
        } catch (ModelNotFoundException $e) {
            return 'User not found';
        }

        return $user->name;
    }

    /**
     * Retrieves the email of a user based on the provided URI.
     *
     * @param string $uri_user The URI of the user.
     * @return string The email of the user.
     */
    public static function getUserEmail(string $uri_user): string
    {
        try {
            $user = CaronteUser::firstOrFail($uri_user);
        } catch (ModelNotFoundException $e) {
            return 'User not found';
        }

        return $user->email;
    }

    /**
     * Retrieves the metadata value for a specific user and key.
     *
     * @param string $uri_user The URI of the user.
     * @param string $key The key of the metadata.
     * @return string|null The value of the metadata, or null if not found.
     */
    public static function getUserMetadata(string $uri_user, string $key): string|null
    {
        try {
            $data = CaronteUserMetadata::where('uri_user', $uri_user)->where('key', $key)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return null;
        }

        return $data->value;
    }
}
