<?php

declare(strict_types=1);

namespace App\Documentation;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Support\Str;
use RuntimeException;

use function __;
use function array_values;
use function class_basename;
use function implode;
use function is_string;
use function sprintf;
use function str_repeat;
use function str_replace;

/**
 * Schrijft één register als markdownhoofdstuk.
 *
 * De indeling volgt de stappen van het formulier, zodat het document dezelfde
 * volgorde heeft als wat een invuller in het scherm ziet.
 */
class RegisterRenderer
{
    /** Markering voor een veld dat bij het onderdeel erboven hoort. */
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

        // Een register zonder secties betekent dat het formulier niet goed is
        // opgebouwd - bijvoorbeeld doordat een component onderweg afhaakte. Dan
        // liever falen dan een hoofdstuk met alleen een kop opleveren.
        if ($sections === 0) {
            throw new RuntimeException('geen velden gevonden; is het formulier goed opgebouwd?');
        }

        return implode("\n", $lines);
    }

    /**
     * @param array<int, Component> $children
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

        $lines[] = '| Veld | Soort invoer | Toelichting |';
        $lines[] = '| --- | --- | --- |';

        foreach ($rows as $row) {
            $lines[] = sprintf('| %s | %s | %s |', $row['field'], $row['kind'], $row['help']);
        }

        $lines[] = '';

        return $lines;
    }

    /**
     * Loopt de componentboom af en maakt er tabelregels van.
     *
     * @param array<int, Component> $components
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

            // Layoutcomponenten (Grid, Group, Section, Fieldset) hebben zelf
            // geen waarde; hun kinderen wel.
            $this->collect($this->notes->childrenOf($component), $rows, $depth);
        }
    }

    /**
     * Verborgen velden en informatieblokken horen niet in het overzicht: de
     * eerste ziet een invuller nooit, de tweede is hulptekst voor de invuller en
     * niet voor de lezer van dit document.
     */
    private function isSkippable(Component $component): bool
    {
        return $component instanceof Hidden
            || $component instanceof Placeholder
            || $this->describer->isInformationBlock($component);
    }

    /**
     * Een Section binnen een stap groepeert bij elkaar horende vragen ("Is een
     * GEB (DPIA) verplicht?", "Bijzondere gegevens"). Die kop is inhoudelijk:
     * zonder die context staan de vragen los in de lucht.
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

        // Eén markering per niveau: gegevens binnen een betrokkene binnen de
        // registratie krijgen er dus twee.
        $prefix = str_repeat(self::NESTING_MARKER, $depth);

        $rows[] = [
            'field' => $prefix . $this->escape($label),
            'kind' => $this->describer->kind($component),
            'help' => $this->escape($this->describer->help($component)),
        ];
    }

    /**
     * @return array<int, Component>
     */
    private function componentsOf(mixed $form): array
    {
        if (!$form instanceof Form) {
            return [];
        }

        return array_values($form->getComponents());
    }

    /**
     * De omschrijving van het register, uit het taalbestand van het model.
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
     * Tekens die een markdowntabel zouden breken onschadelijk maken.
     */
    private function escape(string $text): string
    {
        return str_replace('|', '\\|', $text);
    }
}
