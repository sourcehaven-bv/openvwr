<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Authentication;

use App\Http\Controllers\Authentication\PasswordlessLoginException;
use Tests\TestCase;

use function expect;
use function it;
use function uses;

uses(TestCase::class);

it('keeps the exception message in english for the audit trail', function (): void {
    expect(PasswordlessLoginException::invalidToken()->getMessage())->toBe('invalid token')
        ->and(PasswordlessLoginException::noTokenFound()->getMessage())->toBe('no token found')
        ->and(PasswordlessLoginException::noOrganisationFound()->getMessage())->toBe('no organisation found');
});

it('shows a dutch message to the user for every error path', function (): void {
    expect(PasswordlessLoginException::noTokenFound()->getTranslatedMessage())
        ->toBe('Deze loginlink is verlopen. Vraag een nieuwe login-e-mail aan om in te loggen.')
        ->and(PasswordlessLoginException::invalidToken()->getTranslatedMessage())
        ->toBe('Deze loginlink is niet geldig. Vraag een nieuwe login-e-mail aan om in te loggen.')
        ->and(PasswordlessLoginException::noOrganisationFound()->getTranslatedMessage())
        ->toBe('Uw account is nog niet aan een organisatie gekoppeld. Neem contact op met uw beheerder.');
});
