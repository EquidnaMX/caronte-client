<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.3.2
 *
 */

namespace Equidna\Caronte\Helpers;

use Equidna\Caronte\Models\CaronteUser;
use Equidna\Caronte\Models\CaronteUserMetadata;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CaronteUserHelper
{
    private function __construct()
    {
        //
    }

    /**
     * Get the user's name by URI.
     *
     * @param string $uri_user User URI.
     * @return string User name, or 'User not found' if not found.
     */
    public static function getUserName(string $uri_user): string
    {
        try {
            $user = CaronteUser::where('uri_user', $uri_user)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return 'User not found';
        }

        return $user->name;
    }

    /**
     * Get the user's email by URI.
     *
     * @param string $uri_user User URI.
     * @return string User email, or 'User not found' if not found.
     */
    public static function getUserEmail(string $uri_user): string
    {
        try {
            $user = CaronteUser::where('uri_user', $uri_user)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return 'User not found';
        }

        return $user->email;
    }

    /**
     * Get a user's metadata value by URI and key.
     *
     * @param string $uri_user User URI.
     * @param string $key Metadata key.
     * @return string|null Metadata value, or null if not found.
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
