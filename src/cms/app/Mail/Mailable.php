<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Contracts\Mail\Factory;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Mailable as IlluminateMailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mime\Address;
use Webmozart\Assert\Assert;

use function array_map;
use function array_merge;

abstract class Mailable extends IlluminateMailable
{
    abstract public function getSubject(): string;

    public function envelope(): Envelope
    {
        // No "[OpenVWR]: " prefix: mail clients already show the sender
        // prominently, so it only ate into the subject line -- which is what
        // gets truncated first on a phone.
        return new Envelope(subject: $this->getSubject());
    }

    /**
     * @return array<string, string|array<string>>
     */
    public function getLogContext(): array
    {
        return [];
    }

    /**
     * @param Factory|Mailer $mailer
     */
    public function send($mailer): ?SentMessage // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    {
        $sentMessage = parent::send($mailer);
        Assert::notNull($sentMessage);

        $recipients = $sentMessage->getSymfonySentMessage()->getEnvelope()->getRecipients();
        $emailAddresses = array_map(static function (Address $address): string {
            return $address->getAddress();
        }, $recipients);

        $userIds = User::whereIn('email', $emailAddresses)
            ->get()
            ->map(static function (User $user): string {
                return $user->getKey()->toString();
            })
            ->toArray();

        Log::info('Mail message sent', array_merge($this->getLogContext(), [
            'mailable' => static::class,
            'userIds' => $userIds,
        ]));

        return $sentMessage;
    }
}
