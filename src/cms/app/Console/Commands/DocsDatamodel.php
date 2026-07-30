<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Components\Uuid\Uuid;
use App\Documentation\DocNote;
use App\Enums\RegisterLayout;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Models\Concerns\HasContactPersons;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasProcessors;
use App\Models\Concerns\HasReceivers;
use App\Models\Concerns\HasResponsibles;
use App\Models\Concerns\HasSystems;
use App\Models\Concerns\HasTags;
use App\Models\Organisation;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Console\Command;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component as LivewireComponent;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

use function __;
use function array_merge;
use function array_values;
use function base_path;
use function basename;
use function class_basename;
use function class_exists;
use function class_uses_recursive;
use function count;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function glob;
use function implode;
use function in_array;
use function is_array;
use function is_dir;
use function is_string;
use function is_subclass_of;
use function mkdir;
use function preg_replace;
use function rtrim;
use function sort;
use function sprintf;
use function str_contains;
use function str_repeat;
use function str_replace;
use function strip_tags;
use function trim;
use function usort;

use const PHP_INT_MAX;

/**
 * Genereert de registerhoofdstukken van de datamodel-documentatie uit de
 * Filament-formulieren zelf.
 *
 * De formulieren zijn de enige plek waar de velden, hun labels en hun
 * hulpteksten echt staan; die met de hand overschrijven levert een document op
 * dat stilletjes veroudert. Deze generator leest daarom de echte schema's uit
 * en schrijft daar markdowntabellen van.
 *
 * Welke registers erin komen wordt niet hier vastgelegd: het commando vraagt
 * het Filament-paneel welke resources in de navigatiegroep "Registers" staan.
 * Een nieuw register verschijnt dus vanzelf in de documentatie, en een register
 * dat in een bepaalde installatie ontbreekt (zoals de DPIA-module) valt vanzelf
 * weg.
 *
 * Wat NIET uit de code komt is de redactionele omlijsting: de inleiding, de
 * leeswijzer en de slothoofdstukken. Die staan als losse markdownbestanden in
 * docs/handgeschreven/ en worden hier alleen tussengevoegd. Zo blijft de tekst
 * die een lezer overtuigt handwerk, terwijl de feiten automatisch kloppen.
 */
class DocsDatamodel extends Command
{
    protected $signature = 'docs:datamodel
        {--output= : Pad van het te schrijven markdownbestand}
        {--prose= : Map met de handgeschreven hoofdstukken}
        {--panel=admin : Het Filament-paneel waaruit de registers komen}';

    protected $description = 'Genereer de datamodel-documentatie uit de formulierdefinities';

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
     * Sectienotities uit #[DocNote], op naam van het eerste veld in de sectie.
     *
     * @var array<string, string>
     */
    private array $noteFingerprints = [];

    public function handle(): int
    {
        $outputOption = $this->option('output');
        $output = is_string($outputOption) && $outputOption !== ''
            ? $outputOption
            : base_path('../../docs/gegevensmodel-verwerkingen.md');

        $proseOption = $this->option('prose');
        $proseDir = is_string($proseOption) && $proseOption !== ''
            ? $proseOption
            : base_path('../../docs/handgeschreven');

        $this->bootContext();

        $registers = $this->discoverRegisters();

        if ($registers === []) {
            $this->error('Geen registers gevonden in de navigatiegroep "Registers".');

            return self::FAILURE;
        }

        $chapters = [];
        $models = [];

        foreach ($registers as $resourceClass) {
            $title = $this->toText($resourceClass::getPluralModelLabel());

            $this->loadNotes($resourceClass);

            try {
                $chapters[] = $this->renderRegister($resourceClass, $title);

                $model = $resourceClass::getModel();
                if (is_subclass_of($model, Model::class)) {
                    $models[$title] = $model;
                }
                $this->info('Gelezen: ' . $title);
            } catch (Throwable $e) {
                $this->error('Mislukt: ' . $title . ' - ' . $e->getMessage());

                return self::FAILURE;
            }
        }

        $markdown = $this->assemble($chapters, $proseDir, $models);

        $directory = dirname($output);
        if (!is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }
        file_put_contents($output, $markdown);

        $this->info('Geschreven naar ' . $output);

        return self::SUCCESS;
    }

    /**
     * Alle resources die in de navigatiegroep "Registers" staan, in de volgorde
     * waarin ze ook in het menu verschijnen.
     *
     * Hierdoor hoeft dit commando geen lijst met registers te kennen: wat de
     * gebruiker als register ziet, komt in de documentatie.
     *
     * @return array<int, class-string<Resource>>
     */
    private function discoverRegisters(): array
    {
        $panelId = $this->option('panel');
        $panel = is_string($panelId) && $panelId !== ''
            ? Filament::getPanel($panelId)
            : Filament::getDefaultPanel();

        $group = __(NavigationGroup::REGISTERS->value);

        $registers = [];

        foreach ($panel->getResources() as $resourceClass) {
            try {
                if ($resourceClass::getNavigationGroup() !== $group) {
                    continue;
                }
            } catch (Throwable) {
                continue;
            }

            if (!is_subclass_of($resourceClass, Resource::class)) {
                continue;
            }

            // Zonder formulier valt er niets te documenteren.
            if (!$this->hasForm($resourceClass)) {
                continue;
            }

            $registers[] = $resourceClass;
        }

        usort($registers, static function (string $a, string $b): int {
            return ($a::getNavigationSort() ?? PHP_INT_MAX) <=> ($b::getNavigationSort() ?? PHP_INT_MAX);
        });

        return $registers;
    }

    /**
     * @param class-string<Resource> $resourceClass
     */
    private function hasForm(string $resourceClass): bool
    {
        $reflection = new ReflectionClass($resourceClass);

        if (!$reflection->hasMethod('form')) {
            return false;
        }

        // Een resource die form() niet zelf invult, erft de lege standaard.
        return $reflection->getMethod('form')->getDeclaringClass()->getName() === $resourceClass;
    }

    /**
     * De formulieren gaan uit van een ingelogde gebruiker binnen een
     * organisatie. Voor het uitlezen van de structuur maakt het niet uit wie
     * dat is, dus zetten we een tijdelijke context neer; er wordt niets
     * opgeslagen.
     *
     * De voorkeur staat op ONE_PAGE, want dat is de indeling waarin alle
     * secties onder elkaar staan en dus de volledige inhoud zichtbaar is.
     */
    private function bootContext(): void
    {
        $organisation = new Organisation(['name' => 'Documentatie']);
        $organisation->id = Uuid::fromString(Str::uuid()->toString());
        Filament::setTenant($organisation, isQuiet: true);

        $user = new User(['name' => 'Documentatie', 'email' => 'docs@example.org']);
        $user->id = Uuid::fromString(Str::uuid()->toString());
        $user->register_layout = RegisterLayout::ONE_PAGE;
        Auth::setUser($user);
    }

    /**
     * Leest de #[DocNote]-attributen die bij de secties van dit register horen.
     *
     * De notities staan bij de schema-methodes (getStakeholder() en dergelijke).
     * Die klassen worden gevonden via de naam van de resource: naast
     * FooResource hoort FooResource\FooResourceFormSchemas.
     *
     * De koppeling met een sectie loopt niet via de methodenaam - die is Engels
     * en de sectiekop Nederlands - maar via het eerste veld dat de methode
     * oplevert. Dat veld komt terug in de sectie waar de methode bij hoort.
     *
     * @param class-string<Resource> $resourceClass
     */
    private function loadNotes(string $resourceClass): void
    {
        $this->noteFingerprints = [];

        foreach ($this->schemaClassesFor($resourceClass) as $schemaClass) {
            $reflection = new ReflectionClass($schemaClass);

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_STATIC) as $method) {
                foreach ($method->getAttributes(DocNote::class) as $attribute) {
                    /** @var DocNote $note */
                    $note = $attribute->newInstance();

                    $fingerprint = $this->fingerprintOf($method);
                    if ($fingerprint === null) {
                        continue;
                    }

                    $this->noteFingerprints[$fingerprint] = $this->tidy($note->text);
                }
            }
        }
    }

    /**
     * De klassen met schema-methodes die bij een resource horen.
     *
     * @param class-string<Resource> $resourceClass
     *
     * @return array<int, class-string>
     */
    private function schemaClassesFor(string $resourceClass): array
    {
        // FooResource staat in App\Filament\Resources; de bijbehorende schema's
        // in de gelijknamige submap daaronder.
        $fileName = (new ReflectionClass($resourceClass))->getFileName();
        if ($fileName === false) {
            return [];
        }

        $directory = dirname($fileName) . '/' . class_basename($resourceClass);

        if (!is_dir($directory)) {
            return [];
        }

        $files = glob($directory . '/*Schemas.php');
        if ($files === false) {
            return [];
        }

        $namespace = (new ReflectionClass($resourceClass))->getNamespaceName()
            . '\\' . class_basename($resourceClass);

        $classes = [];
        foreach ($files as $file) {
            /** @var class-string $class */
            $class = $namespace . '\\' . basename($file, '.php');

            if (!class_exists($class)) {
                continue;
            }

            $classes[] = $class;
        }

        return $classes;
    }

    /**
     * De naam van het eerste echte veld dat een schema-methode oplevert.
     *
     * Dat veld is uniek genoeg om de sectie mee terug te vinden, en het
     * aanroepen van de methode is verder ongevaarlijk: er wordt alleen een
     * formulierdefinitie opgebouwd.
     */
    private function fingerprintOf(ReflectionMethod $method): ?string
    {
        if ($method->getNumberOfRequiredParameters() > 0) {
            return null;
        }

        try {
            $components = $method->invoke(null);
        } catch (Throwable) {
            return null;
        }

        if (!is_array($components)) {
            return null;
        }

        return $this->firstFieldName($components);
    }

    /**
     * @param array<mixed> $components
     */
    private function firstFieldName(array $components): ?string
    {
        foreach ($components as $component) {
            if (!$component instanceof Component) {
                continue;
            }

            if ($component instanceof Hidden || $component instanceof Placeholder) {
                continue;
            }

            if ($component instanceof Field) {
                try {
                    $name = $component->getName();
                } catch (Throwable) {
                    continue;
                }

                if ($name !== '') {
                    return $name;
                }
            }

            $nested = $this->firstFieldName($this->childrenOf($component));
            if ($nested !== null) {
                return $nested;
            }
        }

        return null;
    }

    /**
     * @param class-string<Resource> $resourceClass
     */
    private function renderRegister(string $resourceClass, string $title): string
    {
        $form = $resourceClass::form(Form::make($this->makeLivewireHost()));

        $lines = ['# ' . $title, ''];

        $description = $this->registerDescription($resourceClass);
        if ($description !== null) {
            $lines[] = $description;
            $lines[] = '';
        }

        foreach ($form->getComponents() as $section) {
            $heading = $this->headingOf($section);
            if ($heading === null) {
                continue;
            }

            $children = $this->childrenOf($section);

            $rows = [];
            $this->collect($children, $rows, 0);

            if ($rows === []) {
                continue;
            }

            $lines[] = '## ' . $heading;
            $lines[] = '';

            $note = $this->noteFor($children);
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
        }

        return implode("\n", $lines);
    }

    /**
     * Loopt de componentboom af en maakt er tabelregels van.
     *
     * Velden binnen een Repeater krijgen een inspringmarkering, zodat in het
     * document zichtbaar blijft dat ze bij het bovenliggende onderdeel horen en
     * meerdere keren kunnen voorkomen.
     *
     * @param array<int, Component> $components
     * @param array<int, array{field: string, kind: string, help: string}> $rows
     */
    private function collect(array $components, array &$rows, int $depth): void
    {
        foreach ($components as $component) {
            // Verborgen velden en informatieblokken horen niet in het overzicht:
            // de eerste ziet een invuller nooit, de tweede is hulptekst voor de
            // invuller en niet voor de lezer van dit document.
            if ($component instanceof Hidden || $component instanceof Placeholder) {
                continue;
            }

            if ($this->isInformationBlock($component)) {
                continue;
            }

            if ($component instanceof Repeater) {
                $this->addRow($rows, $component, $depth);
                $this->collect($this->childrenOf($component), $rows, $depth + 1);

                continue;
            }

            if ($component instanceof Field) {
                $this->addRow($rows, $component, $depth);

                continue;
            }

            // Een Section binnen een stap groepeert bij elkaar horende vragen
            // ("Is een GEB (DPIA) verplicht?", "Bijzondere gegevens"). Die kop
            // is inhoudelijk: zonder die context staan de vragen los in de lucht.
            if ($component instanceof Section) {
                $subheading = $this->headingOf($component);
                if ($subheading !== null && $subheading !== '') {
                    $rows[] = [
                        'field' => '**' . $this->escape($subheading) . '**',
                        'kind' => '',
                        'help' => $this->escape($this->descriptionOf($component)),
                    ];
                }
            }

            // Layoutcomponenten (Grid, Group, Section, Fieldset) hebben zelf
            // geen waarde; hun kinderen wel.
            $this->collect($this->childrenOf($component), $rows, $depth);
        }
    }

    /**
     * De kinderen van een component, of een lege lijst als die pas tijdens het
     * invullen te bepalen zijn.
     *
     * Sommige componenten stellen hun inhoud samen op basis van de registratie
     * die op dat moment open staat. Buiten een request bestaat die niet, en dan
     * werpt Filament een fout. Voor het overzicht is dat geen bezwaar: zulke
     * onderdelen hebben geen vaste velden om te documenteren.
     *
     * @return array<int, Component>
     */
    private function childrenOf(Component $component): array
    {
        try {
            return array_values($component->getChildComponents());
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @param array<int, array{field: string, kind: string, help: string}> $rows
     */
    private function addRow(array &$rows, Component $component, int $depth): void
    {
        $label = $this->labelOf($component);
        if ($label === null || $label === '') {
            return;
        }

        // Eén pijl per niveau: gegevens binnen een betrokkene binnen de
        // registratie krijgen er dus twee.
        $prefix = str_repeat('⤷ ', $depth);

        $rows[] = [
            'field' => $prefix . $this->escape($label),
            'kind' => $this->kindOf($component),
            'help' => $this->helpOf($component),
        ];
    }

    private function kindOf(Component $component): string
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
     * Herkent componenten die naar een andere registratie verwijzen.
     *
     * Deze zijn in dit project altijd van een eigen klasse (RelationTable,
     * SelectSingleWithLookup, ChildrenRelationTable), dus de naam is een
     * betrouwbaarder signaal dan de Filament-basisklasse.
     */
    private function isRelation(Component $component): bool
    {
        $name = class_basename($component);

        return str_contains($name, 'RelationTable')
            || str_contains($name, 'WithLookup')
            || str_contains($name, 'ParentSelect');
    }

    private function isInformationBlock(Component $component): bool
    {
        return str_contains(class_basename($component), 'InformationBlock');
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

    private function labelOf(Component $component): ?string
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

    /**
     * De beschrijving onder een sectiekop, bijvoorbeeld de uitleg bij de
     * GEB-vragenlijst of bij de bijzondere gegevens.
     */
    private function descriptionOf(Component $component): string
    {
        if (!$component instanceof Section) {
            return '';
        }

        try {
            $description = $component->getDescription();
        } catch (Throwable) {
            return '';
        }

        if (!$description instanceof Htmlable && !is_string($description)) {
            return '';
        }

        return $this->toText($description);
    }

    private function headingOf(Component $component): ?string
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
     * De hulptekst onder een veld. Dat is precies de uitleg die een invuller in
     * het scherm ziet, en daarmee de meest betrouwbare omschrijving die er is.
     *
     * Bij een keuzelijst worden de opties eraan toegevoegd: juist die
     * antwoordmogelijkheden laten zien wat het systeem kan vastleggen.
     */
    private function helpOf(Component $component): string
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

        $options = $this->optionsOf($component);
        if ($options !== []) {
            $parts[] = 'Keuze uit: ' . implode('; ', $options) . '.';
        }

        return $this->escape(implode(' ', $parts));
    }

    /**
     * @return array<int, string>
     */
    private function optionsOf(Component $component): array
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
     * De notitie bij een sectie, gevonden via het eerste veld dat erin staat.
     *
     * @param array<int, Component> $sectionComponents
     */
    private function noteFor(array $sectionComponents): ?string
    {
        $fingerprint = $this->firstFieldName($sectionComponents);

        if ($fingerprint === null) {
            return null;
        }

        return $this->noteFingerprints[$fingerprint] ?? null;
    }

    /**
     * De omschrijving van het register, uit het taalbestand van het model.
     *
     * @param class-string<Resource> $resourceClass
     */
    private function registerDescription(string $resourceClass): ?string
    {
        $model = $resourceClass::getModel();
        $key = Str::snake(class_basename($model)) . '.register_description';

        $translation = __($key);
        if (!is_string($translation) || $translation === $key) {
            return null;
        }

        return $this->tidy($translation);
    }

    /**
     * Voegt de handgeschreven hoofdstukken en de gegenereerde registers samen.
     *
     * De bestanden in de prose-map worden op naam gesorteerd ingevoegd:
     * alles met een naam die begint onder "50-" komt vóór de registers, de
     * rest erna. Zo bepaalt de bestandsnaam de plek in het document.
     *
     * @param array<int, string> $chapters
     * @param array<string, class-string<Model>> $models registertitel => model
     */
    private function assemble(array $chapters, string $proseDir, array $models): string
    {
        $before = [];
        $after = [];

        if (is_dir($proseDir)) {
            $files = glob($proseDir . '/*.md');
            if ($files === false) {
                $files = [];
            }
            sort($files);

            foreach ($files as $file) {
                $contents = rtrim((string) file_get_contents($file));
                if ($contents === '') {
                    continue;
                }

                // De hergebruik-tabel volgt uit de modellen; de handgeschreven
                // tekst geeft alleen aan waar hij komt te staan.
                $contents = str_replace(
                    '<!-- HERGEBRUIK-TABEL -->',
                    $this->renderSharedParts($models),
                    $contents,
                );

                if (basename($file) < '50-') {
                    $before[] = $contents;

                    continue;
                }

                $after[] = $contents;
            }
        }

        $parts = array_merge($before, $chapters, $after);

        // Waarschuwing vooraf: dit bestand wordt overschreven, de bron staat
        // elders. Een HTML-commentaar valt weg in de PDF.
        $header = "<!--\n"
            . "  Dit bestand wordt gegenereerd door `just docs-datamodel`.\n"
            . "  Wijzigingen hier gaan verloren.\n\n"
            . "  De veldtabellen komen uit de Filament-formulieren; pas die aan\n"
            . "  (labels en hulpteksten staan in resources/lang/nl/). De\n"
            . "  omringende tekst staat in docs/handgeschreven/.\n"
            . "-->\n\n";

        return $header . implode("\n\n---\n\n", $parts) . "\n";
    }

    /**
     * Bouwt de tabel met gedeelde onderdelen.
     *
     * Welke registers een onderdeel delen blijkt uit de traits op het model, dus
     * die tabel hoeft niet met de hand te worden bijgehouden.
     *
     * @param array<string, class-string<Model>> $models registertitel => model
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
                $this->escape($part),
                $this->escape(
                    count($users) === count($models)
                        ? 'Alle registers'
                        : implode(', ', $users),
                ),
            );
        }

        return implode("\n", $lines);
    }

    /**
     * Livewire-component die alleen dient als houder voor het formulier.
     */
    private function makeLivewireHost(): HasForms
    {
        return new class extends LivewireComponent implements HasForms
        {
            use InteractsWithForms;

            public function render(): string
            {
                return '';
            }
        };
    }

    /**
     * Maakt van meerregelige of ingesprongen tekst één nette regel.
     */
    private function tidy(string $text): string
    {
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    /**
     * Tekens die een markdowntabel zouden breken onschadelijk maken.
     */
    private function escape(string $text): string
    {
        return str_replace('|', '\\|', $text);
    }
}
