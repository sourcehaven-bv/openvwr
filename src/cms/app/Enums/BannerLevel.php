<?php

declare(strict_types=1);

namespace App\Enums;

enum BannerLevel: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case DANGER = 'danger';

    /**
     * Modifier class for the banner container. The colours themselves live in the
     * panel theme (`resources/css/filament/admin/theme.css`), like the rest of the
     * app's custom styling: Tailwind only scans `app/Filament` and the Blade views,
     * so utility classes returned from here would never be compiled and the banner
     * would render as an unstyled, transparent bar.
     *
     * Colour is decoration only; the message text carries the meaning (WCAG 1.4.1).
     */
    public function cssClass(): string
    {
        return 'fi-banner--' . $this->value;
    }
}
