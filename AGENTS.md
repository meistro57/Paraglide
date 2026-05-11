# AGENTS.md

## Repository Status (Observed)

- Root currently contains project docs and the Laravel app scaffold under `app/`.
- Tauri shell (`src-tauri/`) and project scripts (`scripts/dev.sh`, `scripts/build.sh`) are **not present yet**.
- Product/architecture intent is documented in `CODE_BRIEF.md` (Phase 1 brief).

## Source of Truth

- Use `CODE_BRIEF.md` for Phase 1 scope and sequencing.
- Current implementation is partial; verify any brief requirement against actual files before coding.

## Project Structure

- `app/` — Laravel 12 application
  - `app/app/` — PHP application code
  - `app/config/` — framework and app configuration
  - `app/routes/` — route definitions (`routes/web.php` currently serves `welcome`)
  - `app/resources/` — Blade views and frontend assets
  - `app/tests/` — PHPUnit tests (`Feature` and `Unit` examples)
  - `app/composer.json` — PHP deps and dev/test scripts
  - `app/package.json` — Vite/Tailwind frontend scripts
- `docs/decisions/0001-stack-choice.md` — initial ADR

## Essential Commands

Run from `app/` unless noted.

### Install / Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### Development

```bash
composer dev
```

This runs Laravel server, queue listener, logs (`pail`), and Vite concurrently.

### Testing

```bash
composer test
# or
php artisan test
```

### Frontend

```bash
npm run dev
npm run build
```

## Current Dependency/Stack Notes (Observed)

- Laravel `^12.0`
- Livewire `^3.0` (explicitly pinned to v3 line)
- `paragonie/sodium_compat` installed
- Tailwind + Vite are configured via `@tailwindcss/vite` and `laravel-vite-plugin`

## Config and Security-Relevant Patterns

- `.env.example` defaults to:
  - `DB_CONNECTION=sqlcipher`
  - `DB_SQLCIPHER_ENABLED=true`
  - `DB_SQLCIPHER_KEY_HEX=` (placeholder)
  - `AI_BACKEND`, `OLLAMA_BASE_URL`, `OPENROUTER_*` placeholders
- SQLCipher hook is implemented in `app/app/Providers/AppServiceProvider.php`:
  - Listens to `Illuminate\Database\Events\ConnectionEstablished`
  - Applies `PRAGMA key = "x'{hex}'"` only when:
    - connection config `sqlcipher` is truthy
    - key is non-empty hex (`ctype_xdigit`)

## Coding Conventions Seen in Repo

- PHP style follows Laravel defaults with typed method signatures and PSR-4 namespaces.
- Tests are PHPUnit class-based (not Pest currently).
- Keep framework defaults unless a Phase 1 requirement explicitly changes them.

## Testing Approach (Current)

- Baseline smoke tests exist:
  - `tests/Unit/ExampleTest.php`
  - `tests/Feature/ExampleTest.php`
- Run tests after config/service-provider changes; these currently verify app boot and root route response.

## Known Gotchas

- System Composer on this machine emits many PHP 8.4 deprecation notices during install/update; installs still complete.
- `composer require livewire/livewire` may pull v4 by default; keep `^3.0` per brief.
- Root-level automation scripts referenced in `CODE_BRIEF.md` are not created yet; use `app/` commands directly for now.

## When Implementing Next Phase Tasks

- Keep changes aligned to `CODE_BRIEF.md` task ordering unless explicitly reprioritized.
- Document non-trivial architecture choices as ADRs in `docs/decisions/`.
- Prefer adding concrete commands to root docs once `src-tauri/` and `scripts/` are introduced.
