#!/usr/bin/env bash
# Local installer for an existing UniBank checkout.
# Prefer the aaPanel one-liner for fresh servers; this wraps the same logic.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT"

exec bash "$ROOT/aapanel-install.sh" --dir "$ROOT" --skip-clone --no-pull "$@"
