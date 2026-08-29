<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Models\RetentionPeriod;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Collection;

use function __;
use function array_key_exists;
use function is_string;

/**
 * A bewaartermijn: pick one of the organisation's terms, or write your own.
 *
 * The list only supplies suggestions -- the chosen term is stored as text in
 * the record's own column, so an established register keeps the wording that
 * applied when it was recorded even if the list is edited afterwards. That
 * also means no migration and no foreign key: existing free text simply
 * opens as "overig".
 *
 * The two visible fields are pure form state (dehydrated(false)); only the
 * hidden real field is written back.
 */
class RetentionPeriodInput
{
    public const string OTHER = '__other__';

    private const string CHOICE_SUFFIX = '_choice';
    private const string CUSTOM_SUFFIX = '_custom';

    /**
     * @return array<Component>
     */
    public static function make(string $name, string $label, ?string $helperText = null, bool $required = false): array
    {
        return [
            Group::make()
                ->schema([
                    // The real column. The two fields below are form-only and write here.
                    Hidden::make($name),
                    self::choiceField($name, $label, $helperText, $required),
                    self::customField($name, $label, $helperText, $required),
                ])
                ->columnSpanFull(),
        ];
    }

    private static function choiceField(string $name, string $label, ?string $helperText, bool $required): Select
    {
        $customName = $name . self::CUSTOM_SUFFIX;

        return Select::make($name . self::CHOICE_SUFFIX)
            ->label($label)
            ->helperText($helperText)
            ->options(static fn (): array => self::options())
            ->required($required)
            ->live()
            ->dehydrated(false)
            // An organisation with no terms yet would get a dropdown whose only
            // entry is "overig", so skip straight to the free text field.
            ->visible(static fn (): bool => self::hasTerms())
            ->afterStateHydrated(static function (Select $component, Get $get, Set $set) use ($name, $customName): void {
                $stored = $get($name);
                $stored = is_string($stored) ? $stored : '';

                if ($stored === '') {
                    $component->state(null);

                    return;
                }

                // A stored term that no longer matches the list -- because it was
                // typed by hand, or the list item was renamed or removed since --
                // stays editable as free text instead of silently resetting.
                if (array_key_exists($stored, self::options())) {
                    $component->state($stored);

                    return;
                }

                $component->state(self::OTHER);
                $set($customName, $stored);
            })
            ->afterStateUpdated(static function (?string $state, Get $get, Set $set) use ($name, $customName): void {
                $set($name, $state === self::OTHER ? ($get($customName) ?? '') : ($state ?? ''));
            });
    }

    private static function customField(string $name, string $label, ?string $helperText, bool $required): Textarea
    {
        $choiceName = $name . self::CHOICE_SUFFIX;

        $customLabel = self::translate('retention_period.custom_label');
        $customHelperText = self::translate('retention_period.help_custom');

        return Textarea::make($name . self::CUSTOM_SUFFIX)
            // With no list to pick from this is the only field, so it carries
            // the real label rather than the "toelichting bij overig" one.
            ->label(static fn (): string => self::hasTerms() ? $customLabel : $label)
            ->helperText(static fn (): ?string => self::hasTerms() ? $customHelperText : $helperText)
            ->rows(3)
            ->maxLength(512)
            ->required($required)
            ->live(onBlur: true)
            ->dehydrated(false)
            ->visible(static fn (Get $get): bool => !self::hasTerms() || $get($choiceName) === self::OTHER)
            ->afterStateHydrated(static function (Textarea $component, Get $get) use ($name): void {
                // Without a list the select is hidden and never hydrates the
                // custom field, so seed it from the stored value here.
                if (self::hasTerms()) {
                    return;
                }

                $stored = $get($name);
                $component->state(is_string($stored) ? $stored : '');
            })
            ->afterStateUpdated(static function (?string $state, Set $set) use ($name): void {
                $set($name, $state ?? '');
            });
    }

    /**
     * The enabled terms of the current organisation, keyed by their own text:
     * the value stored on the record is the term itself, not an id.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = self::terms()->mapWithKeys(static fn (string $name): array => [$name => $name])->all();

        $options[self::OTHER] = self::translate('retention_period.other');

        return $options;
    }

    /**
     * __() is typed as array|string|null; these keys are always plain strings.
     */
    private static function translate(string $key): string
    {
        $translation = __($key);

        return is_string($translation) ? $translation : $key;
    }

    public static function hasTerms(): bool
    {
        return self::terms()->isNotEmpty();
    }

    /**
     * @return Collection<int, string>
     */
    private static function terms(): Collection
    {
        /** @var Collection<int, string> $names */
        $names = RetentionPeriod::tenantQuery()
            ->where('enabled', true)
            ->orderBy('name')
            ->pluck('name');

        return $names;
    }
}
