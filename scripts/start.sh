#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
APP_DIR="${ROOT_DIR}/app"

require_cmd() {
  local name="$1"

  if ! command -v "$name" >/dev/null 2>&1; then
    echo "$name is required"
    exit 1
  fi
}

require_cmd php
require_cmd composer
require_cmd npm

if [ ! -f "${APP_DIR}/.env" ]; then
  cp "${APP_DIR}/.env.example" "${APP_DIR}/.env"
fi

if command -v ollama >/dev/null 2>&1; then
  if ! pgrep -x ollama >/dev/null 2>&1; then
    ollama serve >/dev/null 2>&1 &
  fi
fi

cd "${APP_DIR}"
composer install
npm install

if ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force
fi

php artisan migrate --graceful

if command -v cargo >/dev/null 2>&1 && command -v cargo-tauri >/dev/null 2>&1; then
  cd "${ROOT_DIR}/src-tauri"
  cargo tauri dev
else
  echo "cargo/cargo-tauri missing; running Laravel dev stack instead"
  cd "${APP_DIR}"
  composer dev
fi
