# Environment variables

## CMS & Worker

### Required

The following variables are required to be set before the application will run.

- `APP_KEY` application key, can be set with the `php artisan key:generate` command
- `APP_URL`, application url
- `DB_HOST` database host address
- `DB_DATABASE` database name
- `DB_USERNAME` username for database access
- `DB_PASSWORD` database password for the database user
- `STATIC_WEBSITE_BASE_URL` base-url for the static-website

### Commonly used, but not required

The following variables are optional. Defaults are used when these variables are
not set.

- `APP_ENV` environment name, (default: `production`)
- `APP_DEBUG` debug level, (default: `false`)
- `DB_PORT` database port, (default: `5432`)
- `LOG_LEVEL` log level, (default: `debug`)
- `OUTBOX_SMTP_FROM` from-address for sending e-mail, (default is not set)
- `FILESYSTEM_STATIC_WEBSITE_ROOT` root-folder for the static-website (default: `<laravel-storage-path>/app`)
- `FILESYSTEM_SHARED_STORAGE_PATH` root-folder for shared storage (default: `<laravel-storage-path>/app/shared-storage`)
- `FILESYSTEM_SHARED_DRIVER` driver for the shared disks, `local` or `s3` (default: `local`). See [object_storage.md](object_storage.md)
- `VIRUSSCANNER_DEFAULT` default virus-scanner (default: `clamav`)
- `VIRUSSCANNER_SOCKET` socket for the virus-scanner (default: `unix:///var/run/clamav/clamd.ctl`)
- `CLAMAV_SOCKET_READ_TIMEOUT` read timeout for the clamav socket (default: `30`)
- `APP_BANNER_MESSAGE` message shown in a banner at the top of every page, including the login page (default is not set, which hides the banner)
- `APP_BANNER_LEVEL` banner colour, one of `info`, `warning` or `danger` (default: `warning`)

### Complete list

The following environment variables are used to configure the application in `src/cms/config`:

**APP:**

- `APP_NAME` (default: `Verwerkingsregister`)
- `APP_ENV` (default: `production`)
- `APP_DEBUG` (default: `false`)
- `APP_URL`
- `ASSET_URL`
- `DISPLAY_TIMEZONE` (default: `Europe/Amsterdam`)
- `APP_KEY`
- `APP_BANNER_MESSAGE` (default is not set)
- `APP_BANNER_LEVEL` (default: `warning`)

**AUTH:**

- `PASSWORDLESS_TOKEN_EXPIRY_MINUTES` (default: `5`)
- `PASSWORDLESS_THROTTLE_WINDOW_SECONDS` (default: `60 * 5`)
- `PASSWORDLESS_THROTTLE_MAX_ATTEMPTS` (default: `5`)
- `ONE_TIME_PASSWORD_DRIVER` (default: `timed`)

**DATABASE:**

- `DB_CONNECTION` (default: `pgsql`)
- `DATABASE_URL`
- `DB_HOST` (default: `pgsql`)
- `DB_PORT` (default: `5432`)
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `DB_SSLMODE` (default: `prefer`)
- `DB_SSLROOTCERT` (default: `null`)
- `DB_SSLCERT` (default: `null`)
- `DB_SSLKEY` (default: `null`)
- `REDIS_CLIENT` (default: `phpredis`)
- `REDIS_CLUSTER` (default: `redis`)
- `REDIS_PREFIX` (default: `verwerkingsregister_database_`)
- `REDIS_URL`
- `REDIS_HOST` (default: `127.0.0.1`)
- `REDIS_USERNAME`
- `REDIS_PASSWORD`
- `REDIS_PORT` (default: `6379`)
- `REDIS_DB` (default: `0`)
- `REDIS_CACHE_DB` (default: `1`)

**DEBUGBAR:**

- `DEBUGBAR_ENABLED` (default: `null`)
- `DEBUGBAR_OPEN_STORAGE` (default: `false`)
- `DEBUGBAR_EDITOR` (default: `phpstorm`)
- `DEBUGBAR_THEME` (default: `auto`)

**FILAMENT:**

- `FILAMENT_FILESYSTEM_DISK` (default: `filament`)

**FILESYSTEMS:**

- `FILESYSTEM_SHARED_STORAGE_PATH` (default: `app/shared-storage`)
- `FILESYSTEM_STATIC_WEBSITE_ROOT` (default: `app/static-website`)
- `FILESYSTEM_SHARED_DRIVER` (default: `local`)

**OBJECT STORAGE** (only when `FILESYSTEM_SHARED_DRIVER=s3`):

- `AWS_ENDPOINT` endpoint of the S3-compatible service (default is not set)
- `AWS_ACCESS_KEY_ID` (default is not set)
- `AWS_SECRET_ACCESS_KEY` (default is not set)
- `AWS_DEFAULT_REGION` (default: `eu-central-1`)
- `AWS_USE_PATH_STYLE_ENDPOINT` (default: `true`)
- `UPLOADS_BUCKET` bucket for the media-library disk (default: `uploads`)
- `EXPORTS_BUCKET` bucket for the filament disk (default: `exports`)
- `TRANSFER_BUCKET` bucket for the transfer disk (default: `transfer`)

**LOGGING:**

- `LOG_CHANNEL` (default: `stderr`)
- `LOG_DEPRECATIONS_CHANNEL` (default: `null`)
- `LOG_LEVEL` (default: `debug`)

**MAIL:**

- `MAIL_MAILER` (default: `smtp`)
- `MAIL_URL`
- `OUTBOX_SMTP_HOST` (default: `mailpit`)
- `OUTBOX_SMTP_PORT` (default: `1025`)
- `OUTBOX_SMTP_ENCRYPTION` (default: `tls`)
- `OUTBOX_SMTP_USERNAME`
- `OUTBOX_SMTP_PASSWORD`
- `MAIL_EHLO_DOMAIN`
- `MAIL_SENDMAIL_PATH` (default: `/usr/sbin/sendmail -bs -i`)
- `MAIL_LOG_CHANNEL`
- `OUTBOX_SMTP_FROM`
- `MAIL_FROM_NAME` (default: `APP_NAME`)

**PUBLIC:**

- `PUBLIC_WEBSITE_BASE_URL`
- `PUBLIC_WEBSITE_BUILD_AFTER_HOOK`

**STATIC:**

- `STATIC_WEBSITE_BASE_URL` - Base URL for the static website
- `STATIC_WEBSITE_BUILD_SCRIPT` - Path to build script (default: `<project-root>/static-website/build.sh`)

**Build Script Environment Variables:**

- `HUGO_THEME` - Hugo theme to use (default: `openvwr`)
- `HUGO_OUTPUT_DIR` - Where build script writes output (default: `./public`)

**QUEUE:**

- `QUEUE_CONNECTION` (default: `database`)
- `REDIS_QUEUE` (default: `default`)
- `QUEUE_FAILED_DRIVER` (default: `database-uuids`)

**SESSION:**

- `SESSION_DRIVER` (default: `database`)
- `SESSION_LIFETIME` (default: `15`)
- `SESSION_CONNECTION`
- `SESSION_STORE`
- `SESSION_COOKIE` (default: `__HOST-verwerkingsregister_session`)
- `SESSION_DOMAIN`
- `SESSION_SECURE_COOKIE` (default: `true`)

**TRANSFER (import/export between organisations):**

- `TRANSFER_MAX_ZIPPED_NUMBER_OF_FILES` (default: `5000`)
- `TRANSFER_MAX_ZIPPED_FILESIZE` (default: `50`, in MB per file in the zip)

**VIRUSSCANNER:**

- `VIRUSSCANNER_DEFAULT` (default: `clamav`)
- `VIRUSSCANNER_SOCKET` (default: `unix:///var/run/clamav/clamd.ctl`)
- `CLAMAV_SOCKET_READ_TIMEOUT` (default: `30`)
