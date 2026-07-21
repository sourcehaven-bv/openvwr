<?php

declare(strict_types=1);

namespace App\Jobs\Media;

use App\Config\Config;
use App\Vendor\MediaLibrary\Media;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

use function in_array;
use function pathinfo;
use function strtolower;

use const PATHINFO_EXTENSION;

class ValidateMimeType implements ShouldQueue
{
    use Dispatchable;
    use Batchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use SkipBatchIfCancelledMiddleware;

    /** @var array<string> */
    private readonly array $permittedMimeTypes;

    /** @var array<string, list<string>> */
    private readonly array $permittedFileExtensions;

    public function __construct(
        private readonly Media $media,
    ) {
        $collectionKey = 'media-library.permitted_file_types.' . $this->media->collection_name;
        $key = Config::has($collectionKey) ? $collectionKey : 'media-library.permitted_file_types.attachment';

        $permittedMimeTypes = Config::array($key);
        Assert::allString($permittedMimeTypes);

        $this->permittedMimeTypes = $permittedMimeTypes;

        $extensionCollectionKey = 'media-library.permitted_file_extensions.' . $this->media->collection_name;
        $extensionKey = Config::has($extensionCollectionKey)
            ? $extensionCollectionKey
            : 'media-library.permitted_file_extensions.attachment';

        $permittedFileExtensions = Config::array($extensionKey);
        /** @var array<string, list<string>> $permittedFileExtensions */
        $this->permittedFileExtensions = $permittedFileExtensions;
    }

    /**
     * @throws InvalidMimeTypeException
     */
    public function handle(
        LoggerInterface $logger,
    ): void {
        $mimeType = $this->media->mime_type;

        $logger->info('Validating mime type', [
            'collection' => $this->media->collection_name,
            'mimeType' => $mimeType,
        ]);

        if (!in_array($mimeType, $this->permittedMimeTypes, true)) {
            throw new InvalidMimeTypeException('Mime type is not permitted');
        }

        $permittedExtensions = $this->permittedFileExtensions[$mimeType] ?? [];

        if ($permittedExtensions === []) {
            return;
        }

        $extension = strtolower(pathinfo($this->media->file_name, PATHINFO_EXTENSION));

        if (!in_array($extension, $permittedExtensions, true)) {
            $logger->info('File extension is not permitted for mime type', [
                'extension' => $extension,
                'mimeType' => $mimeType,
            ]);

            throw new InvalidMimeTypeException('File extension is not permitted for this mime type');
        }
    }
}
