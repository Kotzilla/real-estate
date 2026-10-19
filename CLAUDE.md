# CLAUDE.md — Real Estate

## Project Overview

Real Estate is a Laravel back office for a property business. **The domain is not defined yet** — the project is a bare Laravel 12 skeleton plus the Metronic 9 theme assets, copied from `~/Sites/store`; features are implemented later. When scope is unclear, default to the simplest version that covers the workflow.

Conventions below come from `~/Sites/store` and apply once the matching pieces (Livewire, ACL, layouts…) are added. Anything marked *(planned)* is not installed yet — add the package when the first feature needs it, don't add packages for things a few lines of Laravel can do.

## Tech Stack

- **PHP** 8.2+ (dev machine runs 8.5), **Laravel 12**
- **Database**: SQLite for host/tests (`database/database.sqlite`); Docker dev environment (`APP_PORT=8082`, MySQL forwarded on `3312`, database `real_estate`, user `homestead` / `secret`) runs MySQL (`real-estate-mysql-1`). **Whenever adding migrations, always run `docker exec real-estate-app-1 php artisan migrate --force` as well as host `php artisan migrate` so MySQL stays in sync.** phpMyAdmin is the shared one at http://localhost:8090 (server `real-estate`, see `~/Sites/phpmyadmin`).
- **Frontend**: Blade + Metronic 9 theme (KTUI components, `kt-*` classes, Keenicons `ki-*`); Livewire 3 *(planned)*
  - Theme CSS/JS is in `public/assets/` (committed). `styles.css` is already a **Tailwind 4** build, so no Tailwind 3 anywhere.
  - `resources/css/app.css` is a utilities-only Tailwind 4 build (no preflight/theme) that fills gaps — the prebuilt theme only has utilities its demos used. Use theme classes first; if a plain utility has no effect, it's probably missing from the prebuilt CSS and this build covers it after `npm run build`. **Responsive gotcha**: our stylesheet loads *before* the theme's `styles.css` on purpose (the theme wins), and both are in the same cascade layer, so a responsive class from this build loses to a plain theme utility for the same property, and this build generates no `sm:/md:/lg:` classes at all. Only responsive classes the theme already ships work; for anything else write a small custom class in `app.css`. After adding a class, check it is in `public/build/assets/app-*.css`.
  - **Scroll containers use the theme's classes**, never plain `overflow-*-auto`: `kt-scrollable-x-auto`, `kt-scrollable-y-auto`, `kt-scrollable-auto`.
  - **No Tailwind `dark:` variant for colours**: in Tailwind 4 it follows the OS, not the app's theme. Write a small class in `app.css` with an `html.dark` / `[data-kt-theme-mode="dark"]` override instead.
  - Font: **Sarabun** (English + Thai), woff2 in `resources/fonts/` (`@font-face` in `resources/css/app.css`).
  - Assets: root-relative `{{ asset('assets/...') }}` paths. `public/build` is git-ignored — run `npm run build` on deploy.
  - Note: `app.css` was copied from store and still carries the store's cacao brown overrides and shop-specific classes; trim or re-theme them when the brand is decided. The `vite.config.js` is stock (`app.css` + `app.js`).
- **Testing**: PHPUnit (`composer test`)

## Conventions

- **Migrations are merged while we are in dev**: a column added to an existing table goes into that table's `create_*` migration, not a new `add_*` one, and we `migrate:fresh --seed` (host + Docker). Stop merging once there is production data.
- **Column order in `create_*` migrations** — every table in the same order:
  1. `id`
  2. every foreign key / morph (`xxx_id`, `xxx_type`)
  3. the other columns (strings, numbers, text, JSON, dates like `xxx_on`)
  4. booleans (`is_xxx`, `has_xxx`)
  5. other datetimes (`xxx_at`)
  6. `softDeletes()` (`deleted_at`)
  7. `timestamps()`, always last
  8. table-level indexes / constraints
- **Enums** for status/type fields, in `app/Enums/` only — never a stub in `app/`.
- **ACL: spatie/laravel-permission** *(planned)*. Roles and permissions live in its tables, not a `users.role` column. Check permissions (`$user->can('module.action')`, `@can`), not role names. Policies wrap the permission checks per model; never check roles inline in controllers or views.
- **Form Requests** for validation; keep controllers thin.
- Follow `.editorconfig` (4 spaces PHP, 2 spaces for js/css/json/yaml/blade.php, LF).
- Match the surrounding code's style.
- **Comment every class and every method/action** in `app/`, in this format:
  - Class: a title line (`Order Controller`), an optional description, then `@SuppressWarnings(...)` lines, then `@package App\Layer` last. Controllers use `@package App\Controllers`; everything else its real layer (`App\Models`, `App\Livewire`, `App\Policies`, `App\Enums`, `App\Notifications`, `App\Providers`, `App\Traits`, `App\Console\Commands`).
  - Method: a one-line description (no trailing period), a blank line, `@param  Type  $name` for each parameter (two spaces), and `@return Type` (`void` included; none on constructors). Class-level methods only, not closures. Put the docblock above any `#[Attribute]` lines.
- **PHPMD**: run `phpmd app text phpmd.xml` after touching PHP. Fix real findings (e.g. `MissingImport`: add the `use`). Warnings that are inherent to Laravel — `StaticAccess`, `UnusedFormalParameter`, `ElseExpression`, `CamelCasePropertyName` — are suppressed **once at class level** in the class docblock (`@SuppressWarnings(PHPMD.StaticAccess)`), never per method or inline.
- **Back office under `/backend`** (once there is a public site): pages behind `auth`, route names start with `backend.`.

## Browser Checks

I check the browser myself — don't drive Chrome by default. Make the fix, verify it as far as code and tests allow, then tell me what to look at and I'll report back.

Don't suggest or offer to check in Chrome either — no "want me to verify it in the browser?". Only drive Chrome when I explicitly ask you to test it there.

## Blade Conventions

- **Never use one-line `@php(...)` above a `@php … @endphp` block in the same view**: Blade pairs the one-liner with the later `@endphp` and skips everything between them. Put the variable in the view's top `@php` block instead.
- Use `@lang('file.group.key')` for user-facing text — never `{{ __('...') }}`. **`@lang` does not escape**: a replacement value anyone can type goes in as `e($value)`.
- Add every translation key to all language files under `lang/` (`lang/en/` and `lang/th/`) at the same time.
- Indent content inside `@if`, `@foreach`, `@forelse`, `@for`, `@while` one level deeper than the directive.
- **Every view must keep its tags balanced.** One `</div>` too many makes the browser pop `<main>` and `.kt-wrapper`, so the footer is squeezed beside the content on a **full load** (a Livewire re-render repairs it). Check the `<div` / `</div>` count for a view you add or re-indent; a feature test that counts them per page is the cheap guard.

## Livewire *(planned)*

- Use `@livewire('name')` directives, not `<livewire:name />`. Authorize in `render()` (mount runs once). Don't use `wire:navigate` — KTUI components would need re-initialising after each navigation.
- **Drawers**: one Livewire component per drawer, opened by a browser event; Livewire shows/hides it, never KTUI's drawer JS.

## Form widgets

Never a plain browser widget: no bare `<select>`, no `<input type="date">`. The Alpine helpers (`ktSelect`, `datePicker`, `limitedTextarea`…) live in `resources/js/app.js` in `~/Sites/store` and are **not copied yet** — port them from there when the first form needs them.

- **Dropdown → KTUI select** via `ktSelect`. **Date → flatpickr** via `datePicker` (value stays `Y-m-d`). **Textarea → `kt-textarea auto-size`** with a `maxlength` and the same `max:` in the request.
- **Badges are light**: `kt-badge-light kt-badge-{success|warning|info|destructive|primary|secondary}`, never the solid colour.
- **Every card title has an icon** in front: `<h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-… text-lg text-primary"></i> @lang('…')</h3>`. Check the icon exists: `grep -c "ki-NAME.ki-filled" public/assets/vendors/keenicons/styles.bundle.css`.
- **Switch** for booleans: `<input type="checkbox" class="kt-switch">` (a hidden `0` input before it in plain forms).

## Form validation

- **No HTML `required` attribute** on inputs — the server is the single source of truth (`FormRequest`, or `rules()` on a Livewire component). Keep the red asterisk with the `required` **class on the `<label>`**.
- **Show invalid state on every validated field**: `@error('field') aria-invalid="true" @enderror` inside the input tag, next to the `@error('field')` message under it.
- Every field a `required` label marks must have `required` in its rules — and a test that fails without it.
- **Toasts** use **Noty** (top-right), not toastr/SweetAlert; `Swal` only for confirm dialogs.

## Common Commands

```bash
composer setup        # install, .env, key, migrate, npm install + build
composer dev          # server + queue + logs + Vite together
composer test         # run PHPUnit
php artisan migrate
php artisan optimize:clear
npm run dev / npm run build
docker compose up -d  # app on :8082, MySQL on :3312
```

## Project Structure

Standard Laravel 12 layout. Add directories only when a real need appears (e.g. `app/Enums/`, `app/Policies/`).
