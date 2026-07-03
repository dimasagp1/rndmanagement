This repository is a Laravel 13 monolith with no containerization, CI/CD pipeline, or Makefile. The build system consists of two parallel toolchains: Composer for PHP dependency management and Vite (via the Laravel Vite plugin) for frontend asset compilation.

**PHP / Composer layer**
- `composer.json` declares PHP ^8.3 and Laravel ^13.8 as runtime requirements, plus Spatie permission/activitylog, Maatwebsite Excel, and Barryvdh Dompdf.
- Development tooling pinned in `require-dev`: Laravel Breeze, Pail (log tailer), Pao (API docs), Pint (linter), Mockery, PHPUnit ^12.5, Collision (pretty failures).
- Autoloading follows PSR-4: `App\`, `Database\Factories\`, `Database\Seeders\`, and `Tests\`.
- Composer scripts provide the local workflow:
  - `composer setup` — install deps, copy `.env.example`, generate app key, run migrations, install npm packages, and build assets.
  - `composer dev` — runs four concurrent processes via `concurrently`: `php artisan serve`, `php artisan queue:listen --tries=1 --timeout=0`, `php artisan pail --timeout=0`, and `npm run dev`.
  - `composer test` — clears config then delegates to `php artisan test`.
  - Post-hooks publish vendor assets (`post-update-cmd`) and discover packages (`post-autoload-dump`).
- Platform pinning in `config.platform.php = 8.3.26` ensures reproducible builds across machines.
- `composer.lock` and `package-lock.json` are committed, so installs are deterministic.

**Frontend / Vite layer**
- `package.json` defines only `build` (`vite build`) and `dev` (`vite`) scripts; all tooling lives under `devDependencies` (Tailwind v3, Tailwind Forms, Alpine.js, PostCSS, Autoprefixer, laravel-vite-plugin, concurrently).
- `vite.config.js` registers the Laravel Vite plugin with two entry points: `resources/css/app.css` and `resources/js/app.js`. Hot Module Replacement is enabled via `refresh: true`.
- Compiled assets land in `public/build/` (gitignored by default). The Blade layouts reference these entries through Laravel's `@vite()` directive (injected by the plugin).
- `tailwind.config.js` and `postcss.config.js` configure the CSS pipeline; `resources/css/app.css` imports Tailwind directives and any custom styles.

**Testing**
- `phpunit.xml` defines Unit and Feature suites under `tests/Unit` and `tests/Feature`, includes `app/` for source coverage, and overrides environment variables for testing (SQLite in-memory DB, array cache/session/mail, sync queue, disabled Telescope/Pulse/Nightwatch).
- Tests are invoked via `composer test` → `php artisan test`; no separate test runner script exists.

**What is NOT present**
- No Dockerfile, docker-compose, or container orchestration files.
- No CI configuration (no `.github/workflows`, Bitbucket Pipelines, Azure DevOps, Jenkinsfile, etc.).
- No Makefile or shell-based build scripts beyond the Windows `art.bat` / `artisan.bat` wrappers around the `artisan` binary.
- No release packaging, version tagging, or artifact publishing steps.
- No cross-compilation targets; everything assumes a Unix-like CLI with Node 22+ and PHP 8.3.