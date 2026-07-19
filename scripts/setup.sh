#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

mkdir -p uploads storage/worksheets

if ! command -v php >/dev/null 2>&1; then
  echo "PHP is not installed."
  echo "Install PHP 8.1+ with gd and fileinfo, or run: docker compose up --build"
  exit 0
fi

php scripts/check.php
echo
echo "Setup complete. Start the app with:"
echo "  ./scripts/serve.sh"
