<?php

declare(strict_types=1);

namespace App\Support;

use function in_array;
use function parse_url;
use function strtolower;
use function trim;

use const PHP_URL_HOST;
use const PHP_URL_SCHEME;

/**
 * Decides whether a free-text value may be rendered as a clickable link.
 *
 * Fields such as Document::$location hold whatever the user typed: a DMS
 * reference, a network path, or a URL. Only http(s) URLs become links; every
 * other scheme (javascript:, data:, file:, ...) stays inert text, because the
 * value is user-editable and would otherwise be an XSS vector.
 */
class ExternalLink
{
    private const ALLOWED_SCHEMES = ['http', 'https'];

    /**
     * A value is linkable only when it is an absolute http(s) URL with a host.
     * Requiring the host rejects malformed input such as "https:///" that would
     * otherwise produce an href pointing back at the CMS itself.
     */
    public static function isLinkable(?string $value): bool
    {
        $value = trim((string) $value);
        if ($value === '') {
            return false;
        }

        $scheme = parse_url($value, PHP_URL_SCHEME);
        if (!in_array(strtolower((string) $scheme), self::ALLOWED_SCHEMES, true)) {
            return false;
        }

        return (string) parse_url($value, PHP_URL_HOST) !== '';
    }
}
