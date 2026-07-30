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
 * Voegt de gegenereerde registerhoofdstukken en de handgeschreven tekst samen
 * tot één markdownbestand.
 */
class DocumentAssembler
{
    /**
     * Bestanden met een naam onder deze grens komen vóór de registers, de rest
     * erna. Zo bepaalt de bestandsnaam de plek in het document.
     */
    private const PROSE_PIVOT = '50-';

    /** Plek waar de gegenereerde hergebruik-tabel terechtkomt. */
    private const SHARED_PARTS_PLACEHOLDER = '<!-- HERGEBRUIK-TABEL -->';

    /**
     * De gedeelde onderdelen, en de trait waarmee een model aangeeft dat het ze
     * gebruikt. Zo blijft de hergebruik-tabel kloppen zodra een register een
     * koppeling krijgt of verliest.
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
     * @param array<string, class-string<Model>> $models registertitel => model
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

        $files = glob($proseDir . '/*.md');
        if ($files === false) {
            return [[], []];
        }
        sort($files);

        $before = [];
        $after = [];

        foreach ($files as $file) {
            $contents = rtrim((string) file_get_contents($file));
            if ($contents === '') {
                continue;
            }

            // De hergebruik-tabel volgt uit de modellen; de handgeschreven tekst
            // geeft alleen aan waar hij komt te staan.
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
     * Bouwt de tabel met gedeelde onderdelen uit de traits op de modellen.
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
     * Waarschuwing vooraf: dit bestand wordt overschreven, de bron staat elders.
     * Een HTML-commentaar valt weg in de PDF.
     */
    private function header(): string
    {
        return "<!--\n"
            . "  Dit bestand wordt gegenereerd door `just docs-datamodel`.\n"
            . "  Wijzigingen hier gaan verloren.\n\n"
            . "  De veldtabellen komen uit de Filament-formulieren; pas die aan\n"
            . "  (labels en hulpteksten staan in resources/lang/nl/). De\n"
            . "  omringende tekst staat in docs/handgeschreven/.\n"
            . "-->\n\n";
    }
}
