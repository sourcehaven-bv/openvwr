<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\MimeTypesInterface;
use Webmozart\Assert\Assert;

use function __;
use function in_array;

readonly class ExtensionMimeType implements ValidationRule
{
    public function __construct(
        private LoggerInterface $logger,
        private MimeTypesInterface $mimeTypes,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        Assert::isInstanceOf($value, UploadedFile::class);

        $clientOriginalName = $value->getClientOriginalName();
        $clientOriginalExtension = $value->getClientOriginalExtension();
        $mimeType = $value->getMimeType();

        if ($mimeType === null) {
            $this->logger->info('mime-type cannot be guessed', [
                'extension' => $clientOriginalExtension,
            ]);

            $fail(__('validation.custom.mime_type_invalid', ['attribute' => $clientOriginalName]));
            return;
        }

        $validExtensions = $this->mimeTypes->getExtensions($mimeType);
        $result = in_array($clientOriginalExtension, $validExtensions, true);

        if ($result === false) {
            $this->logger->info('extension not valid with mime-type', [
                'extension' => $clientOriginalExtension,
                'mime-type' => $mimeType,
            ]);

            $fail(__('validation.custom.mime_type_invalid', ['attribute' => $clientOriginalName]));
            return;
        }

        $this->logger->debug('extension valid with mime-type', [
            'extension' => $clientOriginalExtension,
            'mime-type' => $mimeType,
        ]);
    }
}
