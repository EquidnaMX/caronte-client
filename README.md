# WARNING UPGRADE FROM 1.1 TO 1.2 OR 1.3 MAY BREAK YOUR APPLICATION DUE TO NAMESPACE CHANGE FOR FACADE Equidna\Caronte to Caronte

**Caronte Client** (v1.3) is a PHP library for JWT-based authentication and permission management, designed for Laravel applications. It provides:

- User authentication via JWT tokens
- Permission and role management
- Middleware for session and role validation
- Helper utilities for user and permission logic
- Facade for easy access to authentication features
- Artisan commands for configuration sync
- Publishing of config, views, assets, and migrations

## Laravel Compatibility

Supports Laravel 10.x, 11.x, and 12.x. Composite primary keys are handled via a trait. See `src/Helpers/LaravelVersionHelper.php` for runtime version checks and future BC logic.

## Installation

Install via Composer:

```
composer require equidna/caronte-client
```

## Publishing Configuration, Views, Assets, and Migrations

Publish roles config:

```
php artisan vendor:publish --tag=caronte:roles
```

Publish views:

```
php artisan vendor:publish --tag=caronte:views
```

Publish migrations:

```
php artisan vendor:publish --tag=caronte:migrations
```

Publish assets:

```
php artisan vendor:publish --tag=caronte:assets
```

Publish everything:

```
php artisan vendor:publish --tag=caronte
```

## Database Migrations

Run migrations to create required tables:

```
php artisan migrate
```

Tables:

- **Users**: Stores user records
- **UsersMetadata**: Stores user metadata

## Configuration

Set environment variables in `.env`:

| Key                         | Default  | Description                                  |
| --------------------------- | -------- | -------------------------------------------- |
| CARONTE_URL                 | ''       | FQDN of Caronte server for authentication    |
| CARONTE_VERSION             | 'v2'     | Caronte auth version                         |
| CARONTE_TOKEN_KEY           | ''       | Symmetric authentication key                 |
| CARONTE_ALLOW_HTTP_REQUESTS | false    | Disable HTTPS protocol verification          |
| CARONTE_ISSUER_ID           | ''       | Issuer ID                                    |
| CARONTE_ENFORCE_ISSUER      | true     | Enforce issuer validation                    |
| CARONTE_APP_ID              | ''       | Registered app name                          |
| CARONTE_APP_SECRET          | ''       | Registered app secret                        |
| CARONTE_2FA                 | false    | Enable two-factor authentication             |
| CARONTE_ROUTES_PREFIX       | ''       | Prefix for protected routes                  |
| CARONTE_SUCCESS_URL         | '/'      | Redirect after authentication                |
| CARONTE_LOGIN_URL           | '/login' | Login route                                  |
| CARONTE_UPDATE_USER         | false    | Track users in local DB (requires migration) |

## Role Configuration

Edit `src/config/caronte-roles.php` to define roles. Notify Caronte server of changes:

```
php artisan caronte:notify-client-configuration
```

## Routes

Routes are defined in `src/routes/web.php`:

| Method   | Route                    | Name                     | Description                      |
| -------- | ------------------------ | ------------------------ | -------------------------------- |
| GET      | login                    | caronte.login            | Returns login/2FA view           |
| POST     | login                    |                          | Email/password authentication    |
| POST     | 2fa                      |                          | 2FA authentication request       |
| GET      | 2fa/{token}              |                          | 2FA validation endpoint          |
| GET      | password/recover         | caronte.password.recover | Password recovery view           |
| POST     | password/recover         |                          | Password recovery endpoint       |
| GET      | password/recover/{token} |                          | New password view if token valid |
| POST     | password/recover/{token} |                          | Update password if token valid   |
| GET/POST | logout                   | caronte.logout           | Logout and clear token           |
| GET      | get-token                | caronte.token.get        | Returns current user token       |

## Middleware

### ValidateSession

**Class:** `Equidna\Caronte\Http\Middleware\ValidateSession`
**Alias:** `Caronte.ValidateSession`

Validates user authentication and token validity. Token is auto-renewed if expired (see `_new_token_` response header).

### ValidateRoles

**Class:** `Equidna\Caronte\Http\Middleware\ValidateRoles`
**Alias:** `Caronte.ValidateRoles`
**Parameters:** Comma-separated list or array of roles

Validates user roles (always includes `root`).

## Helpers

### CaronteUserHelper

Static methods for user info:

- `getUserName(string $uri_user): string` — Get user name
- `getUserEmail(string $uri_user): string` — Get user email
- `getUserMetadata(string $uri_user, string $key): ?string` — Get user metadata

### PermissionHelper

Static methods for permissions:

- `hasApplication(): bool` — User has any role for current app
- `hasRoles(mixed $roles): bool` — User has any of provided roles (comma-separated or array; always includes `root`)

## Facade

### Caronte

Provides static access to authentication features:

- `getToken(): Plain` — Get current JWT token
- `getUser(): ?stdClass` — Get current user
- `getRouteUser(): string` — Get user from route
- `saveToken(string $token_str): void` — Store token
- `clearToken(): void` — Clear token
- `setTokenWasExchanged(): void` — Mark token as exchanged
- `tokenWasExchanged(): bool` — Was token exchanged?
- `echo(string $message): string` — Echo message
- `updateUserData(stdClass $user): void` — Update user data

## Artisan Commands

- `caronte:notify-client-configuration` — Sync roles with Caronte server

## Publishing & Customization

You can publish config, views, assets, and migrations to customize the package for your app. See above for commands.

## Upgrading

If upgrading to Laravel 12, update dependencies and review changelogs. Caronte Client is tested and compatible. Use the version helper for future BC logic.

## Support & Issues

For issues, see the GitHub repository or open a new issue. Always check the changelog and documentation for updates.
