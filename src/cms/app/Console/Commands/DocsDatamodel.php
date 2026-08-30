<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Filament\Schemas\Schema;
use App\Documentation\DocumentAssembler;
use App\Documentation\FieldDescriber;
use App\Documentation\FormEnvironment;
use App\Documentation\RegisterFinder;
use App\Documentation\RegisterRenderer;
use App\Documentation\SectionNotes;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Throwable;

use function base_path;
use function dirname;
use function file_put_contents;
use function is_dir;
use function is_string;
use function is_subclass_of;
use function mkdir;

/**
 * Generates the register chapters of the data-model documentation from the
 * Filament forms themselves.
 *
 * The forms are the only place where the fields, their labels and their helper
 * texts actually live; copying those by hand produces a document that quietly
 * goes stale. This generator therefore reads the real schemas and writes
 * markdown tables from them.
 *
 * Which registers are included is not fixed here: RegisterFinder asks the
 * Filament panel which resources sit in the "Registers" navigation group. A new
 * register thus shows up on its own, and one that is absent in a particular
 * installation (such as the DPIA module) drops out just as easily.
 *
 * What does NOT come from the code is the editorial framing: the introduction,
 * the reading guide and the closing chapters. Those live as separate markdown
 * files under the prose directory and are only slotted in. That keeps the text
 * that persuades a reader handwritten, while the facts stay correct by
 * construction.
 *
 * All texts are read in the active locale, so the same command produces a Dutch
 * or an English document depending on --locale.
 */
class DocsDatamodel extends Command
{
    protected $signature = 'docs:datamodel
        {--output= : Path of the markdown file to write}
        {--prose= : Directory holding the handwritten chapters}
        {--panel=admin : The Filament panel to take the registers from}
        {--locale= : Locale to render the documentation in (defaults to the app locale)}';

    protected $description = 'Generate the data-model documentation from the form definitions';

    public function handle(): int
    {
        $locale = $this->option('locale');
        $previousLocale = App::currentLocale();

        if (is_string($locale) && $locale !== '') {
            App::setLocale($locale);
        }

        $environment = new FormEnvironment();
        $environment->boot();

        try {
            return $this->generate($environment);
        } finally {
            $environment->restore();
            App::setLocale($previousLocale);
        }
    }

    private function generate(FormEnvironment $environment): int
    {
        $describer = new FieldDescriber();
        $notes = new SectionNotes($environment, $describer);
        $renderer = new RegisterRenderer($describer, $notes);

        try {
            $registers = (new RegisterFinder())->find($this->option('panel'));
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $chapters = [];
        $models = [];

        foreach ($registers as $resourceClass) {
            $title = $describer->tidy($resourceClass::getPluralModelLabel());

            try {
                $notes->load($resourceClass);

                $form = $environment->run(
                    static fn (): mixed => $resourceClass::form(Schema::make($environment->makeFormHost())),
                );

                $chapters[] = $renderer->render($form, $resourceClass, $title);
            } catch (Throwable $e) {
                $this->error('Failed: ' . $title . ' - ' . $e->getMessage());

                return self::FAILURE;
            }

            $model = $resourceClass::getModel();
            if (is_subclass_of($model, Model::class)) {
                $models[$title] = $model;
            }

            $this->info('Read: ' . $title);
        }

        $locale = App::currentLocale();

        $output = $this->pathOption('output', '../../docs/datamodel-' . $locale . '.md');
        $proseDir = $this->pathOption('prose', '../../docs/prose/' . $locale);

        $markdown = (new DocumentAssembler())->assemble($chapters, $proseDir, $models);

        $this->write($output, $markdown);
        $this->info('Written to ' . $output);

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
