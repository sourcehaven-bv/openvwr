<?php

declare(strict_types=1);

namespace App\Documentation;

use App\Components\Uuid\Uuid;
use App\Enums\RegisterLayout;
use App\Models\Organisation;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Component as LivewireComponent;
use PDO;
use RuntimeException;

use function config;
use function in_array;
use function preg_match;

/**
 * Zet de omgeving klaar waarin een formulier zonder request en zonder database
 * opgebouwd kan worden.
 *
 * Filament-formulieren gaan uit van een ingelogde gebruiker binnen een
 * organisatie, en sommige velden doen tijdens het opbouwen al een query. Voor
 * het uitlezen van de structuur is geen van beide inhoudelijk van belang, dus
 * wordt hier een lege context neergezet. Er wordt niets opgeslagen.
 */
class FormEnvironment
{
    private const CONNECTION = 'docs';

    /**
     * Hoe vaak een ontbrekende tabel of kolom bijgemaakt mag worden voordat we
     * concluderen dat er iets anders aan de hand is.
     */
    private const MAX_REPAIRS = 60;

    public function boot(): void
    {
        $this->bootDatabase();
        $this->bootTenant();
    }

    /**
     * Voert iets uit dat een formulier opbouwt, en maakt onderweg de tabellen en
     * kolommen aan waar dat formulier om vraagt.
     *
     * Welke dat zijn hangt af van de formulieren zelf; die lijst bijhouden zou
     * een vorm van hardcoderen zijn. In plaats daarvan wordt de fout opgevangen
     * en het ontbrekende stuk alsnog gemaakt, waarna het opnieuw gaat. Alles
     * blijft leeg: het gaat om de structuur, niet om de inhoud van een register.
     */
    public function run(callable $callback): mixed
    {
        for ($attempt = 0; $attempt < self::MAX_REPAIRS; $attempt++) {
            try {
                return $callback();
            } catch (QueryException $e) {
                if (!$this->repairSchema($e)) {
                    throw $e;
                }
            }
        }

        return $callback();
    }

    /**
     * Een Livewire-component die alleen dient als houder voor het formulier.
     */
    public function makeFormHost(): HasForms
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
     * Wijst de database naar een lege SQLite in het geheugen.
     *
     * De volledige migratie draaien lukt niet: die is op PostgreSQL geschreven.
     * De tabellen worden daarom pas aangemaakt wanneer een formulier ze nodig
     * heeft; zie run().
     */
    private function bootDatabase(): void
    {
        config([
            'database.connections.' . self::CONNECTION => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);

        DB::purge(self::CONNECTION);
        DB::setDefaultConnection(self::CONNECTION);

        // Zonder de SQLite-driver mislukt elke query met "could not find
        // driver", en dat is geen fout die run() kan verhelpen. Beter hier
        // duidelijk maken wat er ontbreekt dan verderop stranden.
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            throw new RuntimeException(
                'De PHP-extensie pdo_sqlite ontbreekt. Die is nodig om de '
                . 'formulieren op te bouwen zonder database (installeer php-sqlite3).',
            );
        }

        Model::unguard();
    }

    /**
     * Zet een tijdelijke organisatie en gebruiker neer.
     *
     * De voorkeur staat op ONE_PAGE: dat is de indeling waarin alle secties
     * onder elkaar staan en dus de volledige inhoud zichtbaar is.
     */
    private function bootTenant(): void
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
     * Maakt de ontbrekende tabel of kolom uit een SQLite-fout alsnog aan.
     *
     * Geeft false terug als de fout ergens anders over gaat; die hoort dan
     * gewoon door te komen.
     */
    private function repairSchema(QueryException $exception): bool
    {
        $message = $exception->getMessage();

        if (preg_match('/no such table: ([A-Za-z0-9_]+)/', $message, $matches) === 1) {
            Schema::connection(self::CONNECTION)->create(
                $matches[1],
                static function (Blueprint $blueprint): void {
                    $blueprint->string('id')->nullable();
                },
            );

            return true;
        }

        if (preg_match('/no such column: (?:([A-Za-z0-9_]+)\.)?([A-Za-z0-9_]+)/', $message, $matches) !== 1) {
            return false;
        }

        $table = $matches[1] !== '' ? $matches[1] : $this->tableFromQuery($exception);
        if ($table === null || !Schema::connection(self::CONNECTION)->hasTable($table)) {
            return false;
        }

        Schema::connection(self::CONNECTION)->table(
            $table,
            static function (Blueprint $blueprint) use ($matches): void {
                $blueprint->string($matches[2])->nullable();
            },
        );

        return true;
    }

    /**
     * De eerste tabelnaam uit de mislukte query, voor foutmeldingen die de tabel
     * niet zelf noemen.
     */
    private function tableFromQuery(QueryException $exception): ?string
    {
        if (preg_match('/\bfrom\s+"([A-Za-z0-9_]+)"/i', $exception->getSql(), $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }
}
