# OpenVWR Justfile
# Use `just --list` to see available commands

# Default recipe when just is called without arguments
default:
    @just --list

# Create a production release archive of the CMS
release version="dev":
    ./scripts/create-release.sh {{version}}

# Extract and test a release archive
test-release archive:
    ./scripts/test-release.sh {{archive}}

# Show test environment logs
test-logs:
    docker compose -f docker-compose.test.yml logs --tail=50

# Stop test environment
test-stop:
    docker compose -f docker-compose.test.yml down -v
    
# Clean up test releases
clean-test:
    rm -rf test-release-*
    docker compose -f docker-compose.test.yml down -v --remove-orphans 2>/dev/null || true

# Development Setup
# ==================

# Complete initial setup for local development
setup: setup-env setup-deps setup-docker setup-app
    @echo "✅ Setup complete! Run 'just dev-up' to start the environment."

# Setup environment file
setup-env:
    @echo "📝 Setting up environment file..."
    @if [ ! -f src/cms/.env ]; then \
        cp src/cms/.env.example src/cms/.env; \
        echo "✅ .env file created from .env.example"; \
    else \
        echo "ℹ️  .env file already exists"; \
    fi

# Install composer dependencies using Docker
setup-deps:
    @echo "📦 Installing composer dependencies..."
    @if [ ! -f src/cms/vendor/bin/sail ]; then \
        cd src/cms && docker run --rm \
            -u "$$(id -u):$$(id -g)" \
            -v "$$(pwd):/var/www/html" \
            -w /var/www/html \
            laravelsail/php84-composer:latest \
            composer install --ignore-platform-reqs \
        && echo "✅ Dependencies installed"; \
    else \
        echo "ℹ️  Dependencies already installed"; \
    fi

# Build Docker images and start containers
setup-docker:
    @echo "🐳 Building Docker images and starting containers..."
    cd src/cms && ./vendor/bin/sail up -d
    @echo "⏳ Waiting for database to be ready..."
    @sleep 5
    @echo "✅ Docker containers started"

# Setup application (key, database, testing db)
setup-app:
    @echo "🔑 Generating application key..."
    cd src/cms && ./vendor/bin/sail artisan key:generate
    @echo "🗄️  Creating testing database..."
    @cd src/cms && ./vendor/bin/sail exec pgsql psql -U sail -d postgres -c "CREATE DATABASE testing;" 2>/dev/null || echo "ℹ️  Testing database already exists"
    @echo "📊 Running migrations and seeders for main database..."
    cd src/cms && ./vendor/bin/sail artisan migrate:fresh --seed
    @echo "📊 Running migrations for testing database..."
    cd src/cms && DB_DATABASE=testing ./vendor/bin/sail artisan migrate:fresh --seed
    @echo "✅ Application setup complete"

# Development Commands
# ====================

# Start development environment
dev-up:
    cd src/cms && ./vendor/bin/sail up -d

# Stop development environment
dev-down:
    cd src/cms && ./vendor/bin/sail down

# Open shell in the application container
dev-shell:
    cd src/cms && ./vendor/bin/sail shell

# Reset the development environment
dev-reset:
    cd src/cms && composer run reset

# Print a passwordless login link for local dev (default: admin@example.com)
login-link email="admin@example.com":
    cd src/cms && php artisan dev:login-link --email={{email}}

# Native Development (no Docker)
# ==============================
# macOS + Homebrew only. See docs/local_development_without_docker.md.

# Install dependencies, create the database, and seed test data
setup-native:
    ./scripts/setup-local-dev.sh

# Check the native environment and report what is missing
doctor-native:
    ./scripts/check-local-dev.sh

# Serve the application natively on http://127.0.0.1:8000
dev-native port="8000":
    cd src/cms && "$(brew --prefix php@8.4)/bin/php" artisan serve --host=127.0.0.1 --port={{port}}

# Print a magic-link to log in (defaults to admin@example.com), pinned to PHP 8.4
dev-native-login email="admin@example.com":
    cd src/cms && "$(brew --prefix php@8.4)/bin/php" artisan dev:login-link --email={{email}}

# Run the test suite natively (needs PHP 8.4; 8.5 fails on UUID casts)
test-native +args="":
    cd src/cms && "$(brew --prefix php@8.4)/bin/php" -d memory_limit=4G ./vendor/bin/pest {{args}}

# Rebuild the native database from scratch and reseed
dev-native-reset:
    cd src/cms && "$(brew --prefix php@8.4)/bin/php" artisan migrate:fresh --force \
        && "$(brew --prefix php@8.4)/bin/php" artisan db:seed --class=TestDataSeeder --force

# Build frontend assets
dev-build:
    @echo "🎨 Building frontend assets..."
    cd src/cms && ./vendor/bin/sail shell -c "npm ci && npm run build"

# Run tests
test:
    cd src/cms && ./vendor/bin/sail exec laravel.test ./vendor/bin/pest

# Run specific test
test-filter filter:
    cd src/cms && ./vendor/bin/sail exec laravel.test ./vendor/bin/pest --filter {{filter}}

# Run tests with coverage
test-coverage:
    cd src/cms && ./vendor/bin/sail exec laravel.test ./vendor/bin/pest --coverage

# View logs
dev-logs:
    cd src/cms && ./vendor/bin/sail logs -f

# Build the static website
build-static:
    cd src/cms && ./vendor/bin/sail artisan static-website:refresh

# Open the static website in browser
open-static:
    open http://localhost:8080

# Code Quality & CI
# ==================

# Run all CI checks (code style, static analysis, tests)
ci: ci-style ci-phpstan ci-phpmd test
    @echo "✅ All CI checks passed!"

# Fix code style issues automatically
ci-fix:
    @echo "🔧 Fixing code style issues..."
    cd src/cms && ./vendor/bin/phpcbf
    @echo "✅ Code style fixed"

# Check code style (phpcs)
ci-style:
    @echo "🎨 Checking code style..."
    cd src/cms && ./vendor/bin/phpcs -n

# Run static analysis (phpstan)
ci-phpstan:
    @echo "🔍 Running static analysis..."
    cd src/cms && ./vendor/bin/phpstan analyse

# Run mess detector (phpmd)
ci-phpmd:
    @echo "🔍 Running mess detector..."
    cd src/cms && ./vendor/bin/phpmd app github ./phpmd.xml

# Run all checks and auto-fix what can be fixed
ci-check: ci-fix ci-phpstan ci-phpmd test
    @echo "✅ All checks complete!"
