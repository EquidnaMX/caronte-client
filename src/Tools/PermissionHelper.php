<?php

namespace Gruelas\Caronte\Tools;

use Exception;

class PermissionHelper
{
    /**
     * Create a new class instance.
     */
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

        if (is_null($user)) {
            throw new Exception('No user provided');
        }

        $roles = collect($user->roles);

        return $roles->contains(
            fn($role) => $role->uri_application === config('caronte.APP_ID')
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
        $user = Caronte::getUser();

        if (is_null($user)) {
            throw new Exception('No user provided');
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
            fn($user_role) => in_array($user_role->name, $roles) && config('caronte.APP_ID') === $user_role->uri_application
        );
    }
}
