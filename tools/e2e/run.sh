#!/usr/bin/env bash
#
# Stands up OpenVWR behind the Pratique proxy, locally and natively.
#
#   Postgres :5432   already serving OpenVWR; Pratique gets its own database
#   Mailpit  :1025   SMTP for the login codes, web UI on :8025
#   OpenVWR  :8000   php artisan serve, AUTH_DRIVER=pratique
#   Pratique :8080   the proxy — this is the address you browse to
#
# No Docker. Assumes Postgres and Mailpit are already reachable; it will say so
# if they are not rather than failing later in a confusing way.
#
# Re-runnable: provisioning is guarded, so a second run tops up rather than
# duplicating.

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PRATIQUE_SRC="${PRATIQUE_SRC:-$ROOT/../pratique}"
CFG="$ROOT/tools/e2e/pratique.e2e.yaml"
RUN="$ROOT/tools/e2e/.run"
PHP="${PHP:-$(brew --prefix php@8.4)/bin/php}"

mkdir -p "$RUN"

say() { printf '\n==> %s\n' "$*"; }

require_port() {
  local port="$1" what="$2" hint="$3"
  if ! nc -z localhost "$port" 2>/dev/null; then
    echo "error: $what is not reachable on :$port" >&2
    echo "       $hint" >&2
    exit 1
  fi
}

say "checking what is already running"
require_port 5432 "Postgres" "start your local Postgres (OpenVWR uses it too)"
require_port 1025 "Mailpit SMTP" "run: mailpit --listen 127.0.0.1:8025 --smtp 127.0.0.1:1025 &"
[ -d "$PRATIQUE_SRC" ] || { echo "error: no pratique checkout at $PRATIQUE_SRC" >&2; exit 1; }
echo "    ok"

say "building pratique"
(cd "$PRATIQUE_SRC" && go build -o "$RUN/pratique" ./cmd/pratique)

say "migrating + signing key"
"$RUN/pratique" migrate -config "$CFG"
# keygen is not idempotent in the "already exists" sense; a second run is a no-op
# failure we do not care about.
"$RUN/pratique" keygen -config "$CFG" 2>/dev/null || true

say "provisioning tenants and members from OpenVWR"
# The export is read-only; the script it produces is the part that writes, and
# every step of it probes before mutating.
(cd "$ROOT/src/cms" && "$PHP" artisan pratique:export-provisioning --format=sh) > "$RUN/provision.sh"
PRATIQUE="$RUN/pratique" PRATIQUE_ARGS="-config $CFG" bash "$RUN/provision.sh"

say "starting OpenVWR on :8000"
(
  cd "$ROOT/src/cms"
  AUTH_DRIVER=pratique \
  PRATIQUE_ISSUER="http://localhost:8080" \
  PRATIQUE_AUDIENCE="app://openvwr-e2e" \
  PRATIQUE_JWKS_URL="http://localhost:8080/.well-known/pratique/jwks.json" \
  "$PHP" artisan serve --host=127.0.0.1 --port=8000 > "$RUN/openvwr.log" 2>&1 &
  echo $! > "$RUN/openvwr.pid"
)
until nc -z localhost 8000 2>/dev/null; do sleep 1; done
echo "    up"

say "starting pratique on :8080"
"$RUN/pratique" serve -config "$CFG" > "$RUN/pratique.log" 2>&1 &
echo $! > "$RUN/pratique.pid"
until nc -z localhost 8080 2>/dev/null; do sleep 1; done
echo "    up"

cat <<INFO

Ready.

  Browse       http://localhost:8080/
  Mail         http://localhost:8025   (login codes arrive here)
  Logs         $RUN/openvwr.log · $RUN/pratique.log
  Stop         bash tools/e2e/stop.sh

A direct request to http://localhost:8000/ should be refused with 403 — that is
the app failing closed without an assertion, and it is worth checking.
INFO
