<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use function __;
use function is_string;
use function preg_split;
use function sprintf;
use function trim;

/**
 * What a cell mapped onto a note target becomes. A column that is itself a
 * notes column ("Opmerkingen", "Notities", "Tekst", or the export of OpenVWR)
 * holds the notes as they are, separated by blank lines; any other column
 * becomes one note headed with the column name, so the origin stays visible.
 * The FG note is always taken as it is.
 */
class NoteBodies
{
    public function __construct(
        private readonly FieldSynonyms $fieldSynonyms,
    ) {
    }

    /**
     * @param mixed $value the cell, or one entry per row for a record folded
     *        from several rows
     *
     * @return array<int, string>
     */
    public function for(MappingField $field, mixed $value): array
    {
        $asTheyAre = $field->relation === RelationKey::FG_REMARK || $this->isNotesColumn($field->source);
        $bodies = [];

        foreach ((array) $value as $entry) {
            if (!is_string($entry) || trim($entry) === '') {
                continue;
            }

            if (!$asTheyAre) {
                $bodies[] = sprintf('%s: %s', $field->source, trim($entry));

                continue;
            }

            foreach ($this->paragraphs($entry) as $body) {
                $bodies[] = $body;
            }
        }

        return $bodies;
    }

    private function isNotesColumn(string $header): bool
    {
        return $this->fieldSynonyms->canonicalise($header)
            === $this->fieldSynonyms->canonicalise(__('import_mapping.field_remarks'));
    }

    /**
     * @return array<int, string>
     */
    private function paragraphs(string $text): array
    {
        $parts = preg_split('/\n[ \t]*\n/', trim($text));
        $paragraphs = [];

        foreach ($parts === false ? [] : $parts as $part) {
            if (trim($part) !== '') {
                $paragraphs[] = trim($part);
            }
        }

        return $paragraphs;
    }
}
