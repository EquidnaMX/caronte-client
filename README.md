# Caronte Client v1.3.2

A robust PHP library for JWT-based authentication and permission management, purpose-built for Laravel applications. Caronte Client streamlines secure user authentication, role-based access control, and seamless integration with modern Laravel projects.

> **Note:** Caronte Client supports Laravel 10.x, 11.x, and 12.x. PHP 8.0 or higher is required.

## Coding Standards

- Follows PSR-12 with a 250-character line limit
- Uses 4-space indentation, LF line endings, and a final newline
- StyleCI uses the Laravel preset (see `.gitattributes` and `ruleset.xml`)
- Classes use StudlyCase; methods and variables use camelCase
- Functions are documented with PHPDoc and type hints

## Blade Message Handling

The package supports displaying messages in views using both session variables and base64-encoded request parameters:

- Session keys: `error`, `warning`, `success`, `info`, `message`
- Request parameters: `_err`, `_war`, `_suc` (base64-encoded)

Example usage in Blade:

```blade
@if (app('request')->input('_err'))
    <div class="alert alert-danger">{{ base64_decode(app('request')->input('_err')) }}</div>
@endif
```

This allows for flexible error and success message display after redirects or API calls.

## Error Handling & Message Display

Error and success messages are displayed in views using session variables and base64-encoded request parameters. See Blade Message Handling above for details. Always validate and sanitize user input before displaying.

---

## Main Use Cases

- Secure user authentication using JWT tokens
- Fine-grained permission and role management
- Middleware for session and role validation in Laravel routes
- Helper utilities for user and permission logic
- Facade for easy access to authentication features
- Artisan commands for configuration and environment sync
- Publishing of config, views, assets, and migrations for customization

---

## Installation

Install Caronte Client via Composer:

```bash
composer require equidna/caronte-client
```

---

## Usage Examples

### Authenticating Users

```php
use Caronte;

// Retrieve the current JWT token
$token = Caronte::getToken();

// Get the authenticated user object from the token
$user = Caronte::getUser();
```

### Middleware Integration

Add Caronte middleware to your routes for session and role validation:

```php
// In your routes/web.php or routes/api.php

Route::middleware(['Caronte.ValidateSession'])->group(function () {
    Route::get('/dashboard', function () {
        // Only accessible to authenticated users
    });
});

Route::middleware(['Caronte.ValidateRoles:administrator,manager'])->group(function () {
    Route::get('/admin', function () {
        // Only accessible to users with administrator or manager roles (or root)
    });
});
```

### Permission Checks in Code

```php
use Equidna\Caronte\Helpers\PermissionHelper;

// Check if the user has access to the application
if (PermissionHelper::hasApplication()) {
    // User has access
}

// Check if the user has a specific role
if (PermissionHelper::hasRoles(['administrator', 'editor'])) {
    // User has one of the required roles
}
```

---

## Technical Overview

Caronte Client is architected for seamless integration with Laravel applications:

- **JWT Authentication:** Securely validates and manages user sessions using JWT tokens.
- **Middleware:** `ValidateSession` and `ValidateRoles` ensure authentication and role-based access at the route level.
- **Helpers:** Utility classes for user and permission management.
- **Facade:** The `Caronte` facade provides a simple API for token and user operations.
- **Configurable:** Easily publish and customize configuration, views, assets, and migrations.
- **Artisan Commands:** Sync roles and configuration with the Caronte server.

[!TIP]
All permission checks automatically include the `root` role for maximum flexibility.

---

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

---

## Role Configuration

To define roles, edit `src/config/caronte-roles.php`. After making changes, notify the Caronte server:

```bash
php artisan caronte:notify-client-configuration
```

---

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

---

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

---

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

---

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

---

## Artisan Commands

- `caronte:notify-client-configuration` — Sync roles with Caronte server

---

## Publishing & Customization

You can publish config, views, assets, and migrations to customize the package for your app. See above for commands.

---

## Upgrading

If upgrading to Laravel 12, update dependencies and review changelogs. Caronte Client is tested and compatible. Use the version helper for future BC logic.

---

## Support & Issues

For issues, see the GitHub repository or open a new issue. Always check the changelog and documentation for updates.
