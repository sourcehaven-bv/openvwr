<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use MinVWS\Logging\Laravel\Models\AuditLog as BaseAuditLog;

use function array_flip;
use function array_intersect_key;
use function base64_decode;
use function is_array;
use function is_string;
use function json_decode;

/**
 * The package's database logger leaves the `email` column empty: it only
 * writes the actor's email inside `pii_request`. This copies it into the
 * column, so "last login per user" is a plain indexed query.
 *
 * When database encryption is configured, `pii_request` is ciphertext, the
 * email cannot be read from it, and the column stays empty.
 */
class AuditLog extends BaseAuditLog
{
    /**
     * The logger also passes `user_id`, which the table has no column for.
     * Fillable drops it, except under Model::unguarded() (factories,
     * seeders), where it would reach the insert and fail it.
     *
     * @param array<string, mixed> $attributes
     */
    public function fill(array $attributes): static
    {
        return parent::fill(array_intersect_key($attributes, array_flip($this->getFillable())));
    }

    /**
     * @return Attribute<never, string>
     */
    protected function piiRequest(): Attribute
    {
        return Attribute::set(static fn (string $value): array => [
            'pii_request' => $value,
            'email' => self::emailFromPiiRequest($value),
        ]);
    }

    private static function emailFromPiiRequest(string $piiRequest): ?string
    {
        $decoded = base64_decode($piiRequest, true);
        $data = $decoded === false ? null : json_decode($decoded, true);
        $email = is_array($data) ? ($data['email'] ?? null) : null;

        return is_string($email) ? $email : null;
    }
}
