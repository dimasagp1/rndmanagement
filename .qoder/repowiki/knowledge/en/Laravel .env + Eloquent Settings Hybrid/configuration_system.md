The application uses a two-tier configuration approach built on Laravel's standard runtime configuration system, augmented by a database-backed settings store for per-tenant/runtime values.

**Runtime configuration (framework-level)**
- All framework and environment-specific options live in `config/*.php` files (`app.php`, `auth.php`, `cache.php`, `database.php`, `filesystems.php`, `logging.php`, `mail.php`, `permission.php`, `queue.php`, `services.php`, `session.php`).
- Values are resolved via `env('KEY', default)` calls inside those files; the authoritative source of truth is the `.env` file (with `.env.example` as the template).
- No custom config loader or third-party package is used — this is vanilla Laravel configuration with no overrides, merging, or dynamic reload at runtime.

**Application settings (runtime data)**
- Global application identity (app name, company name, logo, favicon) is persisted in a `settings` table created by migration `2026_07_02_000000_create_settings_table.php`. The table has a simple key/value schema (`key` unique string, `value` text nullable).
- Access is provided through a global helper function `setting($key, $default = null)` defined in `app/Helpers/setting.php` and autoloaded from `AppServiceProvider::register()` via `require_once app_path('Helpers/setting.php')`. The helper queries `App\Models\Setting` and returns the stored value or the supplied default; it swallows exceptions so missing rows never crash views.
- Write access goes through `SettingController`, which validates incoming form fields, persists text settings via `updateOrCreate(['key' => ...], ['value' => ...])`, and uploads logo/favicon to `storage/app/public/settings` using Laravel Storage, replacing any previously stored path. Only users with the `Superadmin` role may reach these endpoints.

**Layering and conventions**
- Static, deploy-time configuration stays in `config/*.php` → `.env` (no code changes needed to switch environments).
- Dynamic, user-editable configuration lives in the `settings` table and is read through the `setting()` helper everywhere in Blade/views.
- There is no caching layer around the settings table reads, so every request that calls `setting()` incurs a DB query; no cache invalidation strategy is implemented when settings are updated.
- File-based assets (logo, favicon) are referenced by their storage-relative path stored in the settings table rather than embedded content.