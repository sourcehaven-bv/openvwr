<?php

declare(strict_types=1);

namespace App\Http\Controllers\Authentication;

use App\Exceptions\AppException;

use function __;

class PasswordlessLoginException extends AppException
{
    /**
     * The exception message stays English on purpose: it ends up in the logs and the audit trail.
     * The translation key carries the message that is shown to the user.
     */
    private string $translationKey = 'auth.login_link_invalid';

    public static function invalidToken(): self
    {
        return self::withTranslationKey('invalid token', 'auth.login_link_invalid');
    }

    public static function noTokenFound(): self
    {
        return self::withTranslationKey('no token found', 'auth.login_link_expired');
    }

    public static function noOrganisationFound(): self
    {
        return self::withTranslationKey('no organisation found', 'auth.login_no_organisation');
    }

    public function getTranslatedMessage(): string
    {
        return __($this->translationKey);
    }

    private static function withTranslationKey(string $message, string $translationKey): self
    {
        $exception = new self($message);
        $exception->translationKey = $translationKey;

        return $exception;
    }
}
