# Static Website Publishing with Hugo

## Overview

The OpenVWR system automatically generates and publishes a static website using [Hugo](https://gohugo.io/). The CMS contains the source data for processing records (verwerkingen), which are converted to static HTML files for public access.

## Architecture

The static website publishing process consists of three chained jobs that execute sequentially:

1. **ContentGeneratorJob** - Generates Hugo-compatible content files (JSON/Markdown) from CMS data
2. **HugoWebsiteGeneratorJob** - Runs Hugo to build static HTML from the content files
3. **StaticWebsiteCheckJob** - Verifies the deployment succeeded

These jobs are orchestrated by `BuildHandler.php:24` and triggered automatically when relevant data changes in the CMS.

## How It Works

### Step 1: Content Generation

The CMS data is converted to Hugo-compatible formats:
- Content files are written to the `static-content/` folder
- Files are managed through `HugoFilesystem.php`
- Supports both JSON and Markdown formats

### Step 2: Hugo Build Process

The CMS calls the build script `src/static-website/build.sh` with the following parameters:

```bash
./build.sh <content-path> <base-url>
```

The build script determines where to write output files and which theme to use, then executes Hugo:

```bash
hugo -c <content-path> -d <destination-path> -b <base-url> -t <theme> --cleanDestinationDir
```

**Script Parameters:**
1. Content directory path (from CMS content storage)
2. Base URL for the site (from `STATIC_WEBSITE_BASE_URL`)

**Build Script Configuration:**
The build script itself determines:
- **Output location**: Via `HUGO_OUTPUT_DIR` env var (defaults to `./public`)
- **Theme**: Via `HUGO_THEME` env var (defaults to `rijkshuisstijl`)

**Hugo Project Structure:**
- Location: `/src/static-website/`
- Build script: `build.sh`
- Configuration: `hugo.yaml`
- Theme: `themes/rijkshuisstijl/` (default)
- Assets: `assets/css/hugo-pagination.scss`

### Step 3: Deployment Check

After the build completes:
- The build script can optionally perform post-build actions (deployment, optimization, etc.)
- Deployment verification jobs are scheduled at intervals: 1, 2, 3, 5, and 10 minutes
- Health checks verify the site is accessible at the configured URL

## Configuration

### Environment Variables

**Required:**
- `STATIC_WEBSITE_BASE_URL` - Base URL for the static website (e.g., `https://example.com`)

**Optional:**
- `STATIC_WEBSITE_BUILD_SCRIPT` - Path to the build script (default: `<project-root>/static-website/build.sh`)
- `STATIC_WEBSITE_THEME` - Theme to use for the static website (default: `rijkshuisstijl`)
- `STATIC_WEBSITE_CHECK_BASE_URL` - URL to check for deployment verification (defaults to `STATIC_WEBSITE_BASE_URL`)
- `STATIC_WEBSITE_CHECK_PROXY` - Proxy to use for deployment checks
- `STATIC_WEBSITE_GENERATOR` - Generator to use (default: `hugo`, options: `hugo`, `fake`)
- `STATIC_WEBSITE_FILESYSTEM` - Filesystem driver to use (default: `hugo`, options: `hugo`, `fake`)
- `FILESYSTEM_STATIC_WEBSITE_ROOT` - Root directory for static website files (default: `storage/app/static-website`)

**Deprecated:**
- `STATIC_WEBSITE_BUILD_AFTER_HOOK` - Command to run after successful build. Use the build script for post-build actions instead.

### Config File

The static website is configured in `src/cms/config/static-website.php`:

```php
return [
    'static_website_generator' => env('STATIC_WEBSITE_GENERATOR', 'hugo'),
    'hugo_filesystem_disk' => 'static-website',
    'hugo_content_folder' => 'static-content',
    'static_website_folder' => 'static-website',
    'build_script_path' => env('STATIC_WEBSITE_BUILD_SCRIPT', base_path('static-website/build.sh')),
    'theme' => env('STATIC_WEBSITE_THEME', 'rijkshuisstijl'),
    'base_url' => env('STATIC_WEBSITE_BASE_URL'),
    'plan-check-job-delays' => [1, 2, 3, 5, 10], // minutes
];
```

## Manual Rebuild

To manually trigger a static website rebuild:

```bash
php artisan static-website:refresh
# or with Sail
./vendor/bin/sail artisan static-website:refresh
# or with just
just build-static
```

This command should be run:
- After deployment to production
- When content changes aren't automatically detected
- After configuration changes

## System Requirements

**Hugo Requirements:**
- Hugo **extended** version 0.121.1 or higher
- Installation: https://gohugo.io/installation/

**Dart Sass Requirements:**
- Dart Sass version 1.69.5 or higher
- Installation: https://sass-lang.com/install/

Both tools must be available in the system PATH for the build process to work.

## Local Development with Docker

For local development, the docker-compose setup includes an nginx web server that serves the generated static site:

**Starting the environment:**
```bash
cd src/cms
./vendor/bin/sail up -d
# or
just dev-up
```

**Access the services:**
- CMS: http://localhost
- Static website: http://localhost:8080
- Mailpit: http://localhost:8025

**Build the static site:**
```bash
./vendor/bin/sail artisan static-website:refresh
# or
just build-static
```

**View logs:**
```bash
./vendor/bin/sail logs -f
# or
just dev-logs
```

**Configuration:**
The static website port can be changed via the `STATIC_WEBSITE_PORT` environment variable (default: 8080).

## Deployment Workflow

### Automatic Deployment

1. Content changes in CMS trigger a rebuild event
2. `BuildHandler` chains the three jobs
3. Content is generated from database
4. Hugo builds static HTML files
5. Post-build hook executes (if configured)
6. Deployment checks verify accessibility

### Customizing the Build Script

The build script at `src/static-website/build.sh` can be customized to add post-build actions:

```bash
#!/bin/bash
set -e

# ... (Hugo build happens here) ...

# Add your custom post-build steps:
# - Copy files to a web server
# - Sync to CDN/cloud storage
# - Trigger cache invalidation
# - Run additional deployment scripts

# Example: Deploy to remote server
# rsync -av "${DESTINATION_PATH}/" user@webserver:/var/www/html/

# Example: Upload to S3
# aws s3 sync "${DESTINATION_PATH}/" s3://my-bucket/ --delete

exit 0
```

Alternatively, you can create your own build script and set `STATIC_WEBSITE_BUILD_SCRIPT` to point to it.

## File Locations

**Hugo Project:**
- Source: `/src/static-website/`
- Config: `/src/static-website/hugo.yaml`
- Theme: `/src/static-website/themes/rijkshuisstijl/`

**Generated Content:**
- Content files: `storage/app/static-content/`
- Built website: `storage/app/static-website/`

**CMS Code:**
- Job: `src/cms/app/Jobs/StaticWebsite/HugoWebsiteGeneratorJob.php`
- Generator: `src/cms/app/Services/StaticWebsite/HugoStaticWebsiteGenerator.php`
- Filesystem: `src/cms/app/Services/StaticWebsite/HugoFilesystem.php`
- Build Handler: `src/cms/app/Listeners/StaticWebsite/BuildHandler.php`

## Troubleshooting

### Build Failures

If the Hugo build fails:
1. Check Hugo is installed and accessible: `hugo version`
2. Check Dart Sass is installed: `sass --version`
3. Review logs in Laravel log files
4. Verify `STATIC_WEBSITE_BASE_URL` is set correctly
5. Check file permissions on content and destination directories

### Deployment Verification Issues

If deployment checks fail:
- Verify the website is accessible at `STATIC_WEBSITE_CHECK_BASE_URL`
- Check proxy configuration if using `STATIC_WEBSITE_CHECK_PROXY`
- Review scheduled job logs for check results

### Queue Workers

The build process runs through Laravel queues. Ensure queue workers are running:
```bash
php artisan queue:work --queue=high,default,low
```

Remember to restart workers after deployments to load new code.
