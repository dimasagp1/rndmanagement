The R&D Management System uses a Tailwind CSS–driven, Blade-component frontend built on Laravel's default scaffolding. The stack is: Tailwind v3 with the `@tailwindcss/forms` plugin, Vite as the asset pipeline (via `laravel-vite-plugin`), PostCSS for autoprefixing, and Alpine.js for lightweight interactivity.

**Design tokens & theme** — All visual tokens live in `tailwind.config.js`. A custom palette centers on an herbal-green primary (`#2F6B3C`) with dark/light variants, a warm secondary brown, an accent gold, a light surface background, and a deep ink text color. Typography extends Inter (body) and Poppins (headings). Custom shadows (`card`, `card-hover`, `sidebar`) and two herbal gradients are defined. Three keyframe animations (`fadeIn`, `slideIn`, `pulseDot`) power subtle motion.

**CSS architecture** — `resources/css/app.css` imports Google Fonts then layers Tailwind directives into three sections:
- `@layer base`: global body font/scroll behavior, heading font override, and a thin green scrollbar.
- `@layer components`: reusable UI primitives — sidebar links, cards (with header/body), stat cards, buttons (`btn-primary`/`secondary`/`outline`/`ghost`/`danger`/`accent`, plus `sm`/`lg` sizes), form controls (`form-input`/`select`/`textarea`/`label`/`error`/`hint`/`group`/`section`), data tables, page headers, badges, alerts (success/warning/danger/info), approval stepper icons, flash messages, spinners, tabs, dividers, and avatars.
- `@layer utilities`: small helpers like `.text-balance`, `.bg-primary-8`, `.sidebar-gradient`, `.glass`, and a subtle SVG pattern overlay.

**Blade component layer** — Shared UI fragments live under `resources/views/components/` (e.g. `primary-button.blade.php`, `secondary-button.blade.php`, `input-label.blade.php`, `text-input.blade.php`, `modal.blade.php`, `dropdown.blade.php`, `status-badge.blade.php`, `audit-trail.blade.php`, `approval-timeline.blade.php`, `empty-state.blade.php`). Page-level layouts are split into `layouts/app.blade.php`, `layouts/guest.blade.php`, and `layouts/navigation.blade.php`, with feature folders (`formulas/`, `trial-pms/`, `trial-rms/`, `materials/`, `suppliers/`, `users/`, `profile/`, `settings/`, `approval-center/`, `auth/`) each holding their own Blade views that compose these shared components.

**JavaScript interactivity** — `resources/js/app.js` bootstraps Alpine.js globally; no other JS framework or build target exists beyond this single entry point consumed by Vite.

**Build pipeline** — `vite.config.js` registers the Laravel plugin with inputs `resources/css/app.css` and `resources/js/app.js`; `postcss.config.js` runs Tailwind + Autoprefixer; `package.json` exposes `npm run dev` / `npm run build` scripts.

**Conventions developers should follow**
- Style exclusively through Tailwind utility classes; extend the design system via `tailwind.config.js` tokens rather than ad-hoc CSS.
- Reuse the component-layer classes from `app.css` (`.card`, `.btn-*`, `.form-*`, `.data-table`, etc.) instead of re-declaring layout styles per view.
- Encapsulate repeated markup in Blade partials under `resources/views/components/` and keep feature-specific markup inside its feature folder.
- Use Alpine.js only for small client-side toggles/state; avoid heavy SPA patterns.
- Keep fonts, colors, shadows, and animations centralized in `tailwind.config.js` and `app.css` so new pages stay visually consistent.