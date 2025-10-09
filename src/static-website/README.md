# OpenVWR Static Website

This directory contains the Hugo static website that displays published processing records (verwerkingen) from the OpenVWR CMS.

The website uses the `openvwr` theme - a minimal, neutral theme with dark mode support.

## Architecture

The static website is generated in two steps:

1. **Content Generation**: The CMS generates markdown/HTML content files in the `content/` directory
2. **Static Site Build**: Hugo processes the content with the theme to generate final HTML/CSS/JS

## Prerequisites

- Hugo v0.121.1 or higher
- The CMS must be running to generate content

## Building the Static Website

### Via CMS (Recommended)

The CMS automatically generates and builds when content is published:

```bash
cd ../cms
./vendor/bin/sail artisan static-website:refresh
./vendor/bin/sail artisan queue:work --stop-when-empty
```

### Manual Build

```bash
# Using the build script (recommended)
./build.sh /path/to/content http://localhost:8080/

# Or using Hugo directly
hugo -c ./content -d /output/path -b http://localhost:8080/ -t openvwr --cleanDestinationDir
```

### Development Server

For theme development with live reload:

```bash
hugo server -c ./content -b http://localhost:1313/
```

## Environment Variables

Configure these in the CMS `.env` file:

- `STATIC_WEBSITE_BASE_URL` - URL where site will be accessible (e.g., `http://localhost:8080`)
- `HUGO_OUTPUT_DIR` - Where Hugo outputs built files (e.g., `/var/www/html/storage/app/static-website`)
- `FILESYSTEM_STATIC_WEBSITE_ROOT` - Hugo project root (e.g., `/var/www/html/static-website`)
- `HUGO_THEME` - Theme name (default: `openvwr`)

## Theme: OpenVWR

A minimal, neutral theme with:

- Clean, accessible design
- Dark mode with localStorage persistence
- System preference detection
- Responsive layout
- Processing record list and detail pages

### Customization

Edit `themes/openvwr/assets/_variables.scss` to customize colors:

```scss
:root {
  --color-primary: #2563eb;
  --color-bg: #ffffff;
  --color-text: #1a1a1a;
}
```

## Troubleshooting

### Content not showing

1. Check that records are published (`public_from` date in past)
2. Verify content generated: `ls -la content/organisatie/`
3. Check build output for errors

### CSS not loading

1. Verify `STATIC_WEBSITE_BASE_URL` matches actual URL
2. Check nginx is serving from correct directory

## License

EUPL-1.2
