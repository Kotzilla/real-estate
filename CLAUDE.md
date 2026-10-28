# CLAUDE.md — Real Estate

## Project Overview

Real Estate is the back office of an agency selling and letting property in **Southern Thailand** (the 14 southern provinces). The flow is findnest's (`~/Sites/findnest`): an **owner** lists a **property** (for sale, for rent or both, with photos and facilities), an agent finds a **customer**, and the deal becomes a **contract** — a lease or an agreement to sell and purchase — printed in English or Thai. Signing a contract marks the property rented / sold. The structure, layout, users, permissions and activity log are store's (`~/Sites/store`). The site covers **Koh Phangan only** (`Property::HOME_DISTRICT`, Ko Pha-ngan district); the public site: landing page `/` (`HomeController`: hero, newest listings, areas), map search `/properties` (filters | Mapbox map | listings; `ListingController@index` + `resources/js/home.js`, Alpine `propertyMap`, filtered in the browser, takes `?offer=` / `?area=`), a listing's page `/property/{code}/{slug}` (`ListingController@show`, 301 to the current slug) and `/privacy` (PDPA, `lang/*/privacy.php`). Header menu in `layouts/site.blade.php`, footer in `layouts/partials/site-footer.blade.php`. Map keys: `MAPBOX_TOKEN`, `GOOGLE_MAP_API` (`config/services.php`). When scope is unclear, default to the simplest version that covers the workflow.

Don't add packages for things a few lines of Laravel can do.

## Domain

- `Property` (code `PHU-0001` from the province) → `owner` (a `Person`), `agent` (a `User`), `province` / `district` (seeded from `database/data/south.json`), photos (`HasPhotos`), facilities (spatie tags, type `Property::FACILITY`). The form picks a district; the province comes from it.
- `Person` — `PersonType` owner / customer / outside agent. Names, address kept in English **and** Thai: the contract copies print their own language (`fullName($lang)`, falls back to English). Owners carry the bank account the rent goes to.
- `Contract` (code `RE-2026-0001`) — `ContractType` rent / sale, `ContractStatus` draft / active / completed / cancelled. The commission is worked out on save (`calculate()`: rent × months or sale price, × %), and status changes move the property (`syncPropertyStatus()`). A lease's end date defaults to start + months − 1 day. Printed by `contracts/print` → `contracts/print/{rent,sale}-{en,th}.blade.php`; the lease clauses' figures are constants on `Contract`. Thai copies use the Buddhist year (`Contract::dateIn`) and amounts in words (`Contract::inWords`).
- Dev data: `DemoSeeder` (not in production). 60 mock Koh Phangan listings (24 hand-written, 36 random) with Unsplash beach / villa photos (`DemoSeeder::PHOTOS`) (skipped offline and in tests). Sign in `admin` / `letmein`, agent `napat` / `letmein`.

## Tech Stack

- **PHP** 8.2+ (dev machine runs 8.5), **Laravel 12**
- **Database**: SQLite for host/tests (`database/database.sqlite`); Docker dev environment (`APP_PORT=8082`, MySQL forwarded on `3312`, database `real_estate`, user `homestead` / `secret`) runs MySQL (`real-estate-mysql-1`). **Whenever adding migrations, always run `docker exec real-estate-app-1 php artisan migrate --force` as well as host `php artisan migrate` so MySQL stays in sync.** phpMyAdmin is the shared one at http://localhost:8090 (server `real-estate`, see `~/Sites/phpmyadmin`).
- **Frontend**: Blade + Metronic 9 theme (KTUI components, `kt-*` classes, Keenicons `ki-*`); Livewire 3
  - Theme CSS/JS is in `public/assets/` (committed). `styles.css` is already a **Tailwind 4** build, so no Tailwind 3 anywhere.
  - `resources/css/app.css` is a utilities-only Tailwind 4 build (no preflight/theme) that fills gaps — the prebuilt theme only has utilities its demos used. Use theme classes first; if a plain utility has no effect, it's probably missing from the prebuilt CSS and this build covers it after `npm run build`. **Responsive gotcha**: our stylesheet loads *before* the theme's `styles.css` on purpose (the theme wins), and both are in the same cascade layer, so a responsive class from this build loses to a plain theme utility for the same property, and this build generates no `sm:/md:/lg:` classes at all. Only responsive classes the theme already ships work; for anything else write a small custom class in `app.css`. After adding a class, check it is in `public/build/assets/app-*.css`.
  - **Scroll containers use the theme's classes**, never plain `overflow-*-auto`: `kt-scrollable-x-auto`, `kt-scrollable-y-auto`, `kt-scrollable-auto`.
  - **No Tailwind `dark:` variant for colours**: in Tailwind 4 it follows the OS, not the app's theme. Write a small class in `app.css` with an `html.dark` / `[data-kt-theme-mode="dark"]` override instead.
  - Font: **Sarabun** (English + Thai), woff2 in `resources/fonts/` (`@font-face` in `resources/css/app.css`).
  - Assets: root-relative `{{ asset('assets/...') }}` paths. `public/build` is committed (the live server has no Node and deploys by `git pull`): run `npm run build` and commit it with any CSS/JS change. `public/storage` must be a **relative** link (`ln -s ../storage/app/public public/storage`) so it works inside Docker too.
  - **Brand colours** (after islanders-properties.com): navy `--primary` #3e5083, lime accent `--brand-lime` #c1df0f (dark text only — `.brand-lime`), near-black sidebar #12171f, page #f7f9fc; all in the brand block of `app.css`. `app.css` still carries some of store's shop-specific classes (pay dots, dashboard helpers); trim them when they get in the way. The `vite.config.js` is stock (`app.css` + `app.js`).
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
- **ACL: spatie/laravel-permission**. Roles: `App\Enums\UserRole` (admin, manager, agent); permissions and their default roles: `PermissionSeeder::DEFAULTS` (`properties.*`, `people.*`, `contracts.*`, `settings.manage`, `activity.view`), changed afterwards on the Permissions page. Roles and permissions live in its tables, not a `users.role` column. Check permissions (`$user->can('module.action')`, `@can`), not role names. Policies wrap the permission checks per model; never check roles inline in controllers or views.
- **Form Requests** for validation; keep controllers thin.
- Follow `.editorconfig` (4 spaces PHP, 2 spaces for js/css/json/yaml/blade.php, LF).
- Match the surrounding code's style.
- **Comment every class and every method/action** in `app/`, in this format:
  - Class: a title line (`Order Controller`), an optional description, then `@SuppressWarnings(...)` lines, then `@package App\Layer` last. Controllers use `@package App\Controllers`; everything else its real layer (`App\Models`, `App\Livewire`, `App\Policies`, `App\Enums`, `App\Notifications`, `App\Providers`, `App\Traits`, `App\Console\Commands`).
  - Method: a one-line description (no trailing period), a blank line, `@param  Type  $name` for each parameter (two spaces), and `@return Type` (`void` included; none on constructors). Class-level methods only, not closures. Put the docblock above any `#[Attribute]` lines.
- **PHPMD**: run `phpmd app text phpmd.xml` after touching PHP. Fix real findings (e.g. `MissingImport`: add the `use`). Warnings that are inherent to Laravel — `StaticAccess`, `UnusedFormalParameter`, `ElseExpression`, `CamelCasePropertyName` — are suppressed **once at class level** in the class docblock (`@SuppressWarnings(PHPMD.StaticAccess)`), never per method or inline.
- **Back office under `/backend`**: pages behind `auth`, route names start with `backend.`.
- **Activity log** (spatie/laravel-activitylog): call `$this->log($model, 'property-update')` (`App\Traits\LogActivity`) after a change; add the code's label to `lang/*/activity.php`.
- **Morph map is enforced** (`AppServiceProvider`): a new model that gets photos, tags or log entries needs its alias there.

## Browser Checks

I check the browser myself — don't drive Chrome by default. Make the fix, verify it as far as code and tests allow, then tell me what to look at and I'll report back.

Don't suggest or offer to check in Chrome either — no "want me to verify it in the browser?". Only drive Chrome when I explicitly ask you to test it there.

## Blade Conventions

- **Never use one-line `@php(...)` above a `@php … @endphp` block in the same view**: Blade pairs the one-liner with the later `@endphp` and skips everything between them. Put the variable in the view's top `@php` block instead.
- Use `@lang('file.group.key')` for user-facing text — never `{{ __('...') }}`. **`@lang` does not escape**: a replacement value anyone can type goes in as `e($value)`.
- Add every translation key to all language files under `lang/` (`lang/en/` and `lang/th/`) at the same time.
- Indent content inside `@if`, `@foreach`, `@forelse`, `@for`, `@while` one level deeper than the directive.
- **Every view must keep its tags balanced.** One `</div>` too many makes the browser pop `<main>` and `.kt-wrapper`, so the footer is squeezed beside the content on a **full load** (a Livewire re-render repairs it). Check the `<div` / `</div>` count for a view you add or re-indent; a feature test that counts them per page is the cheap guard.

## Livewire

- Use `@livewire('name')` directives, not `<livewire:name />`. Authorize in `render()` (mount runs once). Don't use `wire:navigate` — KTUI components would need re-initialising after each navigation.
- **Drawers**: one Livewire component per drawer, opened by a browser event; Livewire shows/hides it, never KTUI's drawer JS.

## Form widgets

Never a plain browser widget: no bare `<select>`, no `<input type="date">`. The Alpine helpers (`ktSelect`, `datePicker`, `tagInput`, `limitedTextarea`…) are in `resources/js/app.js` (from store). `<x-field>` is a label + control + hint + error: `type` text / number / date (datePicker) / textarea, or a slot for a select.

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

## Servers

One droplet, `ssh root@178.128.215.25` (Ubuntu, nginx, PHP 8.5-fpm, MySQL `real_estate` / `real_estate_dev`, deploy key for the repo). Deploy = `git pull` + `composer install` as root, then **artisan as the site's user** (`sudo -u www-data php artisan migrate --force && sudo -u www-data php artisan optimize`; `www-dev` on dev) — artisan run as root leaves root-owned cache/log files the site can't write.

- **Production** `https://first-real-estate.com` — branch `main`, `/var/www/html`, runs as `www-data` (`APP_ENV=production`, no demo data), nginx `sites-enabled/digitalocean`.
- **Dev** `dev.first-real-estate.com` — branch `develop`, `/var/www/dev`, own FPM pool as `www-dev` (`APP_ENV=staging`, dev deps installed so `DemoSeeder` runs), nginx `sites-enabled/dev`, basic auth `/etc/nginx/.htpasswd-dev`.
- Code is root-owned and read-only to PHP; only `storage/` and `bootstrap/cache/` are writable; `.env` is 640. PHP-FPM disables `exec`, `shell_exec`, `proc_open`… (`/etc/php/8.5/fpm/conf.d/99-hardening.ini`; the CLI keeps them), so web code must never shell out.

## Project Structure

Standard Laravel 12 layout. Pages: a thin controller per module (`PropertyController`, `PersonController`, `ContractController`, `DashboardController`) with a `FormRequest`; each list is a Livewire table (`app/Livewire/{Properties,People,Contracts}/Table.php`). Users, permissions and the activity log are Livewire pages copied from store.
