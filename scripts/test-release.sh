#!/bin/bash
set -euo pipefail

ARCHIVE="$1"

if [ ! -f "$ARCHIVE" ]; then
    echo "Error: Release archive $ARCHIVE not found"
    exit 1
fi

echo "Setting up test environment for $ARCHIVE..."

# Create test directory
TEST_DIR="test-release-$(date +%s)"
mkdir -p "$TEST_DIR"

# Extract archive
echo "Extracting $ARCHIVE..."
tar -xzf "$ARCHIVE" -C "$TEST_DIR" --strip-components=1

echo "Test environment ready in: $TEST_DIR"

# Create docker-compose.yml for testing
cd "$TEST_DIR"
cat > docker-compose.test.yml <<'EOF'
services:
  # PostgreSQL Database
  postgres:
    image: postgres:16-alpine
    restart: unless-stopped
    environment:
      POSTGRES_DB: openvwr_test
      POSTGRES_USER: openvwr
      POSTGRES_PASSWORD: secret
    volumes:
      - postgres_data:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U openvwr -d openvwr_test"]
      interval: 10s
      timeout: 5s
      retries: 5

  # PHP-FPM Service  
  php:
    image: php:8.3-fpm-bookworm
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
      - php_storage:/var/www/html/storage
      - php_cache:/var/www/html/bootstrap/cache
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - APP_KEY=base64:BjQI8jG8K3GEzOl7XkQQMTD8IUQWa2z1Y6Tr7gFN3oA=
      - DB_CONNECTION=pgsql
      - DB_HOST=postgres
      - DB_PORT=5432
      - DB_DATABASE=openvwr_test
      - DB_USERNAME=openvwr
      - DB_PASSWORD=secret
      - CACHE_DRIVER=file
      - SESSION_DRIVER=file
      - QUEUE_CONNECTION=sync
      - MAIL_MAILER=log
    depends_on:
      postgres:
        condition: service_healthy
    command: >
      bash -c "
        apt-get update &&
        apt-get install -y curl zip unzip git libpq-dev libicu-dev libzip-dev &&
        curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer &&
        docker-php-ext-configure intl &&
        docker-php-ext-install pdo pdo_pgsql intl zip sockets exif &&
        composer install --no-dev --optimize-autoloader --no-interaction &&
        php artisan key:generate --no-interaction --force &&
        php artisan config:cache &&
        until php artisan migrate:status 2>/dev/null; do echo 'Waiting for DB...'; sleep 2; done &&
        php artisan migrate --force --no-interaction &&
        chown -R www-data:www-data storage bootstrap/cache &&
        chmod -R 775 storage bootstrap/cache &&
        php-fpm
      "

  # Caddy Web Server
  caddy:
    image: caddy:2-alpine
    restart: unless-stopped
    ports:
      - "8080:80"
    volumes:
      - ./:/var/www/html
      - ./Caddyfile:/etc/caddy/Caddyfile
    depends_on:
      - php

volumes:
  postgres_data:
  php_storage:
  php_cache:
EOF

# Create Caddyfile
cat > Caddyfile <<'EOF'
:80 {
    root * /var/www/html/public
    encode gzip
    
    php_fastcgi php:9000
    
    try_files {path} /index.php?{query}
    
    header {
        X-Frame-Options DENY
        X-Content-Type-Options nosniff
        X-XSS-Protection "1; mode=block"
        Referrer-Policy strict-origin-when-cross-origin
    }
    
    @static {
        file
        path *.css *.js *.ico *.png *.jpg *.jpeg *.gif *.svg *.woff *.woff2
    }
    header @static Cache-Control "public, max-age=31536000"
    
    file_server
}
EOF

echo "Starting Docker test environment..."
docker compose -f docker-compose.test.yml up -d

echo "Waiting for services to start..."
sleep 30

# Test the application
echo "Testing application..."
if curl -f -s http://localhost:8080 > /dev/null; then
    echo "✅ Application is responding"
    
    # Get the page content to verify it looks similar to the original
    echo "Fetching homepage content..."
    curl -s http://localhost:8080 | head -20
    
else
    echo "❌ Application is not responding"
    echo "Container logs:"
    docker compose -f docker-compose.test.yml logs --tail=50
    exit 1
fi

echo "Test completed successfully!"
echo "View the application at: http://localhost:8080"
echo "To stop the test environment: docker compose -f docker-compose.test.yml down"