This Laravel monolith manages dependencies through two independent registries, each with its own manifest and lockfile:

- PHP (Composer): `composer.json` declares runtime (`require`) and development (`require-dev`) packages. The project pins the PHP platform to `8.3.26` via `config.platform`, sets `minimum-stability: stable` with `prefer-stable`, sorts packages, installs from `dist`, and allows a small set of Composer plugins (`pestphp/pest-plugin`, `php-http/discovery`). A `composer.lock` is committed, so CI and teammates resolve an identical tree. No private repository or `repositories` entry is configured — all packages come from Packagist.

- JavaScript (npm): `package.json` defines build/dev tooling (Vite, Tailwind, Alpine) under `devDependencies` plus one runtime dep (`alpinejs`). `package-lock.json` (lockfile v3) is committed, pinning every transitive resolution. There is no `.npmrc` custom registry; the default public npm registry is used.

Key conventions and scripts
- `composer.json` scripts orchestrate setup (`setup` runs install, env copy, keygen, migration, then `npm install --ignore-scripts` + `npm run build`), local dev (`dev` concurrently starts server, queue listener, Pail logs, and Vite), and tests (`test` clears config then runs PHPUnit).
- Autoloading follows PSR-4 for `App\`, `Database\Factories\`, `Database\Seeders\`, and `Tests\` in autoload-dev.
- Post-update hooks publish assets and discover service providers automatically.
- Development-only tools are isolated in `require-dev` (Breeze, Pail, Pint, Mockery, Collision, PHPUnit); production `require` contains only framework + business integrations (Spatie permission/activitylog, DomPDF, Maatwebsite Excel).

No vendoring strategy is used beyond Composer's standard `vendor/` directory and npm's `node_modules/`; neither is checked into version control. There is no evidence of private registries, proxy mirrors, or `GOPRIVATE`-style overrides.