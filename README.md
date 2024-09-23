# Caronte client

---

Caronte is a JWT token based authentication system written in Laravel.
This client consumes the Caronte API to authenticate other systems and provides two middlewares for validating user acces.

## Configuration

Caronte uses environment variables to configure itself, these are the options that you can configure.

| Key                           | Default    | Description                                                                                                                                                               | V1        | V2           |
| ----------------------------- | ---------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------- | ------------ |
| `CARONTE_URL`                 | `''`       | The FQDN of the Caronte server against which the client will authenticate.                                                                                                | Mandatory | Mandatory    |
| `CARONTE_VERSION`             | `'v2'`     | Caronte auth version.                                                                                                                                                     | Mandatory | Optional     |
| `CARONTE_TOKEN_KEY`           | `''`       | Symmetric authentication key.                                                                                                                                             | Mandatory | Not required |
| `CARONTE_ALLOW_HTTP_REQUESTS` | `false`    | Disable https protocol verification in requests.                                                                                                                          | Optional  | Optional     |
| `CARONTE_ISSUER_ID`           | ''         |                                                                                                                                                                           |           |              |
| `CARONTE_ENFORCE_ISSUER`      | `true`     |                                                                                                                                                                           |           |              |
| `CARONTE_APP_ID`              | `''`       | Common name registered in Caronte.                                                                                                                                        | Mandatory | Mandatory    |
| `CARONTE_APP_SECRET`          | `''`       | String that identifies a system registered in Caronte.                                                                                                                    | Mandatory | Mandatory    |
| `CARONTE_2FA`                 | `false`    | Enable two-factor authentication.                                                                                                                                         | Optional  | Optional     |
| `CARONTE_ROUTES_PREFIX`       | `''`       | Prefix of the routes protected by Caronte. e.g. '/admin'.                                                                                                                 | Optional  | Optional     |
| `CARONTE_SUCCESS_URL`         | `'/'`      | Route to which a user will be redirected upon completing authentication.                                                                                                  | Optional  | Optional     |
| `CARONTE_LOGIN_URL`           | `'/login'` | Assigned route for login.                                                                                                                                                 | Optional  | Optional     |
| `CARONTE_UPDATE_USER`         | `false`    | When enabled, a record of users who have logged into the system is kept in the local database. (It is necessary to run the caronte-client migrations to use this feature) | Optional  | Optional     |

---

## Routes

| Method   | Route                    | Name                     | Description                                                       |
| -------- | ------------------------ | ------------------------ | ----------------------------------------------------------------- |
| GET      | login                    | caronte.login            | Returns the login or 2FA view depending on previous configuration |
| POST     | login                    |                          | email/password authentication endpoint                            |
| POST     | 2fa                      |                          | 2fa authentication request endpoint                               |
| GET      | 2fa/{token}              |                          | 2fa validation endpoint                                           |
| GET      | password/recover         | caronte.password.recover | Returns the view for starting pasword recovery procedure          |
| POST     | password/recover         |                          | Pasword recovery endpoint                                         |
| GET      | password/recover/{token} |                          | Returns the view to enter a new password if token is valid        |
| POST     | password/recover/{token} |                          | Updates user password if token is valid                           |
| GET/POST | logout                   |                          | Logouts the user and clear token cookie                           |
| GET      | get-token                | caronte.token.get        | Returns the current user token                                    |

---

## Middleware

### ValidateSession

**Main Class:** Equidna\Caronte\Http\Middleware\ValidateSession
**Alias:** Caronte.ValidateSession

Validates that the user is authenticated with a valid JWT token and has _any_ permission associated with the CARONTE_APP_ID provided in configuration

> Route::put('/dashboard',
> function (string $id) {
> // ...
> })->middleware('Caronte.ValidateSession');

Token is automatically renewed during the validation process if it has expired, in this case the new token will be available on a response header named **_new_token_**

---

### ValidateRoles

**Main Class:** Equidna\Caronte\Http\Middleware\ValidateRoles
**Alias:** Caronte.ValidateRoles
**Parameters:** a comma separated list or array of permissions to validate

Validates that the user has **_any_** of the provided roles. _root_ role is allways added to the list, therefore a user with the root role will _allways_ be considered valid.

> Route::put('/users',
> function (string $id) {
> // ...
> })->middleware('Caronte.ValidateRoles:administrator,manager');

---

## Helpers

This package provides helper classes for simplifying some common actions with users, all methods are designed to be called statically

**Equidna\Caronte\Helpers\CaronteUserHelper**

_getUserName(string $uri_user):**string**_
Returns the name of the user with the asociated uri

> _getUserEmail(string $uri_user):**string**_
> Returns the email of the user with the asociated uri

> _getUserMetadata(string $uri_user, string $key):**string**_
> Returns metadata valuie for the provided key and uri_user

**Equidna\Caronte\Helpers\PermissionHelper**

> _hasApplication():**bool**_
> Validates if current user has any role asociated with the **CARONTE_APP_ID**
>
> _hasRoles(mixed $roles):**bool**_
> Validates if the current user has any of the provided roles **$roles** can be provided as a comma separated list of values or an array
> **root** role is always added to the list therefore an user with the root role will allways return true.

---

## Facades

**Equidna\Caronte**

> _getToken():**Plain**_
> Returns a Lcobucci\JWT\Token\Plain representing the token in use

> _getUser():**stdClass|null**_
> Returns a stdClass representing the user for whom the token was isued or null if any error was found while decoding

> _getRouteUser():**string**_
> Returns the {uri_user} part of the route or an empty string of no {uri_user} is found on the route

> _saveToken(string $token_str):**void**_
> @param $token*string
> stores the provided token string on the *tokens* folder of the server and saves the file id on a cookie named *caronte_token\*

> _clearToken():**void**_
> Clears the _caronte_token_ cookie and deletes the asociated file storing the user token

> _setTokenWasExchanged():**void**_
> Raises a flag indicating that the token had expired and was exchanged during the validation process

> _tokenWasExchanged():**bool**_
> Return true if the token was exchanged during the validation process

> _echo(string $message):**string**_
> Returns the provided message
