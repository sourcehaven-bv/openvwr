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

use function class_basename;
use function implode;
use function is_array;
use function preg_replace;
use function str_contains;
use function strip_tags;
use function trim;

/**
 * Beschrijft een formuliercomponent in gewone taal.
 *
 * Alles wat een lezer van de documentatie over één veld te zien krijgt - het
 * label, het soort invoer en de toelichting - wordt hier bepaald. De teksten
 * komen uit het formulier zelf, zodat het document zegt wat de invuller ook
 * werkelijk in het scherm ziet.
 */
class FieldDescriber
{
    /**
     * Vertaling van componentklasse naar de omschrijving in de kolom
     * "Soort invoer". Bewust in gewone taal: het document is voor privacy
     * officers, niet voor ontwikkelaars.
     */
    private const INPUT_KINDS = [
        Textarea::class => 'Toelichting',
        TextInput::class => 'Tekst',
        Toggle::class => 'Ja/nee',
        Checkbox::class => 'Ja/nee',
        DatePicker::class => 'Datum',
        DateTimePicker::class => 'Datum',
        Radio::class => 'Keuze',
        Select::class => 'Keuze',
        CheckboxList::class => 'Meerkeuze',
        FileUpload::class => 'Bestand',
        Repeater::class => 'Lijst',
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
     * De beschrijving onder een sectiekop, bijvoorbeeld de uitleg bij de
     * GEB-vragenlijst of bij de bijzondere gegevens.
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
        // Labels zijn vrij te kiezen trefwoorden, geen vaste lijst; technisch
        // is het een Select, maar dat zegt de lezer niets.
        if (class_basename($component) === 'TagsInput') {
            return 'Meerkeuze (vrij)';
        }

        // Eerst koppelingen: een verwijzing naar een andere registratie is voor
        // de lezer iets anders dan een keuzelijst, ook al is het technisch een
        // Select.
        if ($this->isRelation($component)) {
            return 'Koppeling';
        }

        foreach (self::INPUT_KINDS as $class => $kind) {
            if ($component instanceof $class) {
                return $kind;
            }
        }

        return 'Tekst';
    }

    /**
     * De hulptekst onder een veld. Dat is precies de uitleg die een invuller in
     * het scherm ziet, en daarmee de meest betrouwbare omschrijving die er is.
     *
     * Bij een keuzelijst worden de opties eraan toegevoegd: juist die
     * antwoordmogelijkheden laten zien wat het systeem kan vastleggen.
     */
    public function help(Component $component): string
    {
        $parts = [];

        // Alleen echte velden hebben een hulptekst; layoutcomponenten kennen de
        // methode niet eens.
        if ($component instanceof Field) {
            try {
                $helper = $this->toText($component->getHelperText());
                if ($helper !== '') {
                    $parts[] = $helper;
                }
            } catch (Throwable) {
                // Een hulptekst die pas tijdens het invullen te bepalen is,
                // slaan we over; de rest van de regel blijft bruikbaar.
            }
        }

        $options = $this->options($component);
        if ($options !== []) {
            $parts[] = 'Keuze uit: ' . implode('; ', $options) . '.';
        }

        return implode(' ', $parts);
    }

    /**
     * Herkent componenten die naar een andere registratie verwijzen.
     *
     * Deze zijn in dit project altijd van een eigen klasse (RelationTable,
     * SelectSingleWithLookup, ChildrenRelationTable), dus de naam is een
     * betrouwbaarder signaal dan de Filament-basisklasse.
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
     * Maakt van meerregelige of ingesprongen tekst één nette regel.
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

        // Een lijst die uit de database komt hoort niet in het document: die
        // verschilt per organisatie en zegt niets over wat het systeem kan.
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
            // Filament kent ook gegroepeerde opties; die komen als array binnen.
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
     * Maakt platte tekst van wat Filament teruggeeft.
     *
     * Labels en hulpteksten zijn een string of een Htmlable; in het laatste
     * geval zit er opmaak in die in een tabel niets te zoeken heeft.
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
