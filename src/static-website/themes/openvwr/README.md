# OpenVWR Theme

A minimal, neutral Hugo theme with dark mode support for OpenVWR (Open Verwerkingsregister).

## Features

- **Neutral Design**: Clean, minimal design without government-specific branding
- **Dark Mode Support**: Full dark mode with toggle and system preference detection
- **Accessible**: WCAG 2.1 AA compliant with proper contrast ratios
- **Responsive**: Mobile-first design that works on all screen sizes
- **Minimal Dependencies**: Built with vanilla CSS and JavaScript
- **Customizable**: Easy to customize colors and styling via CSS custom properties

## Installation

This theme is already integrated into the OpenVWR project. To use it, set the theme in your `hugo.yaml`:

```yaml
theme: openvwr
params:
  darkMode: true
  defaultTheme: auto  # Options: light, dark, auto
```

## Configuration

### Basic Configuration

```yaml
languageCode: nl-NL
title: Verwerkingsregister
theme: openvwr

params:
  darkMode: true        # Enable/disable dark mode toggle
  defaultTheme: auto    # Default theme: light, dark, or auto (system preference)
  description: "Your site description"
```

### Menus

Define navigation menus in your `hugo.yaml`:

```yaml
menu:
  main:
    - name: Home
      url: /
      weight: 1
    - name: Organisaties
      url: /organisatie/
      weight: 2

  footer:
    - name: Privacy
      url: /privacy/
    - name: Contact
      url: /contact/
```

## Customization

### Colors

The theme uses CSS custom properties for easy customization. Override these in your own CSS:

```css
:root {
  --color-primary: #2563eb;      /* Primary brand color */
  --color-primary-hover: #1d4ed8;
  --color-bg: #ffffff;
  --color-text: #1a1a1a;
  /* ... and more */
}

[data-theme="dark"] {
  --color-primary: #60a5fa;
  --color-bg: #1a1a1a;
  --color-text: #f5f5f5;
  /* ... and more */
}
```

### Typography

The theme uses a system font stack by default for optimal performance:

```css
--font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto',
               'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans',
               'Helvetica Neue', sans-serif;
```

You can override this in your own CSS to use custom fonts.

### Logo

The theme includes a simple document icon as the default logo. To customize:

1. Replace the SVG in `layouts/partials/logo.html`
2. Or add your own image to `static/img/logo.svg` and update the partial

## Dark Mode

### How It Works

The dark mode implementation:

1. Checks for user's saved preference in `localStorage`
2. Falls back to system preference (`prefers-color-scheme`)
3. Provides a toggle button to switch between light and dark modes
4. Persists the user's choice across sessions

### Disabling Dark Mode

To disable dark mode, set in your `hugo.yaml`:

```yaml
params:
  darkMode: false
```

## Layout Structure

```
layouts/
├── _default/
│   ├── baseof.html      # Base template
│   ├── list.html        # List pages
│   └── single.html      # Single pages
├── partials/
│   ├── head.html        # <head> section
│   ├── header.html      # Site header
│   ├── footer.html      # Site footer
│   ├── navigation.html  # Main navigation
│   ├── logo.html        # Site logo
│   ├── dark-mode-toggle.html  # Dark mode toggle button
│   └── hero-links.html  # Hero section links
└── index.html           # Homepage template
```

## Browser Support

- Chrome/Edge (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Accessibility

The theme follows WCAG 2.1 AA guidelines:

- Proper color contrast ratios (4.5:1 for text)
- Keyboard navigation support
- ARIA labels and semantic HTML
- Focus indicators for interactive elements
- Responsive text sizing

## Performance

- System fonts for zero font loading time
- Minimal CSS and JavaScript
- CSS and JS fingerprinting for cache busting
- Lazy loading for images
- Optimized image formats (WebP)

## License

This theme is part of the OpenVWR project and is licensed under EUPL-1.2.

## Contributing

Contributions are welcome! Please see the main OpenVWR repository for contribution guidelines.

## Support

For issues or questions, please open an issue in the OpenVWR repository:
https://github.com/Sourcehaven-BV/openvwr/issues
