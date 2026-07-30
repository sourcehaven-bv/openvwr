#!/bin/bash
# Diagnoses a native (non-Docker) development environment.
# Read-only: reports problems and how to fix them, changes nothing.
set -uo pipefail

CMS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../src/cms" && pwd)"
PHP_FORMULA="php@8.4"
DB_PORT="${DB_PORT:-5432}"
DB_NAME="${DB_NAME:-openvwr_local}"
TEST_DB_NAME="${TEST_DB_NAME:-testing}"
MINIO_PORT="${MINIO_PORT:-9000}"

if [[ -t 1 ]]; then
    C_GREEN=$'\033[0;32m'; C_YELLOW=$'\033[0;33m'; C_RED=$'\033[0;31m'; C_OFF=$'\033[0m'
else
    C_GREEN=''; C_YELLOW=''; C_RED=''; C_OFF=''
fi

PROBLEMS=0
ok()   { printf '%s✓%s %s\n' "$C_GREEN" "$C_OFF" "$1"; }
bad()  { printf '%s✗%s %s\n' "$C_RED" "$C_OFF" "$1"; PROBLEMS=$((PROBLEMS + 1)); }
hint() { printf '    %s↳ %s%s\n' "$C_YELLOW" "$1" "$C_OFF"; }

# --- Toolchain --------------------------------------------------------------

if command -v brew >/dev/null; then
    ok "Homebrew installed"
else
    bad "Homebrew not found"; hint "Install from https://brew.sh"
fi

PHP_BIN="$(brew --prefix "$PHP_FORMULA" 2>/dev/null)/bin/php"
if [[ -x "$PHP_BIN" ]]; then
    ok "PHP $("$PHP_BIN" -r 'echo PHP_VERSION;') at $PHP_BIN"
else
    bad "$PHP_FORMULA not installed"; hint "brew install $PHP_FORMULA"
fi

# The suite fails wholesale on 8.5: HasUuidAsId uses a custom Uuid object as an
# array key, which 8.5 rejects.
if command -v php >/dev/null; then
    SYSTEM_PHP="$(php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;' 2>/dev/null)"
    if [[ "$SYSTEM_PHP" != "8.4" ]]; then
        printf '%s!%s Default `php` is %s; the just recipes pin 8.4 explicitly.\n' \
            "$C_YELLOW" "$C_OFF" "$SYSTEM_PHP"
    fi
fi

if [[ -x "$PHP_BIN" ]]; then
    # Read the module list once: `grep -q` closes the pipe early, and the
    # resulting SIGPIPE can truncate php's output on repeated invocations.
    PHP_MODULES="$("$PHP_BIN" -m 2>/dev/null)"
    MISSING=()
    for ext in pdo_pgsql fileinfo sockets zip; do
        grep -qix "$ext" <<<"$PHP_MODULES" || MISSING+=("$ext")
    done
    if [[ ${#MISSING[@]} -eq 0 ]]; then
        ok "PHP extensions: pdo_pgsql, fileinfo, sockets, zip"
    else
        bad "Missing PHP extension(s): ${MISSING[*]}"
        hint "All four are bundled with $PHP_FORMULA: brew reinstall $PHP_FORMULA"
        hint "On a non-Homebrew PHP: pecl install ${MISSING[*]}"
    fi
fi

for tool in composer node npm psql; do
    if command -v "$tool" >/dev/null; then
        ok "$tool found"
    else
        bad "$tool not found"
        case "$tool" in
            composer) hint "brew install composer" ;;
            node|npm) hint "brew install node" ;;
            psql)     hint "brew install postgresql@15, or install Postgres.app" ;;
        esac
    fi
done

# Optional: only the static-website generator needs it.
if command -v hugo >/dev/null; then
    ok "hugo found (static-website tests will run)"
else
    printf '%s!%s hugo not installed — 4 HugoStaticWebsiteGenerator tests will fail.\n' \
        "$C_YELLOW" "$C_OFF"
    hint "brew install hugo"
fi

# --- Database ---------------------------------------------------------------

if pg_isready -h 127.0.0.1 -p "$DB_PORT" >/dev/null 2>&1; then
    ok "PostgreSQL reachable on port $DB_PORT"

    for db in "$DB_NAME" "$TEST_DB_NAME"; do
        if [[ "$(psql -h 127.0.0.1 -p "$DB_PORT" -d postgres -tAc \
                "SELECT 1 FROM pg_database WHERE datname='$db'" 2>/dev/null)" == "1" ]]; then
            TABLES="$(psql -h 127.0.0.1 -p "$DB_PORT" -d "$db" -tAc \
                "SELECT count(*) FROM information_schema.tables WHERE table_schema='public'" 2>/dev/null)"
            if [[ "${TABLES:-0}" -gt 0 ]]; then
                ok "Database '$db' exists ($TABLES tables)"
            else
                bad "Database '$db' exists but has no tables"
                hint "just setup-native"
            fi
        else
            bad "Database '$db' does not exist"
            hint "just setup-native"
        fi
    done
else
    bad "PostgreSQL not reachable on 127.0.0.1:$DB_PORT"
    hint "brew services start postgresql@15, or start Postgres.app"
fi

# --- Object storage ---------------------------------------------------------

# Only a problem when the .env opts in: on the default local driver there is
# nothing to run, and a missing minio is the expected state.
if grep -qE '^FILESYSTEM_SHARED_DRIVER=s3' "$CMS_DIR/.env" 2>/dev/null; then
    if curl -sf "http://127.0.0.1:${MINIO_PORT}/minio/health/live" >/dev/null 2>&1; then
        ok "Object storage reachable on port $MINIO_PORT (FILESYSTEM_SHARED_DRIVER=s3)"
    else
        bad ".env sets FILESYSTEM_SHARED_DRIVER=s3 but nothing answers on 127.0.0.1:$MINIO_PORT"
        hint "brew services start minio, or unset FILESYSTEM_SHARED_DRIVER to use local disks"
    fi
else
    ok "Shared disks on the local filesystem (object storage not enabled)"
fi

# --- Application ------------------------------------------------------------

if [[ -f "$CMS_DIR/.env" ]]; then
    ok ".env exists"
    grep -qE '^APP_KEY=.+' "$CMS_DIR/.env" \
        && ok "APP_KEY is set" \
        || { bad "APP_KEY is empty"; hint "cd src/cms && \"\$(brew --prefix $PHP_FORMULA)/bin/php\" artisan key:generate"; }
else
    bad ".env missing"; hint "just setup-native"
fi

[[ -d "$CMS_DIR/vendor" ]] \
    && ok "composer dependencies installed" \
    || { bad "vendor/ missing"; hint "just setup-native"; }

# Without built assets the UI renders unstyled and every page 500s on the
# missing Vite manifest.
[[ -f "$CMS_DIR/public/build/manifest.json" ]] \
    && ok "frontend assets built" \
    || { bad "frontend assets not built"; hint "cd src/cms && npm run build"; }

printf '\n'
if [[ $PROBLEMS -eq 0 ]]; then
    ok "Environment looks good."
else
    printf '%s✗%s %d problem(s) found.\n' "$C_RED" "$C_OFF" "$PROBLEMS"
    exit 1
fi
