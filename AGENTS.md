# AGENTS.md

## Source of Truth

- Product scope and sequencing are defined in `CODE_BRIEF.md`.
- Implement only what is observable in this repo plus explicitly requested scope.

## Current Repository Layout

- `app/` — Laravel 12 application
  - `app/Services/Crypto/Encryptor.php` — libsodium secretbox encrypt/decrypt service
  - `app/Models/Concerns/HasEncryptedAttributes.php` — model trait for encrypted fields
  - `app/Services/AI/` — backend contract, Ollama/OpenRouter backends, resolver, Lyra chat service
  - `app/Livewire/OnboardingFlow.php` + `resources/views/livewire/onboarding-flow.blade.php` — onboarding scaffold
  - `app/Livewire/LyraChat.php` + `resources/views/livewire/lyra-chat.blade.php` — chat scaffold with encrypted persistence
  - `app/Http/Controllers/LockController.php`, `app/Http/Middleware/EnsureSessionUnlocked.php`, `app/Services/Security/SessionUnlockManager.php` — lock/unlock + idle timeout flow
  - `database/migrations/` — users, AI tables, onboarding progress
  - `tests/Unit` and `tests/Feature` — PHPUnit suite covering crypto, AI backends, onboarding, routes
- `src-tauri/` — Tauri 2 shell scaffold with sidecar lifecycle (`main.rs`, `sidecar.rs`, `lifecycle.rs`)
- `scripts/start.sh`, `scripts/dev.sh`, `scripts/build.sh` — root developer scripts
- `docs/decisions/0001-stack-choice.md` — initial ADR

## Commands

### Laravel app (run from `app/`)

```bash
composer install
npm install
php artisan migrate
php artisan test
composer dev
```

### Root scripts

```bash
./scripts/start.sh
./scripts/dev.sh
./scripts/build.sh
```

Notes:
- `scripts/start.sh` installs dependencies, creates `.env` if needed, generates APP_KEY, migrates DB, starts Ollama when present, then runs `cargo tauri dev` (or `composer dev` fallback).
- `scripts/dev.sh` delegates to `scripts/start.sh`.
- `scripts/build.sh` builds Laravel assets and Tauri bundles into `dist/`.

## Implemented Patterns

### Encryption

- `Encryptor` uses `sodium_crypto_secretbox` with random nonce prepended to ciphertext.
- Key comes from constructor override or `config('app.key')` (`base64:` Laravel key format supported).
- `HasEncryptedAttributes` encrypts in `setAttribute` and decrypts in `getAttributeValue` for fields listed in model `$encrypted`.

### AI backend abstraction

- Interface: `App\Services\AI\Contracts\AIBackend`
  - `streamChat(array $messages, array $options = []): Generator`
  - `listModels(): array`
  - `isAvailable(): bool`
- Backends:
  - `OllamaBackend` parses NDJSON stream chunks
  - `OpenRouterBackend` parses SSE `data:` chunks and enforces auth headers
- `BackendResolver` chooses preferred backend and falls back when unavailable.
- Lyra orchestration: `LyraChatService` prepends `config('lyra.system_prompt')` and streams via resolved backend.

### Onboarding flow

- Root route redirects to onboarding until completion, then to lock/home depending on unlock state.
- Progress persisted in `onboarding_progress` table (single-row workflow via id=1).
- Livewire flow steps currently scaffolded: welcome → password → recovery → hardware → backend → done.
- Completing onboarding unlocks the session; idle timeout and manual lock require password re-entry.

## Test Suite

Run from `app/`:

```bash
php artisan test
```

Coverage currently includes:
- Encryptor round-trip/null/invalid payload behavior
- Encrypted attribute storage/readback
- Ollama/OpenRouter request formatting and stream parsing
- Backend resolver fallback behavior
- Lyra chat orchestration and encrypted message persistence
- Onboarding route/validation/progress persistence
- Lock/unlock and idle-timeout gating

## Config Keys in Active Use

- DB/crypto placeholders in `.env.example`: `DB_CONNECTION=sqlcipher`, `DB_SQLCIPHER_*`
- AI/backend keys: `AI_BACKEND`, `OLLAMA_BASE_URL`, `OLLAMA_MODEL`, `OPENROUTER_*`
- `config/openrouter.php` for default and curated model list
- `config/lyra.php` for system prompt
- `config/paraglide.php` for `unlock_idle_timeout_minutes`

## Gotchas

- Livewire v3 is required; avoid accidental upgrade to v4.
- System Composer in this environment may emit PHP 8.4 deprecation notices; installs still work.
- `routes/web.php` checks `Schema::hasTable('onboarding_progress')` to avoid failures in pre-migration contexts.
- Tauri scaffold exists, but local Rust/cargo availability depends on developer machine.
