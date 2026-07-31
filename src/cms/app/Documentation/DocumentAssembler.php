<?php

declare(strict_types=1);

namespace App\Documentation;

use App\Models\Concerns\HasContactPersons;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasProcessors;
use App\Models\Concerns\HasReceivers;
use App\Models\Concerns\HasResponsibles;
use App\Models\Concerns\HasSystems;
use App\Models\Concerns\HasTags;
use Illuminate\Database\Eloquent\Model;

use function __;
use function array_merge;
use function basename;
use function class_uses_recursive;
use function count;
use function file_get_contents;
use function glob;
use function implode;
use function in_array;
use function is_dir;
use function rtrim;
use function sort;
use function sprintf;
use function str_replace;

/**
 * Joins the generated register chapters and the handwritten prose into a single
 * markdown file.
 */
class DocumentAssembler
{
    /**
     * Files sorting below this name come before the registers, the rest after. That
     * way the file name decides where a chapter lands in the document.
     */
    private const PROSE_PIVOT = '50-';

    /** Where the generated shared-parts table is inserted. */
    private const SHARED_PARTS_PLACEHOLDER = '<!-- HERGEBRUIK-TABEL -->';

    /**
     * The shared parts, and the trait a model uses to declare it has them. That
     * keeps the table correct as soon as a register gains or loses a relation.
     */
    private const SHARED_PARTS = [
        'Verwerkingsverantwoordelijken' => HasResponsibles::class,
        'Verwerkers' => HasProcessors::class,
        'Ontvangers' => HasReceivers::class,
        'Systemen / applicaties' => HasSystems::class,
        'Contactpersonen' => HasContactPersons::class,
        'Documenten' => HasDocuments::class,
        'Labels' => HasTags::class,
    ];

    /**
     * @param array<int, string> $chapters
     * @param array<string, class-string<Model>> $models register title => model
     */
    public function assemble(array $chapters, string $proseDir, array $models): string
    {
        [$before, $after] = $this->prose($proseDir, $models);

        $parts = array_merge($before, $chapters, $after);

        return $this->header() . implode("\n\n---\n\n", $parts) . "\n";
    }

    /**
     * @param array<string, class-string<Model>> $models
     *
     * @return array{array<int, string>, array<int, string>}
     */
    private function prose(string $proseDir, array $models): array
    {
        if (!is_dir($proseDir)) {
            return [[], []];
        }

        $found = glob($proseDir . '/*.md');
        $files = $found === false ? [] : $found;
        sort($files);

        $before = [];
        $after = [];

        foreach ($files as $file) {
            $contents = rtrim((string) file_get_contents($file));
            if ($contents === '') {
                continue;
            }

            // The shared-parts table follows from the models; the handwritten text
            // only marks where it goes.
            $contents = str_replace(
                self::SHARED_PARTS_PLACEHOLDER,
                $this->renderSharedParts($models),
                $contents,
            );

            if (basename($file) < self::PROSE_PIVOT) {
                $before[] = $contents;

                continue;
            }

            $after[] = $contents;
        }

        return [$before, $after];
    }

    /**
     * Builds the shared-parts table from the traits on the models.
     *
     * @param array<string, class-string<Model>> $models
     */
    private function renderSharedParts(array $models): string
    {
        $lines = ['| Onderdeel | Hergebruikt over |', '| --- | --- |'];

        foreach (self::SHARED_PARTS as $part => $trait) {
            $users = [];

            foreach ($models as $title => $model) {
                if (in_array($trait, class_uses_recursive($model), true)) {
                    $users[] = $title;
                }
            }

            if ($users === []) {
                continue;
            }

            $lines[] = sprintf(
                '| %s | %s |',
                $part,
                count($users) === count($models) ? 'Alle registers' : implode(', ', $users),
            );
        }

        return implode("\n", $lines);
    }

    /**
     * A warning up front: this file gets overwritten, the source lives elsewhere.
     * An HTML comment disappears in the PDF.
     */
    private function header(): string
    {
        $lines = [
            __('documentation.generated_header.line_1'),
            __('documentation.generated_header.line_2'),
            '',
            __('documentation.generated_header.line_3'),
            __('documentation.generated_header.line_4'),
            __('documentation.generated_header.line_5'),
        ];

        $body = '';
        foreach ($lines as $line) {
            $body .= $line === '' ? "\n" : '  ' . $line . "\n";
        }

        return "<!--\n" . $body . "-->\n\n";
    }
}
