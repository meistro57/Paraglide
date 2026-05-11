#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
APP_DIR="${ROOT_DIR}/app"

if ! command -v php >/dev/null 2>&1; then
  echo "php is required"
  exit 1
fi

if ! command -v composer >/dev/null 2>&1; then
  echo "composer is required"
  exit 1
fi

if ! command -v npm >/dev/null 2>&1; then
  echo "npm is required"
  exit 1
fi

if command -v ollama >/dev/null 2>&1; then
  if ! pgrep -x ollama >/dev/null 2>&1; then
    ollama serve >/dev/null 2>&1 &
  fi
fi

cd "${APP_DIR}"
composer install
npm install
php artisan migrate --graceful

if command -v cargo >/dev/null 2>&1 && command -v cargo-tauri >/dev/null 2>&1; then
  cd "${ROOT_DIR}/src-tauri"
  cargo tauri dev
else
  echo "cargo/cargo-tauri missing; start Laravel dev stack with:"
  echo "  cd ${APP_DIR} && composer dev"
fi
