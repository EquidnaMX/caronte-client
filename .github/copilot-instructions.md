# Copilot Coding Agent Instructions for Caronte Client

## Project Overview

**Caronte Client** is a PHP library for JWT-based authentication and permission management, designed for Laravel applications.

- Main namespace: `Equidna\Caronte`. Facade is available as `Caronte`.
- Core logic is in `src/` (see `Caronte.php`, `Http/Controllers/`, `Http/Middleware/`, `Helpers/`, `Models/`).
- Configuration files are in `src/config/`.
- Views and assets are in `src/resources/`.

## Key Workflows

- **Install:** `composer require equidna/caronte-client`
- **Publish config/roles:** `php artisan vendor:publish --tag=caronte:roles`
- **Publish views/assets/migrations:** Use appropriate `php artisan vendor:publish --tag=caronte:*` commands.
- **Migrate DB:** `php artisan migrate` (required for user features).
- **Notify Caronte server of role changes:** `php artisan caronte:notify-client-configuration` (after editing `caronte-roles.php`).

## Configuration

- Uses `.env` for settings like `CARONTE_URL`, `CARONTE_APP_ID`, `CARONTE_APP_SECRET`, etc.
- See `README.md` for full list of environment variables and their meanings.

## Routing & Middleware

- Routes are defined in `src/routes/web.php`.
- Two main middlewares:
  - `ValidateSession`: Ensures user is authenticated and token is valid. Token is auto-renewed if expired.
  - `ValidateRoles`: Checks user roles (always includes `root`).
- Example: `->middleware('Caronte.ValidateSession')` or `->middleware('Caronte.ValidateRoles:administrator,manager')`

## Helpers & Facades

- Use static methods in `Helpers/CaronteUserHelper.php` and `Helpers/PermissionHelper.php` for user and permission logic.
- Facade methods (see `Facades/Caronte.php`) provide access to token, user info, and token management.

## Conventions & Patterns

- All permission checks add `root` role automatically.
- User metadata and roles are managed via helpers and config files.
- Token renewal is handled transparently in middleware.
- Customization via publishing views/assets/config to app folders.

## External Dependencies

- Relies on Laravel (`illuminate/support`, `laravel/framework`), JWT (`lcobucci/jwt`), and Equidna Toolkit.

## Example Files

- `src/Caronte.php`: Main client logic.
- `src/Http/Middleware/ValidateSession.php`, `ValidateRoles.php`: Auth middleware.
- `src/Helpers/CaronteUserHelper.php`, `PermissionHelper.php`: Helper utilities.
- `src/config/caronte-roles.php`: Role definitions.
- `src/routes/web.php`: Route definitions.

---

**For updates:** Always check `README.md` and config files for new workflows or conventions.

## Laravel Compatibility & Migration Notes

- This package is compatible with Laravel 10.x and 11.x. For future Laravel versions (12.x+), check for breaking changes in Eloquent, ServiceProvider, and Facade APIs.
- Migrations now use the `InnoDB` engine for better compatibility and reliability. Avoid using `MyISAM`.
- Eloquent models must extend `Illuminate\Database\Eloquent\Model` and be properly autoloaded via Composer. If you see 'Undefined method where', check your autoload and namespace setup.
- For string primary keys, use `Model::where('pk', $value)->firstOrFail()` for compatibility across Laravel versions.
- If you encounter issues with static methods like `where`, ensure your model is correctly extending the Eloquent base class and autoloaded.
