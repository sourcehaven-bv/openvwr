#!/bin/bash
# Sets up a native (non-Docker) development environment on macOS + Homebrew.
# See docs/local_development_without_docker.md for the manual equivalent.
set -euo pipefail

CMS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../src/cms" && pwd)"
PHP_FORMULA="php@8.4"
PG_FORMULA="postgresql@15"
DB_NAME="${DB_NAME:-openvwr_local}"
TEST_DB_NAME="${TEST_DB_NAME:-testing}"
DB_USER="${DB_USER:-sail}"
DB_PASSWORD="${DB_PASSWORD:-password}"
DB_PORT="${DB_PORT:-5432}"

if [[ -t 1 ]]; then
    C_BLUE=$'\033[0;34m'; C_GREEN=$'\033[0;32m'; C_YELLOW=$'\033[0;33m'
    C_RED=$'\033[0;31m'; C_OFF=$'\033[0m'
else
    C_BLUE=''; C_GREEN=''; C_YELLOW=''; C_RED=''; C_OFF=''
fi

info()  { printf '%s→%s %s\n' "$C_BLUE" "$C_OFF" "$1"; }
ok()    { printf '%s✓%s %s\n' "$C_GREEN" "$C_OFF" "$1"; }
warn()  { printf '%s!%s %s\n' "$C_YELLOW" "$C_OFF" "$1"; }
fail()  { printf '%s✗%s %s\n' "$C_RED" "$C_OFF" "$1" >&2; exit 1; }

[[ "$(uname -s)" == "Darwin" ]] || fail "This script targets macOS. See docs/local_development_without_docker.md for other platforms."
command -v brew >/dev/null || fail "Homebrew not found. Install it from https://brew.sh"

# --- Dependencies -----------------------------------------------------------

install_formula() {
    local formula="$1"
    if brew list --formula "$formula" >/dev/null 2>&1; then
        ok "$formula already installed"
    else
        info "Installing $formula..."
        brew install "$formula"
    fi
}

install_formula "$PHP_FORMULA"
install_formula node

# PostgreSQL may also be provided by Postgres.app, which is not a brew formula.
if pg_isready -h 127.0.0.1 -p "$DB_PORT" >/dev/null 2>&1; then
    ok "PostgreSQL already reachable on port $DB_PORT"
else
    install_formula "$PG_FORMULA"
    info "Starting $PG_FORMULA..."
    brew services start "$PG_FORMULA" >/dev/null
    for _ in $(seq 1 30); do
        pg_isready -h 127.0.0.1 -p "$DB_PORT" >/dev/null 2>&1 && break
        sleep 1
    done
    pg_isready -h 127.0.0.1 -p "$DB_PORT" >/dev/null 2>&1 \
        || fail "PostgreSQL did not become reachable on port $DB_PORT."
fi

PHP_BIN="$(brew --prefix "$PHP_FORMULA")/bin/php"
[[ -x "$PHP_BIN" ]] || fail "PHP binary not found at $PHP_BIN"

# composer is not pinned to a PHP version; run it through the 8.4 binary.
COMPOSER_BIN="$(command -v composer || true)"
[[ -n "$COMPOSER_BIN" ]] || fail "composer not found. Run: brew install composer"

ok "Using PHP $("$PHP_BIN" -r 'echo PHP_VERSION;')"

check_extensions() {
    local missing=()
    local ext
    # Read the module list once: `grep -q` closes the pipe early, and the
    # resulting SIGPIPE can truncate php's output on repeated invocations.
    local modules
    modules="$("$PHP_BIN" -m 2>/dev/null)"
    for ext in pdo_pgsql fileinfo sockets zip; do
        grep -qix "$ext" <<<"$modules" || missing+=("$ext")
    done

    [[ ${#missing[@]} -eq 0 ]] && { ok "PHP extensions present: pdo_pgsql, fileinfo, sockets, zip"; return 0; }

    # All four are compiled into Homebrew's php@8.4, so a miss means the wrong
    # binary or a broken install rather than something pecl can fix.
    warn "Missing PHP extension(s): ${missing[*]}"
    warn "These ship with Homebrew's $PHP_FORMULA. Try: brew reinstall $PHP_FORMULA"
    warn "If you use a different PHP build, install them with: pecl install ${missing[*]}"
    return 1
}

check_extensions || fail "Cannot continue without the required PHP extensions."

# --- Database ---------------------------------------------------------------

psql_admin() { psql -h 127.0.0.1 -p "$DB_PORT" -d postgres -tAc "$1"; }

if [[ "$(psql_admin "SELECT 1 FROM pg_roles WHERE rolname='$DB_USER'")" == "1" ]]; then
    ok "Role '$DB_USER' exists"
else
    info "Creating role '$DB_USER'..."
    psql_admin "CREATE ROLE $DB_USER LOGIN PASSWORD '$DB_PASSWORD' SUPERUSER;" >/dev/null
fi

create_database() {
    local name="$1"
    if [[ "$(psql_admin "SELECT 1 FROM pg_database WHERE datname='$name'")" == "1" ]]; then
        ok "Database '$name' exists"
    else
        info "Creating database '$name'..."
        psql_admin "CREATE DATABASE $name OWNER $DB_USER;" >/dev/null
    fi
}

create_database "$DB_NAME"
# phpunit.xml.dist pins DB_DATABASE=testing, so the suite needs its own database.
create_database "$TEST_DB_NAME"

# --- Application ------------------------------------------------------------

cd "$CMS_DIR"

if [[ -f .env ]]; then
    ok ".env already exists (left untouched)"
else
    info "Creating .env from .env.nodocker.example..."
    cp .env.nodocker.example .env
fi

info "Installing composer dependencies..."
"$PHP_BIN" "$COMPOSER_BIN" install --no-interaction

# APP_KEY must exist before seeding: otp_secret is encrypted with it, and
# rotating the key afterwards makes existing secrets undecryptable.
if grep -qE '^APP_KEY=.+' .env; then
    ok "APP_KEY already set"
else
    info "Generating APP_KEY..."
    "$PHP_BIN" artisan key:generate
fi

info "Installing npm dependencies and building assets..."
npm install --silent
npm run build

info "Running migrations..."
"$PHP_BIN" artisan migrate --force

# The suite uses DatabaseTransactions, not RefreshDatabase, so the test
# database needs its schema up front.
info "Migrating the '$TEST_DB_NAME' database..."
DB_DATABASE="$TEST_DB_NAME" "$PHP_BIN" artisan migrate --force

USER_COUNT="$("$PHP_BIN" artisan tinker --execute='echo App\Models\User::count();' 2>/dev/null | tail -1 | tr -dc '0-9')"
if [[ "${USER_COUNT:-0}" -eq 0 ]]; then
    info "Seeding test data..."
    "$PHP_BIN" artisan db:seed --class=TestDataSeeder --force
else
    ok "Database already has $USER_COUNT users (skipping seed)"
fi

printf '\n'
ok "Setup complete."
printf '\n  Start the app:  just dev-native\n  Login link:     just dev-native-login\n\n'
