<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Enums\Import\ImportTarget;
use App\Filament\Forms\FormHost;
use App\Filament\Resources\AlgorithmRecordResource\AlgorithmRecordResourceForm;
use App\Filament\Resources\AvgProcessorProcessingRecordResource\AvgProcessorProcessingRecordResourceForm;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\AvgResponsibleProcessingRecordResourceForm;
use App\Filament\Resources\DataBreachRecord\DataBreachRecordResourceForm;
use App\Filament\Resources\DpiaPrescanRecordResource\DpiaPrescanRecordResourceForm;
use App\Filament\Resources\DpiaRecordResource\DpiaRecordResourceForm;
use App\Filament\Resources\WpgProcessingRecordResource\WpgProcessingRecordResourceForm;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

use function sprintf;
use function str_ends_with;

/**
 * The fields of a register's form, read from the form itself.
 *
 * The form is the one place that says what a user can enter for a register
 * and what it is called. The export and the import each used to keep a list
 * of their own, and every time one drifted a column came out that could not
 * be read back, or a field the form has was left out. The form settles it;
 * the lists are held to the form.
 *
 * The fields of a repeater (a doel, a betrokkene) are a record of their own
 * and are not walked into. Hidden fields carry no user input.
 */
class FormFields
{
    /** @var array<string, array<string, FormField>> */
    private static array $read = [];

    /**
     * @return array<string, FormField> keyed by field name, in form order
     */
    public static function for(ImportTarget $target): array
    {
        return self::$read[$target->value] ??= self::read($target);
    }

    public static function has(ImportTarget $target, string $name): bool
    {
        return isset(self::for($target)[$name]);
    }

    public static function label(ImportTarget $target, string $name): ?string
    {
        return (self::for($target)[$name] ?? null)?->label;
    }

    /**
     * The label of the field that edits a relation: "Subverwerkers" for the
     * verwerkers of a verwerker-verwerking, whatever the relation is called.
     */
    public static function relationLabel(ImportTarget $target, string $relation): ?string
    {
        foreach (self::for($target) as $field) {
            if ($field->relation === $relation) {
                return $field->label;
            }
        }

        return null;
    }

    /**
     * @return array<string, FormField>
     */
    private static function read(ImportTarget $target): array
    {
        $fields = [];
        self::collect(self::form($target)->getComponents(withHidden: true), $fields);

        return $fields;
    }

    /**
     * The one-page layout, which lists every field the steps layout does.
     */
    private static function form(ImportTarget $target): Form
    {
        $form = Form::make(new FormHost());

        return match ($target) {
            ImportTarget::DataBreachRecord => DataBreachRecordResourceForm::onePageForm($form),
            ImportTarget::AvgResponsibleProcessingRecord
                => AvgResponsibleProcessingRecordResourceForm::onePageForm($form),
            ImportTarget::AvgProcessorProcessingRecord
                => AvgProcessorProcessingRecordResourceForm::onePageForm($form),
            ImportTarget::WpgProcessingRecord => WpgProcessingRecordResourceForm::onePageForm($form),
            ImportTarget::AlgorithmRecord => AlgorithmRecordResourceForm::onePageForm($form),
            ImportTarget::DpiaRecord => DpiaRecordResourceForm::onePageForm($form),
            ImportTarget::DpiaPrescanRecord => DpiaPrescanRecordResourceForm::onePageForm($form),
        };
    }

    /**
     * Sections and groups are walked into, hidden or not: a field behind a
     * toggle is still a field of the form.
     *
     * @param array<Component> $components
     * @param array<string, FormField> $fields
     */
    private static function collect(array $components, array &$fields): void
    {
        foreach ($components as $component) {
            if ($component instanceof Field && !$component instanceof Hidden) {
                $name = $component->getName();
                $fields[$name] ??= new FormField(
                    $name,
                    self::labelOf($component, $fields),
                    self::relationOf($component),
                );
            }

            if ($component instanceof Repeater) {
                continue;
            }

            foreach ($component->getChildComponentContainers(withHidden: true) as $container) {
                self::collect($container->getComponents(withHidden: true), $fields);
            }
        }
    }

    /**
     * Several fields are labelled "Namelijk". Each belongs to the choice
     * right before it, whose label goes in front so the field can be told
     * apart on its own.
     *
     * @param array<string, FormField> $fields
     */
    private static function labelOf(Field $field, array $fields): string
    {
        $label = $field->getLabel();
        Assert::string($label);

        $name = $field->getName();
        if (!str_ends_with($name, '_other')) {
            return $label;
        }

        $parent = $fields[Str::beforeLast($name, '_other')] ?? null;
        Assert::notNull($parent, sprintf('"%s" comes before the field it belongs to', $name));

        return sprintf('%s — %s', $parent->label, $label);
    }

    private static function relationOf(Field $field): ?string
    {
        if ($field instanceof Select || $field instanceof Repeater) {
            return $field->getRelationshipName();
        }

        return null;
    }
}
