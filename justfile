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

# Development commands
dev-up:
    cd src/cms && ./vendor/bin/sail up -d

dev-down:
    cd src/cms && ./vendor/bin/sail down

dev-shell:
    cd src/cms && ./vendor/bin/sail shell

dev-reset:
    cd src/cms && composer run reset

dev-logs:
    cd src/cms && ./vendor/bin/sail logs -f

# Build the static website
build-static:
    cd src/cms && ./vendor/bin/sail artisan static-website:refresh

# Open the static website in browser
open-static:
    open http://localhost:8080