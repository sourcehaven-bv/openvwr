<?php

declare(strict_types=1);

use App\Services\StringService;

test('it removes all special characters and keeps alphanumeric and whitespace', function (): void {
    $input = 'Hello World! This is test #123 (v2.0)';
    $expected = 'Hello World This is test 123 v20';

    expect(StringService::mailSafe($input))
        ->toBe($expected);
});

test('it returns the original string if only allowed characters are present', function (): void {
    $input = 'Data Set 2024 Alpha';

    expect(StringService::mailSafe($input))
        ->toBe($input);
});

test('it returns an empty string when only special characters are present', function (): void {
    $input = '!@#$%^&*(){}[].,';
    $expected = '';

    expect(StringService::mailSafe($input))
        ->toBe($expected);
});

test('it handles an empty string input correctly', function (): void {
    $input = '';
    $expected = '';

    expect(StringService::mailSafe($input))
        ->toBe($expected);
});

test('it handles null input by returning an empty string', function (): void {
    $input = null;
    $expected = '';

    expect(StringService::mailSafe($input))
        ->toBe($expected);
});

test('it correctly preserves spaces, tabs, and newlines', function (): void {
    $input = "Item 1\nItem 2\tItem 3!@#";
    $expected = "Item 1\nItem 2\tItem 3";

    expect(StringService::mailSafe($input))
        ->toBe($expected);
});

test('it handles a complex string with mixed cases and numbers', function (): void {
    $input = 'Verwerking Xyz789-abc456-1A (Concept)';
    $expected = 'Verwerking Xyz789abc4561A Concept';

    expect(StringService::mailSafe($input))
        ->toBe($expected);
});

test('escaped_markdown correctly escapes all standard markdown characters', function (): void {
    $input = 'This *is* **bold** and _italic_ text, with a [link](url), a #heading, a list item - and a blockquote > and code `snippet`.';
    $expected = 'This \*is\* \*\*bold\*\* and \_italic\_ text, with a \[link\]\(url\), a \#heading, a list item \- and a blockquote \> and code \`snippet\`\.';

    expect(StringService::toSingleLineEscapedString($input))
        ->toBe($expected);
});

test('escaped_markdown correctly escapes the backslash character itself', function (): void {
    $input = 'This is a path: C:\Users\Docs.';
    $expected = 'This is a path: C:\\\\Users\\\\Docs\.';

    expect(StringService::toSingleLineEscapedString($input))
        ->toBe($expected);
});

test('escaped_markdown handles mixed text with different characters', function (): void {
    $input = 'Special:!*_()#+-.[]{}` and @$%&/?';
    $expected = 'Special:\!\*\_\(\)\#\+\-\.\[\]\{\}\` and @$%&/?';

    expect(StringService::toSingleLineEscapedString($input))
        ->toBe($expected);
});

test('escaped_markdown returns an empty string when given null', function (): void {
    $input = null;
    $expected = '';

    expect(StringService::toSingleLineEscapedString($input))
        ->toBe($expected);
});

test('escaped_markdown returns an empty string when given an empty string', function (): void {
    $input = '';
    $expected = '';

    expect(StringService::toSingleLineEscapedString($input))
        ->toBe($expected);
});

test('escaped_markdown handles text already containing backslashes correctly', function (): void {
    $input = 'An unescaped *asterisk* and a path C:\Users\Docs.';
    $expected = 'An unescaped \*asterisk\* and a path C:\\\\Users\\\\Docs\.';

    expect(StringService::toSingleLineEscapedString($input))
        ->toBe($expected);
});
