<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

use function array_map;
use function array_unshift;
use function nl2br;
use function preg_replace;
use function sprintf;
use function str_replace;
use function str_split;

class StringService
{
    public static function mailSafe(?string $input): ?string
    {
        return preg_replace('/[^a-zA-Z0-9\s]/', '', (string) $input);
    }

    public static function toSingleLineEscapedString(?string $input, string $whenEmpty = ''): string
    {
        if ($input === null || Str::length($input) === 0) {
            return $whenEmpty;
        }

        $markdownCharacters = str_split('`*_{}[]()#+-.!>~');
        $replacementCharacters = array_map(static function (string $markdownCharacter): string {
            return sprintf('\%s', $markdownCharacter);
        }, $markdownCharacters);

        // make sure backslash is replaced with a double backslash before(!) the other replacements
        array_unshift($markdownCharacters, '\\');
        array_unshift($replacementCharacters, '\\\\');

        return nl2br(str_replace($markdownCharacters, $replacementCharacters, $input));
    }
}
