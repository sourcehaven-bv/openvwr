<?php

declare(strict_types=1);

namespace App\Services\Snapshot;

use App\ValueObjects\Snapshot\MissingRequiredField;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Illuminate\Contracts\Support\Htmlable;

use function __;
use function array_slice;
use function count;
use function implode;
use function is_array;
use function is_string;
use function sprintf;
use function strip_tags;
use function trim;

/**
 * Determines whether a record is complete enough to be turned into a version (snapshot).
 *
 * The form schema stays the single source of truth: this service walks the very same
 * components that declare `->required()`, instead of restating those rules elsewhere.
 */
class SnapshotReadinessService
{
    /**
     * Number of missing fields listed individually before the message is truncated.
     */
    private const int MAX_LISTED_FIELDS = 10;

    /**
     * @return array<int, MissingRequiredField>
     */
    public function getMissingRequiredFields(Form $form): array
    {
        $missingRequiredFields = [];

        foreach ($form->getComponents(withHidden: false) as $component) {
            $this->collectMissingRequiredFields($component, null, $missingRequiredFields);
        }

        return $missingRequiredFields;
    }

    public function isReadyForSnapshot(Form $form): bool
    {
        return $this->getMissingRequiredFields($form) === [];
    }

    /**
     * @param array<int, MissingRequiredField> $missingRequiredFields
     */
    public function buildMissingRequiredFieldsMessage(array $missingRequiredFields): string
    {
        $descriptions = [];
        foreach (array_slice($missingRequiredFields, 0, self::MAX_LISTED_FIELDS) as $missingRequiredField) {
            $descriptions[] = $missingRequiredField->describe();
        }

        $message = implode(', ', $descriptions);

        $remaining = count($missingRequiredFields) - self::MAX_LISTED_FIELDS;
        if ($remaining > 0) {
            $message = sprintf('%s %s', $message, __('snapshot.incomplete_and_more', ['count' => $remaining]));
        }

        return $message;
    }

    /**
     * @param array<int, MissingRequiredField> $missingRequiredFields
     */
    private function collectMissingRequiredFields(
        Component $component,
        ?string $stepLabel,
        array &$missingRequiredFields,
    ): void {
        $stepLabel = $this->resolveStepLabel($component) ?? $stepLabel;

        if ($component instanceof Field && $component->isRequired() && $this->isBlank($component)) {
            $missingRequiredFields[] = new MissingRequiredField(
                statePath: $component->getStatePath(),
                label: $this->resolveFieldLabel($component),
                stepLabel: $stepLabel,
            );
        }

        foreach ($component->getChildComponentContainers(withHidden: false) as $childComponentContainer) {
            foreach ($childComponentContainer->getComponents(withHidden: false) as $childComponent) {
                $this->collectMissingRequiredFields($childComponent, $stepLabel, $missingRequiredFields);
            }
        }
    }

    private function resolveStepLabel(Component $component): ?string
    {
        // A wizard step carries its title as a label, a one page section as a heading.
        $label = match (true) {
            $component instanceof Step => $component->getLabel(),
            $component instanceof Section => $component->getHeading(),
            default => null,
        };

        return $this->toPlainText($label);
    }

    /**
     * The label as shown next to the field, so the message names fields exactly as the
     * user sees them. `getValidationAttribute()` is deliberately not used: it lowercases
     * the first letter for use mid-sentence, which reads wrong in a list of field names.
     */
    private function resolveFieldLabel(Field $field): string
    {
        return $this->toPlainText($field->getLabel()) ?? $field->getName();
    }

    private function toPlainText(string|Htmlable|null $value): ?string
    {
        if ($value instanceof Htmlable) {
            $value = strip_tags($value->toHtml());
        }

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    /**
     * A required field counts as missing when it holds no meaningful value. An empty
     * array (no related records selected) counts as missing too, which mirrors how
     * Laravel's `required` rule treats empty arrays.
     */
    private function isBlank(Field $field): bool
    {
        $state = $field->getState();

        if (is_array($state)) {
            return $state === [];
        }

        if (is_string($state)) {
            return trim($state) === '';
        }

        return $state === null;
    }
}
