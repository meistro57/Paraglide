# Paraglide
<img width="1292" height="480" alt="image" src="https://github.com/user-attachments/assets/e017b6d2-3816-4184-82f3-d0b18603d819" />


> Catch the currents others miss.

**A local-first AI copilot for paralegals.** Built on the Five Misfit Archetypes from the *ROOT ACCESS* framework. Runs entirely on your machine. Your data never leaves the box.

[![License: AGPL v3](https://img.shields.io/badge/License-AGPL_v3-blue.svg)](https://www.gnu.org/licenses/agpl-3.0)
[![Status: Phase 1 Build](https://img.shields.io/badge/status-phase_1_build-orange.svg)]()
[![Local First](https://img.shields.io/badge/local-first-green.svg)]()
[![No Cloud](https://img.shields.io/badge/cloud-zero-brightgreen.svg)]()

---

## The Premise

You're not support staff. You never were.

You're a structural engineer of legal cases — reading documents for signal the attorney misses, digging through research like a forensic archaeologist, turning 40,000 pages of mess into a story that fits in a jury's head, performing calmly under trial pressure while everyone else leaks adrenaline.

Most legal software treats you like a labor input. Optimize your hours. Track your productivity. Generate reports for management.

**Paraglide treats you like the operator.** The thermal-rider. The pattern-hunter. The one whose brain was already shaped for this work, even when nobody around you had language for it.

---

## What It Does

Paraglide runs on your laptop and gives you five working modes that mirror how high-performing paralegals actually think:

| Mode | What It Does |
|---|---|
| 🛰 **Antenna** | Quick-capture for pattern pings — the "wait, that's off" moments you can't always articulate. The system clusters them semantically and surfaces patterns across thousands of small notes you'd never connect by memory. |
| 🏗 **Architect** | Build briefs as structural load-paths instead of paragraphs. Your AI collaborator stress-tests the architecture: *"if argument 1 dies, does argument 2 still stand?"* |
| 🪨 **Archaeologist** | A personal Westlaw that compounds over a decade. Every authority you've ever traced, every lineage, every note, semantically searchable and growing with you. |
| ⚗ **Alchemist** | Drop in the deposition transcripts, the email production, the regulatory record. Build the chronology. Ask the alchemist's question: *what is this case actually about — not the legal claim, the human story underneath?* |
| 🚀 **Astronaut** | Pre-trial protocols, pre-mortem workspaces, state check-ins. The work *and* the nervous system that does the work. |

Underneath all five: **Lyra**, a local AI collaborator who reasons against your private knowledge base, helps you draft and pattern-match and distill — and never auto-applies anything. *Lyra proposes; you dispose.* Every suggestion is pending review until you accept it.

---

## Why Local-First

Legal data is privileged. The threat model for legal software isn't internet hackers — it's firm IT departments, opposing counsel subpoenas, SaaS vendor TOS changes, and eventual vendor acquisitions.

Paraglide solves this the only way it's actually solvable:

- ✅ **Your data lives on your machine.** Encrypted at rest with SQLCipher and libsodium. The keys derive from your password. We can't read it. We can't subpoena it. We can't leak it. Because we don't have it.
- ✅ **AI runs on your hardware.** Ollama with a tiered local model strategy. Every prompt, every retrieval, every chronology extraction happens in your RAM, against your data.
- ✅ **Zero network egress by default.** Unplug your ethernet. Paraglide works identically. The only outbound traffic is the one-time model download during setup, and you explicitly approve it.
- ✅ **You own the binary.** You own the data. You own the workflow. The firm can't take it from you when you leave. The vendor can't paywall your case files. The data is yours, forever, the way good software used to work.
- ✅ **Optional external assist.** For the rare task that needs frontier cloud reasoning, a clearly-marked button routes a single query through a configurable cloud endpoint. PII redaction, full audit log, never default. You control the boundary.

---

## The Stack

| Layer | Tech |
|---|---|
| 🖥 Desktop shell | [Tauri 2.x](https://v2.tauri.app/) (Rust core, native webview, lean binary) |
| 🐘 PHP runtime | [FrankenPHP](https://frankenphp.dev/) (single static binary, embedded PHP, Caddy-based) |
| 🎯 Web framework | [Laravel 12](https://laravel.com) |
| 🗄 Database | SQLite + [SQLCipher](https://www.zetetic.net/sqlcipher/) (transparent encryption) |
| 🔍 Vector store | [Qdrant](https://qdrant.tech/) (local sidecar, per-matter collections) |
| 🤖 Local AI | [Ollama](https://ollama.com) with tiered model strategy |
| 🧪 Dev/test AI | [OpenRouter](https://openrouter.ai) (clearly marked, never default for real data) |
| 🪡 Embeddings | [FastEmbed](https://qdrant.github.io/fastembed/) with BAAI/bge models |
| 👁 OCR | [Tesseract](https://tesseract-ocr.github.io/) |
| 🔐 Encryption | [libsodium](https://libsodium.org/) for app-layer encryption |
| ✨ Frontend | [Livewire 3](https://livewire.laravel.com) + [Alpine.js](https://alpinejs.dev) + [Tailwind CSS](https://tailwindcss.com) |

**Everything localhost. Everything encrypted at rest. Network egress: zero by default.**

---

## Hardware Tiers

Paraglide detects your hardware on install and recommends a model tier:

| Tier | Hardware | Local Model | Experience |
|---|---|---|---|
| **🪶 Lightweight** | 16GB RAM, CPU or integrated GPU | Llama 3.1 8B | Slower but functional. Antenna and journaling sing. |
| **⚖️ Standard** | 32GB RAM, 8-16GB VRAM GPU | Qwen 2.5 32B | All archetypes, full quality, fast. |
| **⚡ Power** | 64GB+ RAM, 16GB+ VRAM GPU | Qwen 2.5 72B / DeepSeek R1 70B | Near-frontier quality, locally. |

---

## Project Status

🚧 **Currently in Phase 1: Foundation & Shell.**

We're building the encrypted desktop shell, the AI backend abstraction (Ollama for production, OpenRouter for dev), and the onboarding flow. No archetype features yet — Phase 1 proves the plumbing is solid before we build on top of it.

### Current Implementation Snapshot

- Laravel 12 app scaffolded under `app/` with SQLCipher-oriented config placeholders.
- App-layer encryption foundation implemented (`Encryptor` + encrypted model attribute trait).
- AI backend abstraction implemented (Ollama + OpenRouter backends, resolver, Lyra orchestration service).
- Onboarding scaffold implemented with persisted progress and route gating (`/` redirects to onboarding until completion).
- Tauri shell scaffold added under `src-tauri/` with FrankenPHP sidecar lifecycle management.
- Root dev/build scripts added: `scripts/dev.sh`, `scripts/build.sh`.
- PHPUnit coverage includes crypto, backend parsing/resolver behavior, Lyra orchestration, and onboarding persistence.

### Roadmap

- **Phase 1** — Tauri shell, Laravel skeleton, SQLCipher database, onboarding, basic Lyra chat ← *current*
- **Phase 2** — Document ingestion pipeline (PDF, docx, email), Qdrant integration, OCR
- **Phase 3** — **Antenna** module (the killer feature: quick-capture, semantic clustering, cross-matter pattern surfacing)
- **Phase 4** — **Alchemist** module (chronology auto-extraction, theme distillation, exhibit curation)
- **Phase 5** — Polish, beta with real paralegals
- **Phase 6** — Soft launch
- **v1.5** — **Architect** graph editor, **Archaeologist** lineage tracing, **Astronaut** protocols, MCP server
- **v2** — Voice mode, mobile companion, zero-knowledge mode

---

## Quickstart for Developers

> ⚠️ Phase 1 is still being built. These instructions will be accurate once Phase 1 completes.

### Prerequisites

- Rust 1.75+ ([rustup.rs](https://rustup.rs))
- Node.js 20+ ([nvm](https://github.com/nvm-sh/nvm) recommended)
- PHP 8.3+ with `sodium`, `sqlite3`, `pdo_sqlite` extensions
- Composer 2.x
- (Optional) [Ollama](https://ollama.com) for local AI inference
- (Optional) An [OpenRouter](https://openrouter.ai) API key for cloud testing

### Setup

```bash
git clone https://github.com/<your-username>/paraglide.git
cd paraglide

# Install Laravel dependencies
cd app
composer install
cp .env.example .env
php artisan key:generate

# Build the frontend
npm install
npm run build

# Back to root, run dev mode
cd ..
./scripts/dev.sh
```

The Tauri window will launch, FrankenPHP will spin up serving the Laravel app on a random localhost port, and the onboarding flow will guide you through setup.

---

## Design Principles

The foundation Paraglide is built on:

1. **Zero network egress by default.** No telemetry, no analytics, no auto-updates that phone home.
2. **Single binary install.** Double-click installer. Everything bundled. No Docker required.
3. **Single user, single machine.** One paralegal, one Paraglide. No accounts, no multi-tenancy.
4. **All AI local.** Production AI never leaves your hardware.
5. **All data local and encrypted.** SQLCipher + libsodium. Keys derive from your password.
6. **Portable.** Your entire Paraglide instance lives in one directory. Back it up, move it, restore it.
7. **Open formats.** Markdown for journals, standard SQL dumps, standard document formats. No lock-in.
8. **Transparent operation.** You can see what processes are running, what files exist, what's in the database. Nothing hidden.
9. **Lyra proposes, you dispose.** AI never auto-applies anything to your case file. Every suggestion is pending until you accept.
10. **Verifiable.** Every Lyra claim cites its sources. Click through and verify. Tool calls have undo affordances.

---

## The ADHD-Aware Operating System

Paraglide is designed for misfit-builder-pattern-hunter brains. The kind who hyperfocus when interested, go ghost on the boring stuff, and find what other readers miss.

- 🚫 **No streak counters.** No daily-quota dashboards. No guilt notifications.
- 🌊 **Burst output friendly.** You produce in waves. The system records honestly without judgment.
- 🧘 **"Do not disturb the trance" mode.** Global silence on all alerts until you re-enable.
- ⚡ **Sub-second quick capture.** Hit a hotkey, dump the thought, get back to flow.
- 📚 **Boring-stuff automation.** Templates, reminders, billing prompts — outsourced to systems so they don't depend on your memory.
- 🎯 **First-hour timer.** Optional ritual mode that blocks notifications and runs focused 90-minute sessions.

The frame is: *your brain isn't broken. The institutions designed for different brains were broken. Paraglide is what software looks like when it's designed for yours.*

---

## The Broader Framework

Paraglide is the first profession-specific instantiation of the **Five Misfit Archetypes** from **ROOT ACCESS** — a reality-engineering framework for builders, pattern-hunters, and creators whose nervous systems don't fit the standard institutional template.

The frame says: you can be deliberate about the structure of your life, your work, your state of mind, and your tools, in a way most people aren't. And when you are, the results compound in ways most people never see.

The Five Archetypes are general. The professions are specific. Paraglide is chapter one. Designers, engineers, researchers, therapists — the same pattern applies to any technical-creative work where the operator's brain is the engine.

If this resonates, you're probably one of us. Welcome.

---

## Contributing

This is currently a solo build by [Meistro](https://github.com/meistro57). Once Phase 1 lands, contributions will open up.

If you're a paralegal interested in being a beta tester, or a developer who wants to help, [open an issue](../../issues) or reach out via [Quantum Minds United](https://forum.quantummindsunited.com).

---

## License

Paraglide is licensed under the **GNU Affero General Public License v3.0** (AGPL-3.0).

This means:
- ✅ You can use Paraglide for free, including commercially
- ✅ You can modify it, fork it, build on it
- ⚠️ If you distribute it (or run a modified version as a network service), you must release your changes under AGPL-3.0
- ⚠️ You must preserve the copyright notice and license

See [LICENSE](./LICENSE) for the full text.

---

## Acknowledgments

Paraglide stands on a lot of shoulders:

- **The misfit paralegals** — past, present, and future — whose unrecognized structural work runs the legal system. This is for you.
- **The open source legal data community** — [CourtListener](https://www.courtlistener.com), [Harvard Caselaw Access Project](https://case.law), the [Free Law Project](https://free.law) — for making legal data accessible.
- **The local-first software movement** — for proving cloud-by-default was never the only option.
- **The Quantum Minds United community** — for being the soil this kind of work grows in.

---

<p align="center">
  <b>Local. Encrypted. Hers. Always.</b><br>
  <i>Built with steel-detailer engineering brain by <a href="https://github.com/meistro57">Meistro</a>.</i>
</p>

