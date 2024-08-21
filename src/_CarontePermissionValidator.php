<?php

namespace App\Classes\Caronte;

class CarontePermissionValidator
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Check if the user has any of the specified roles for a given application.
     *
     * @param mixed $roles Roles to check (comma-separated string or array).
     * @param string|null $application_id Optional application ID to use for URI generation.
     * @return bool True if the user has any of the specified roles, false otherwise.
     */
    public static function hasRoles(mixed $roles, string $application_id = null): bool
    {
        $uri_application = CaronteHelper::getUriApplication(application_id: $application_id);
        $user            = CaronteHelper::getUser();

        if (!is_array($roles))
            $roles = explode(",", $roles);

        $roles   = array_map('trim', $roles);
        $roles[] = 'root';  //* root role is always available

        if (in_array('_self', $roles) && CaronteHelper::getRouteUser() == $user->uri_user)
            return true;

        $roles_collection = collect($user->roles);

        return $roles_collection->contains(function ($user_role) use ($roles, $uri_application) {
            return in_array($user_role->name, $roles) && $uri_application == $user_role->uri_application;
        });
    }

    /**
     * Check if the user has any roles assigned for a specific application.
     *
     * @param string|null $application_id Optional application ID to use for URI generation.
     * @return bool True if the user has roles assigned for the application, false otherwise.
     */
    public static function hasApplication(string $application_id = null): bool
    {
        $uri_application = CaronteHelper::getUriApplication(application_id: $application_id);
        $user            = CaronteHelper::getUser();

        $roles = collect($user->roles);

        return $roles->contains(function ($role) use ($uri_application) {
            return $role->uri_application === $uri_application;
        });
    }
}
