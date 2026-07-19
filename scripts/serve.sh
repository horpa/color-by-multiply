#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if ! command -v php >/dev/null 2>&1; then
  echo "PHP was not found on PATH."
  echo "Install PHP 8.1+ with gd and fileinfo, or run: docker compose up --build"
  exit 1
fi

php scripts/check.php
echo
echo "Starting server at http://localhost:8080"
echo "Press Ctrl+C to stop."
echo
php -S localhost:8080 -t public
