<?php

declare(strict_types=1);

namespace App\Documentation;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Contracts\Support\Htmlable;
use Throwable;

use function __;
use function class_basename;
use function implode;
use function is_array;
use function is_string;
use function preg_replace;
use function str_contains;
use function strip_tags;
use function trim;

/**
 * Describes a form component in plain language.
 *
 * Everything a reader of the documentation sees about a single field - its
 * label, the kind of input and the explanation - is decided here. The texts come
 * from the form itself, so the document says what the person filling it in
 * actually sees on screen.
 */
class FieldDescriber
{
    /**
     * Maps a component class to the wording used in the "kind of input" column.
     * Deliberately plain: the document is for privacy officers, not developers.
     */
    private const INPUT_KINDS = [
        Textarea::class => 'textarea',
        TextInput::class => 'text',
        Toggle::class => 'boolean',
        Checkbox::class => 'boolean',
        DatePicker::class => 'date',
        DateTimePicker::class => 'date',
        Radio::class => 'choice',
        Select::class => 'choice',
        CheckboxList::class => 'multiple_choice',
        FileUpload::class => 'file',
        Repeater::class => 'list',
    ];

    public function label(Component $component): ?string
    {
        if (!$component instanceof Field) {
            return null;
        }

        try {
            $label = $this->toText($component->getLabel());
        } catch (Throwable) {
            return null;
        }

        return $label === '' ? null : $label;
    }

    public function heading(Component $component): ?string
    {
        if (!$component instanceof Section) {
            return null;
        }

        try {
            $heading = $this->toText($component->getHeading());
        } catch (Throwable) {
            return null;
        }

        return $heading === '' ? null : $heading;
    }

    /**
     * The description below a section heading, such as the explanation above the
     * DPIA questionnaire or the special categories of data.
     */
    public function description(Component $component): string
    {
        if (!$component instanceof Section) {
            return '';
        }

        try {
            return $this->toText($component->getDescription());
        } catch (Throwable) {
            return '';
        }
    }

    public function kind(Component $component): string
    {
        // Labels are free-form keywords rather than a fixed list; technically a
        // Select, but that tells the reader nothing.
        if (class_basename($component) === 'TagsInput') {
            return $this->kindLabel('free_tags');
        }

        // Relations first: a reference to another record reads differently than a
        // choice list, even though it is technically a Select.
        if ($this->isRelation($component)) {
            return $this->kindLabel('relation');
        }

        foreach (self::INPUT_KINDS as $class => $kind) {
            if ($component instanceof $class) {
                return $this->kindLabel($kind);
            }
        }

        return $this->kindLabel('text');
    }

    /**
     * The translated wording for a kind of input.
     */
    private function kindLabel(string $kind): string
    {
        $label = __('documentation.kind.' . $kind);

        return is_string($label) ? $label : $kind;
    }

    /**
     * The helper text below a field: exactly the explanation someone filling in the
     * form sees on screen, and therefore the most reliable description there is.
     *
     * For a choice list the options are appended, because those answers are what
     * show which values the system can record.
     */
    public function help(Component $component): string
    {
        $parts = [];

        // Only real fields have helper text; layout components do not even have
        // the method.
        if ($component instanceof Field) {
            try {
                $helper = $this->toText($component->getHelperText());
                if ($helper !== '') {
                    $parts[] = $helper;
                }
            } catch (Throwable) {
                // A helper text that can only be resolved while filling in the form is
                // skipped; the rest of the row stays usable.
            }
        }

        $options = $this->options($component);
        if ($options !== []) {
            $prefix = __('documentation.options_prefix', ['options' => implode('; ', $options)]);
            $parts[] = is_string($prefix) ? $prefix : implode('; ', $options);
        }

        return implode(' ', $parts);
    }

    /**
     * Recognises components that point at another record.
     *
     * In this project those always have their own class (RelationTable,
     * SelectSingleWithLookup, ChildrenRelationTable), so the name is a more
     * reliable signal than the Filament base class.
     */
    public function isRelation(Component $component): bool
    {
        $name = class_basename($component);

        return str_contains($name, 'RelationTable')
            || str_contains($name, 'WithLookup')
            || str_contains($name, 'ParentSelect');
    }

    public function isInformationBlock(Component $component): bool
    {
        return str_contains(class_basename($component), 'InformationBlock');
    }

    /**
     * Turns multi-line or indented text into a single tidy line.
     */
    public function tidy(string $text): string
    {
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    /**
     * @return array<int, string>
     */
    private function options(Component $component): array
    {
        if (!$component instanceof Select && !$component instanceof Radio && !$component instanceof CheckboxList) {
            return [];
        }

        // A list that comes from the database does not belong in the document: it
        // differs per organisation and says nothing about what the system can do.
        if ($this->isRelation($component)) {
            return [];
        }

        try {
            $options = $component->getOptions();
        } catch (Throwable) {
            return [];
        }

        $labels = [];
        foreach ($options as $option) {
            // Filament also supports grouped options; those arrive as an array.
            if (is_array($option)) {
                continue;
            }

            if ($option !== '') {
                $labels[] = $this->tidy($option);
            }
        }

        return $labels;
    }

    /**
     * Turns whatever Filament returns into plain text.
     *
     * Labels and helper texts are either a string or an Htmlable; the latter
     * carries markup that has no place in a table.
     */
    private function toText(Htmlable|string|null $value): string
    {
        if ($value === null) {
            return '';
        }

        $text = $value instanceof Htmlable ? $value->toHtml() : $value;

        return $this->tidy(strip_tags($text));
    }
}
