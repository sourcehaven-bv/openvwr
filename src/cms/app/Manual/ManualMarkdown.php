<?php

declare(strict_types=1);

namespace App\Manual;

use App\ValueObjects\Markdown;

use function preg_replace;
use function preg_replace_callback;
use function str_contains;
use function trim;

/**
 * Renders manual markdown to html.
 *
 * The manual text uses two constructs the plain markdown renderer does not
 * know about, both kept because they read well in the source:
 *
 *   > **Hint**: ... becomes a styled callout
 *   > **Let op**: ... becomes a styled warning callout
 *
 * Figures are written as normal markdown images and get a caption from the
 * alt text. Cross references between topics are plain markdown links to an
 * anchor, so they survive both here and in a plain text reading of the source.
 */
final class ManualMarkdown
{
    private const HINT = 'Hint';
    private const WARNING = 'Let op';

    public static function render(string $markdown): string
    {
        $html = Markdown::fromString($markdown)->toHtml();

        $html = self::callouts($html);
        $html = self::statuses($html);

        return self::figures($html);
    }

    /**
     * A code span written as `status:<kind>:<label>` becomes a coloured status
     * marker. The pdf used \textcolor for this; a code span keeps the source
     * readable and, unlike raw html, does not require loosening the sanitiser
     * that keeps the rest of the manual text safe.
     */
    private static function statuses(string $html): string
    {
        return (string) preg_replace(
            '#<code>status:([a-z]+):([^<]*)</code>#',
            '<span class="manual-status manual-status--$1">$2</span>',
            $html,
        );
    }

    /**
     * A blockquote whose first strong element is "Hint" or "Let op" becomes a
     * callout. The label itself is dropped from the body because the callout
     * renders its own heading.
     */
    private static function callouts(string $html): string
    {
        return (string) preg_replace_callback(
            '#<blockquote>\s*(.*?)\s*</blockquote>#s',
            static function (array $matches): string {
                $body = $matches[1];

                if (str_contains($body, '<strong>' . self::HINT . '</strong>')) {
                    return self::callout('hint', self::HINT, $body, self::HINT);
                }

                if (str_contains($body, '<strong>' . self::WARNING . '</strong>')) {
                    return self::callout('warning', self::WARNING, $body, self::WARNING);
                }

                return '<blockquote>' . $body . '</blockquote>';
            },
            $html,
        );
    }

    private static function callout(string $kind, string $title, string $body, string $label): string
    {
        $body = (string) preg_replace('#<strong>' . $label . '</strong>\s*:?\s*#', '', $body);

        return '<div class="manual-callout manual-callout--' . $kind . '">'
            . '<p class="manual-callout__title">' . $title . '</p>'
            . trim($body)
            . '</div>';
    }

    /**
     * A paragraph holding nothing but an image becomes a figure, with the alt
     * text repeated as the caption. That keeps the caption visible on the web
     * the way it was visible under the image in the pdf.
     */
    private static function figures(string $html): string
    {
        return (string) preg_replace(
            '#<p>(<img src="([^"]*)" alt="([^"]*)"\s*/?>)</p>#',
            '<figure class="manual-figure">$1<figcaption>$3</figcaption></figure>',
            $html,
        );
    }
}
