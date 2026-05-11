#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
APP_DIR="${ROOT_DIR}/app"
DIST_DIR="${ROOT_DIR}/dist"

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

cd "${APP_DIR}"
composer install --no-dev --optimize-autoloader
npm ci
npm run build

if ! command -v cargo >/dev/null 2>&1 || ! command -v cargo-tauri >/dev/null 2>&1; then
  echo "cargo/cargo-tauri required for desktop build"
  exit 1
fi

cd "${ROOT_DIR}/src-tauri"
cargo tauri build

mkdir -p "${DIST_DIR}"
if [ -d "target/release/bundle" ]; then
  cp -R "target/release/bundle/." "${DIST_DIR}/"
fi

echo "Build artifacts copied to ${DIST_DIR}"
