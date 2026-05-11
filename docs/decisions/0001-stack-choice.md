# 0001 — Foundation Stack Choice

**Status:** Accepted  
**Date:** 2026-05-11

## Context

Paraglide is a local-first desktop app for paralegals. Phase 1 requires a native desktop shell, local encrypted data storage, and an embedded web application that can run entirely on the user’s machine.

## Decision

Use the following baseline stack for Phase 1:

- **Tauri 2.x** for the desktop shell and lifecycle control.
- **FrankenPHP + Laravel 12** for the embedded application backend and UI delivery.
- **SQLite + SQLCipher** for local encrypted persistence.
- **Livewire 3 + Alpine.js + Tailwind CSS** for the interactive frontend.
- **Ollama** as the default local AI backend, with **OpenRouter** available as a testing/development fallback.

## Consequences

### Positive

- Supports true local-first execution and strong privacy boundaries.
- Keeps a familiar PHP/Laravel development model while still shipping as a desktop app.
- Enables incremental delivery: shell and backend can evolve independently behind stable interfaces.
- Maintains flexibility to swap AI backends via a dedicated abstraction layer.

### Negative

- Cross-process lifecycle management (Tauri sidecar + Laravel runtime) adds operational complexity.
- SQLCipher integration in Laravel requires explicit connection/key handling and careful testing.
- Supporting both Ollama and OpenRouter increases integration and validation surface.
