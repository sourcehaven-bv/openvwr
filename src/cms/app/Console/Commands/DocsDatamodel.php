<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Documentation\DocumentAssembler;
use App\Documentation\FieldDescriber;
use App\Documentation\FormEnvironment;
use App\Documentation\RegisterFinder;
use App\Documentation\RegisterRenderer;
use App\Documentation\SectionNotes;
use Filament\Forms\Form;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Throwable;

use function base_path;
use function dirname;
use function file_put_contents;
use function is_dir;
use function is_string;
use function is_subclass_of;
use function mkdir;

/**
 * Genereert de registerhoofdstukken van de datamodel-documentatie uit de
 * Filament-formulieren zelf.
 *
 * De formulieren zijn de enige plek waar de velden, hun labels en hun
 * hulpteksten echt staan; die met de hand overschrijven levert een document op
 * dat stilletjes veroudert. Deze generator leest daarom de echte schema's uit
 * en schrijft daar markdowntabellen van.
 *
 * Welke registers erin komen wordt niet hier vastgelegd: RegisterFinder vraagt
 * het Filament-paneel welke resources in de navigatiegroep "Registers" staan.
 * Een nieuw register verschijnt dus vanzelf in de documentatie, en een register
 * dat in een bepaalde installatie ontbreekt (zoals de DPIA-module) valt vanzelf
 * weg.
 *
 * Wat NIET uit de code komt is de redactionele omlijsting: de inleiding, de
 * leeswijzer en de slothoofdstukken. Die staan als losse markdownbestanden in
 * docs/handgeschreven/ en worden er alleen tussengevoegd. Zo blijft de tekst
 * die een lezer overtuigt handwerk, terwijl de feiten automatisch kloppen.
 */
class DocsDatamodel extends Command
{
    protected $signature = 'docs:datamodel
        {--output= : Pad van het te schrijven markdownbestand}
        {--prose= : Map met de handgeschreven hoofdstukken}
        {--panel=admin : Het Filament-paneel waaruit de registers komen}';

    protected $description = 'Genereer de datamodel-documentatie uit de formulierdefinities';

    public function handle(): int
    {
        $environment = new FormEnvironment();
        $environment->boot();

        $describer = new FieldDescriber();
        $notes = new SectionNotes($environment, $describer);
        $renderer = new RegisterRenderer($describer, $notes);

        $registers = (new RegisterFinder())->find($this->option('panel'));

        if ($registers === []) {
            $this->error('Geen registers gevonden in de navigatiegroep "Registers".');

            return self::FAILURE;
        }

        $chapters = [];
        $models = [];

        foreach ($registers as $resourceClass) {
            $title = $describer->tidy($resourceClass::getPluralModelLabel());

            try {
                $notes->load($resourceClass);

                $form = $environment->run(
                    static fn (): mixed => $resourceClass::form(Form::make($environment->makeFormHost())),
                );

                $chapters[] = $renderer->render($form, $resourceClass, $title);
            } catch (Throwable $e) {
                $this->error('Mislukt: ' . $title . ' - ' . $e->getMessage());

                return self::FAILURE;
            }

            $model = $resourceClass::getModel();
            if (is_subclass_of($model, Model::class)) {
                $models[$title] = $model;
            }

            $this->info('Gelezen: ' . $title);
        }

        $output = $this->pathOption('output', '../../docs/gegevensmodel-verwerkingen.md');
        $proseDir = $this->pathOption('prose', '../../docs/handgeschreven');

        $markdown = (new DocumentAssembler())->assemble($chapters, $proseDir, $models);

        $this->write($output, $markdown);
        $this->info('Geschreven naar ' . $output);

        return self::SUCCESS;
    }

    private function pathOption(string $name, string $default): string
    {
        $value = $this->option($name);

        return is_string($value) && $value !== '' ? $value : base_path($default);
    }

    private function write(string $path, string $contents): void
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }

        file_put_contents($path, $contents);
    }
}
