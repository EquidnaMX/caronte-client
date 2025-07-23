<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.0.0
 */

namespace Equidna\Caronte\Facades;

use Illuminate\Support\Facades\Facade;
use Equidna\Caronte\Caronte as CaronteClass;

/**
 * Facade for the Caronte client, providing static access to authentication and user methods.
 *
 * @method static \Lcobucci\JWT\Token\Plain getToken()
 * @method static \stdClass getUser()
 * @method static string getRouteUser()
 * @method static void saveToken(string $token_str)
 * @method static void clearToken()
 * @method static void setTokenWasExchanged()
 * @method static bool tokenWasExchanged()
 * @method static string echo(string $message)
 * @method static void updateUserData(\stdClass|string $user)
 *
 * @see Equidna\Caronte\Caronte
 */
class Caronte extends Facade
{
    /**
     * Get the accessor for the facade.
     *
     * @return string The class name of the accessor.
     */
    protected static function getFacadeAccessor(): string
    {
        return CaronteClass::class;
    }
}
