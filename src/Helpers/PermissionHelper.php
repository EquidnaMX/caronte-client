<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.0.0
 *
 */

namespace Equidna\Caronte\Helpers;

use Equidna\Caronte\Facades\Caronte;
use Equidna\Toolkit\Exceptions\UnauthorizedException;

class PermissionHelper
{
    public function __construct()
    {
        //
    }

    /**
     * Check if the user has any roles assigned for a specific application.
     *
     * @return bool True if the user has roles assigned for the application, false otherwise.
     */
    public static function hasApplication(): bool
    {
        $user = Caronte::getUser();
        $app_id = sha1(config('caronte.APP_ID'));

        if (is_null($user)) {
            throw new UnauthorizedException('No user provided');
        }

        $roles = collect($user->roles);

        return $roles->contains(
            fn($role) => ($role->uri_application ?? $role->app_id) === $app_id
        );
    }

    /**
     * Check if the user has any of the specified roles for a given application.
     *
     * @param mixed $roles Roles to check (comma-separated string or array).
     * @return bool True if the user has any of the specified roles, false otherwise.
     */
    public static function hasRoles(mixed $roles): bool
    {
        $user   = Caronte::getUser();
        $app_id = sha1(config('caronte.APP_ID'));

        if (is_null($user)) {
            throw new UnauthorizedException('No user provided');
        }

        if (!is_array($roles)) {
            $roles = explode(",", $roles);
        }

        $roles   = array_map('trim', $roles);
        $roles[] = 'root';  //* root role is always available

        if (in_array('_self', $roles) && Caronte::getRouteUser() == $user->uri_user) {
            return true;
        }

        $roles_collection = collect($user->roles);

        return $roles_collection->contains(
            fn($user_role) => in_array($user_role->name, $roles) && ($app_id === ($user_role->uri_application ?? $user_role->app_id))
        );
    }
}
