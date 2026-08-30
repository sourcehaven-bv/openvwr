<?php

declare(strict_types=1);

namespace App\Documentation;

use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Resource;
use Illuminate\Support\Str;
use RuntimeException;

use function __;
use function array_values;
use function class_basename;
use function implode;
use function sprintf;
use function str_repeat;
use function str_replace;

/**
 * Writes a single register as a markdown chapter.
 *
 * The layout follows the steps of the form, so the document has the same order
 * as what someone filling it in sees on screen.
 */
class RegisterRenderer
{
    /** Marker for a field that belongs to the item above it. */
    private const NESTING_MARKER = '» ';

    public function __construct(
        private readonly FieldDescriber $describer,
        private readonly SectionNotes $notes,
    ) {
    }

    /**
     * @param class-string<Resource> $resourceClass
     */
    public function render(mixed $form, string $resourceClass, string $title): string
    {
        $lines = ['# ' . $title, ''];

        $description = $this->registerDescription($resourceClass);
        if ($description !== null) {
            $lines[] = $description;
            $lines[] = '';
        }

        $sections = 0;

        foreach ($this->componentsOf($form) as $section) {
            $heading = $this->describer->heading($section);
            if ($heading === null) {
                continue;
            }

            $children = $this->notes->childrenOf($section);

            $rows = [];
            $this->collect($children, $rows, 0);

            if ($rows === []) {
                continue;
            }

            $lines = [...$lines, ...$this->renderSection($heading, $children, $rows)];
            $sections++;
        }

        // A register without sections means the form did not assemble properly -
        // a component bailed out along the way, for instance. Better to fail
        // than to emit a chapter that is nothing but a heading.
        if ($sections === 0) {
            throw new RuntimeException('no fields found; did the form assemble correctly?');
        }

        return implode("\n", $lines);
    }

    /**
     * @param array<int, \Filament\Schemas\Components\Component> $children
     * @param array<int, array{field: string, kind: string, help: string}> $rows
     *
     * @return array<int, string>
     */
    private function renderSection(string $heading, array $children, array $rows): array
    {
        $lines = ['## ' . $heading, ''];

        $note = $this->notes->forSection($children);
        if ($note !== null) {
            $lines[] = $note;
            $lines[] = '';
        }

        $lines[] = sprintf(
            '| %s | %s | %s |',
            __('documentation.column_field'),
            __('documentation.column_kind'),
            __('documentation.column_help'),
        );
        // Pandoc derives the column widths from the width of these dashes.
        // Without that the three columns end up equally wide, which leaves the
        // explanation cramped while the other two waste space.
        $lines[] = '|' . str_repeat('-', 34) . '|' . str_repeat('-', 14) . '|' . str_repeat('-', 52) . '|';

        foreach ($rows as $row) {
            $lines[] = sprintf('| %s | %s | %s |', $row['field'], $row['kind'], $row['help']);
        }

        $lines[] = '';

        return $lines;
    }

    /**
     * Walks the component tree and turns it into table rows.
     *
     * @param array<int, \Filament\Schemas\Components\Component> $components
     * @param array<int, array{field: string, kind: string, help: string}> $rows
     */
    private function collect(array $components, array &$rows, int $depth): void
    {
        foreach ($components as $component) {
            if ($this->isSkippable($component)) {
                continue;
            }

            if ($component instanceof Repeater) {
                $this->addRow($rows, $component, $depth);
                $this->collect($this->notes->childrenOf($component), $rows, $depth + 1);

                continue;
            }

            if ($component instanceof Field) {
                $this->addRow($rows, $component, $depth);

                continue;
            }

            $this->addSubheading($rows, $component);

            // Layout components (Grid, Group, Section, Fieldset) hold no value
            // themselves; their children do.
            $this->collect($this->notes->childrenOf($component), $rows, $depth);
        }
    }

    /**
     * Hidden fields and information blocks do not belong in the overview: the first
     * is never seen, the second is guidance for whoever fills in the form rather
     * than for the reader of this document.
     */
    private function isSkippable(Component $component): bool
    {
        return $component instanceof Hidden
            || $component instanceof Placeholder
            || $this->describer->isInformationBlock($component);
    }

    /**
     * A Section inside a step groups related questions ("Is a DPIA mandatory?",
     * "Special categories of data"). That heading carries meaning: without it the
     * questions stand on their own.
     *
     * @param array<int, array{field: string, kind: string, help: string}> $rows
     */
    private function addSubheading(array &$rows, Component $component): void
    {
        if (!$component instanceof Section) {
            return;
        }

        $subheading = $this->describer->heading($component);
        if ($subheading === null || $subheading === '') {
            return;
        }

        $rows[] = [
            'field' => '**' . $this->escape($subheading) . '**',
            'kind' => '',
            'help' => $this->escape($this->describer->description($component)),
        ];
    }

    /**
     * @param array<int, array{field: string, kind: string, help: string}> $rows
     */
    private function addRow(array &$rows, Component $component, int $depth): void
    {
        $label = $this->describer->label($component);
        if ($label === null || $label === '') {
            return;
        }

        // One marker per level, so data inside a data subject inside the record
        // gets two.
        $prefix = str_repeat(self::NESTING_MARKER, $depth);

        $rows[] = [
            'field' => $prefix . $this->escape($label),
            'kind' => $this->describer->kind($component),
            'help' => $this->escape($this->describer->help($component)),
        ];
    }

    /**
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    private function componentsOf(mixed $form): array
    {
        if (!$form instanceof Schema) {
            return [];
        }

        return array_values($form->getComponents());
    }

    /**
     * The register's description, from the model's translation file.
     *
     * @param class-string<Resource> $resourceClass
     */
    private function registerDescription(string $resourceClass): ?string
    {
        $key = Str::snake(class_basename($resourceClass::getModel())) . '.register_description';

        $translation = __($key);
        if ($translation === $key) {
            return null;
        }

        return $this->describer->tidy($translation);
    }

    /**
     * Escapes characters that would otherwise break a markdown table.
     */
    private function escape(string $text): string
    {
        return str_replace('|', '\\|', $text);
    }
}
