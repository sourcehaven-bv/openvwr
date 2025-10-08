# OpenVWR

## Attribution

This project is a fork of [nl-rdo-dataprocessing-register](https://github.com/minvws/nl-rdo-dataprocessing-register) by iRealisatie - Ministerie van Volksgezondheid, Welzijn en Sport (Dutch Ministry of Health, Welfare and Sport). Licensed under [EUPL-1.2](LICENSES/EUPL-1.2.txt).

See [CREDITS.md](CREDITS.md) for full attribution details.

## Introduction

This repository contains OpenVWR (Open Data Processing Register). This project has 2 main components:

**CMS**

The CMS is built using [Laravel](https://laravel.com/). This is where all the data for the processing records (verwerkingen) are kept and maintained.

Directory: `/src/cms/`

**Static website**

This contains the configuration for generating a static website using [Hugo](https://gohugo.io/). It uses JSON and markdown data as its input to generate static html files.

Directory: `/src/static-website/`

## Documentation

- See [docs/environment_variables.md](docs/environment_variables.md) for an overview of all environment variables that can be set in the `.env` file.
- See [docs/roles_and_permissions.md](docs/roles_and_permissions.md) for an overview of all roles and permissions and the location where they are configured.
- See [docs/static_website_hugo.md](docs/static_website_hugo.md) for detailed information about the Hugo static website publishing system.

## Getting started

### Prerequisites

-   An up-to-date [Docker (Desktop)](https://www.docker.com/products/docker-desktop/) installation
-   [just](https://github.com/casey/just) command runner (optional but recommended)

### Quick Setup (Recommended)

If you have [just](https://github.com/casey/just) installed, you can set up the entire development environment with a single command:

```bash
just setup
```

This will:
1. Create the `.env` file from `.env.example`
2. Install composer dependencies via Docker
3. Build and start Docker containers
4. Generate application key
5. Create testing database
6. Run migrations and seeders for both main and testing databases

After setup completes, start the environment with:

```bash
just dev-up
```

See all available commands with:

```bash
just --list
```

### Manual Setup

If you prefer to set up manually or don't have `just` installed:

#### Setup CMS

1. Open a new terminal at `/src/cms`
2. Create an `.env` file by copying the `./.env.template` to `./.env` and optionally set the `SESSION_DRIVER` to `file`
3. Setup docker using laravel/sail by running:

    ```
    docker run --rm \
        -u "$(id -u):$(id -g)" \
        -v "$(pwd):/var/www/html" \
        -w /var/www/html \
        laravelsail/php82-composer:latest \
        composer install --ignore-platform-reqs
    ```

    For more information see: https://laravel.com/docs/10.x/sail#installing-composer-dependencies-for-existing-projects

    (The steps below assume you have an alias for `./vendor/bin/sail`)

4. Start the container by running `sail up -d`
5. Run `sail artisan key:generate` to generate a new application key
6. Run `sail artisan migrate:fresh --seed` to (re)run all migrations and default seeder

As a result of these steps, you have created your local docker working directory, a database and seeded it with a user.

### Setup Public website

We now need the Public website script to build the static files within your container.

1. Open the shell with `sail shell`
2. Run `npm ci` (NPM clean install) to install the required dependencies. If you visit your local website (in your browser) you should see a warning that says something like `Vite manifest not found at: /var/www/html/public/build/manifest.json`.
3. Run `npm run build` (within the shell) to build the static files. This will generate the static files in the `public` folder.

As a result of these steps, you have created the static files for the public website and in your browser you can see the Login page.

### Login to the CMS

1. Visit the project in your browser
2. Login with the following credentials:
    - Email: `admin@example.com` (this user is added with the TestDataSeeder)
3. Open your local Mailpit instance to see the email that is send
4. Click on the link in the email to login
5. Add the 2FA code which you do not have

#### 2FA options

To be able to login, you have three options:

A. Set ENV variable `ONE_TIME_PASSWORD_DRIVER` to `fake` in your `.env` file
   1. Open the `.env` file
   2. Add `ONE_TIME_PASSWORD_DRIVER=fake` to the file
   3. Visit your local default project url again and use a random 6-digit code for 2FA

B. Disable 2FA for the added user
  1. `sail shell` to enter the Shell
  2. `php artisan app:user-disable-otp`
  3. add the email again and press Enter
  4. Visit your local default project url again and you are now logged in

C. Create a new admin user with 2FA disabled
   1. `sail shell` to enter the Shell
   2. `php artisan user:create-admin`
   3. add the name and desired (fake) email you want to use to login
   4. Visit your local default project url again and login (with the email you just added)

Note: to actually use the CMS, you must have 2FA activated.

## Development Commands (with just)

Common development tasks can be run using `just`:

```bash
# Start development environment
just dev-up

# Stop development environment
just dev-down

# Open shell in container
just dev-shell

# View logs
just dev-logs

# Build frontend assets
just dev-build

# Run all tests
just test

# Run specific test
just test-filter HugoStaticWebsiteGeneratorTest

# Run tests with coverage
just test-coverage
```

## Deployment and Production Setup

### System Requirements

**PHP Requirements:**
- PHP 8.4+
- Extensions: fileinfo, pdo, sockets, zip
- exiftool must be available on the server

**External Tools for Public Website:**
- Hugo **extended** version 0.121.1+ ([installation guide](https://gohugo.io/installation/))
- Dart sass version 1.69.5+ ([installation guide](https://sass-lang.com/install/))

### Initial Production Setup

1. **Database Setup:**
   - Process migrations in `database/sql` to create database tables
   - Create admin user: `php artisan user:create-admin`

2. **Shared Storage:**
   - Create shared storage directories that will be shared across releases
   - Set the path via `FILESYSTEM_SHARED_STORAGE_PATH` environment variable

3. **Queue Worker:**
   - Start a worker process that runs continuously: `php artisan queue:work --queue=high,default,low`
   - The worker needs all the same environment variables as the main application
   - Workers should be restarted after each deployment to load new code

4. **Cron Job:**
   - Set up a cron job that runs every minute: `php artisan schedule:run`
   - See [Laravel scheduling documentation](https://laravel.com/docs/10.x/scheduling#running-the-scheduler)

5. **Storage Link (Test Environment):**
   - Run `php artisan storage:link` to make storage accessible

### Post-Deployment Steps

After each deployment, follow these steps:

1. **Clear Caches:** `php artisan optimize:clear`
2. **Restart Workers:** Restart all worker processes to load new code
3. **Rebuild Websites:**
   - `php artisan public-website:refresh`
   - `php artisan static-website:refresh`

### Environment Variables

Key environment variables for production deployment:

- `FILESYSTEM_SHARED_STORAGE_PATH` - Path to shared storage directory
- `PUBLIC_WEBSITE_BASE_URL` - Base URL for the public website (can be relative path like `/public/subfolder` or `.` for root)
- `PUBLIC_WEBSITE_BUILD_AFTER_HOOK` - Command/script to run after building static site
- `STATIC_WEBSITE_BASE_URL` - Base URL for the static website
- `STATIC_WEBSITE_BUILD_AFTER_HOOK` - Command/script to run after building static site
- `FILESYSTEM_PUBLIC_WEBSITE_ROOT` - Directory where static website is built
- `FILESYSTEM_STATIC_WEBSITE_ROOT` - Directory for static website files (default: `./storage/app/static-website`)

**SMTP Configuration (for email):**
- `OUTBOX_SMTP_HOST`
- `OUTBOX_SMTP_PORT` (optional, defaults to 1025)
- `OUTBOX_SMTP_USERNAME`
- `OUTBOX_SMTP_PASSWORD`
- `OUTBOX_SMTP_ENCRYPTION` (optional, defaults to `tls`)
- `OUTBOX_SMTP_FROM`

**Virus Scanning:**
- `VIRUSSCANNER_SOCKET` - Path to ClamAV socket (default: `unix:///var/run/clamav/clamd.ctl`)

See [docs/environment_variables.md](docs/environment_variables.md) for complete list of environment variables.

### Local CI checks

The current CI workflow consists of static code analysis and automated tests. The latter requires a local 'testing' database.
You can use the testing database (which is available by default), which requires to run the migrations there:

1. Bash into the sail-container: `php artisan sail`
2. Run `DB_DATABASE=testing php artisan migrate:fresh --seed` to (re)run all migrations and default seeders
3. Run the test: `php artisan test` (optionally with the `--coverage` parameter)

#### Alternative
Execute the following bin script to run all CI checks: `./bin/ci-local`

## Workflows

-   ci.yml
    -   Continuous integration workflow with code analysis and automated tests
