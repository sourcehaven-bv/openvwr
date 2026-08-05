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
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Component as LivewireComponent;
use RuntimeException;

use function config;
use function preg_match;

/**
 * Sets up the context a form needs to be built outside a request and without a
 * database.
 *
 * Filament forms assume a signed-in user within an organisation, and some fields
 * already run a query while the schema is assembled. Neither matters for reading
 * the structure, so a throwaway context is put in place. Nothing is persisted.
 */
class FormEnvironment
{
    private const CONNECTION = 'docs';

    /**
     * How many missing tables or columns may be created before we conclude that
     * something else is wrong.
     */
    private const MAX_REPAIRS = 60;

    /** The connection that was active before boot() was called. */
    private ?string $previousConnection = null;

    public function boot(): void
    {
        $this->bootDatabase();
        $this->bootTenant();
    }

    /**
     * Restores the database connection to what it was.
     *
     * In a test run the rest of the suite keeps using the real connection; this
     * generator must not switch it and leave it that way.
     */
    public function restore(): void
    {
        if ($this->previousConnection === null) {
            return;
        }

        DB::setDefaultConnection($this->previousConnection);
        DB::purge(self::CONNECTION);
        $this->previousConnection = null;
    }

    /**
     * Runs something that builds a form, creating the tables and columns that form
     * asks for along the way.
     *
     * Which ones those are depends on the forms themselves; keeping a list would
     * be a form of hardcoding. Instead the error is caught, the missing piece is
     * created, and the call is retried. Everything stays empty: this is about
     * structure, not about the contents of anyone's register.
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

        throw new RuntimeException('Too many missing tables or columns in a row.');
    }

    /**
     * A Livewire component that only serves as a host for the form.
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
     * Points the database at an empty in-memory SQLite.
     *
     * Running the full migration is not an option: it is written for PostgreSQL.
     * Tables are therefore created only once a form needs them; see run().
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

        $this->previousConnection = DB::getDefaultConnection();

        DB::purge(self::CONNECTION);
        DB::setDefaultConnection(self::CONNECTION);
    }

    /**
     * Puts a throwaway organisation and user in place.
     *
     * The preference is set to ONE_PAGE: that is the layout listing every section
     * below the other, so the full contents are visible.
     */
    private function bootTenant(): void
    {
        $organisation = new Organisation(['name' => 'Documentation']);
        $organisation->id = Uuid::fromString(Str::uuid()->toString());
        Filament::setTenant($organisation, isQuiet: true);

        $user = new User(['name' => 'Documentation', 'email' => 'docs@example.org']);
        $user->id = Uuid::fromString(Str::uuid()->toString());
        $user->register_layout = RegisterLayout::ONE_PAGE;
        Auth::setUser($user);
    }

    /**
     * Creates the table or column that a SQLite error reports as missing.
     *
     * Returns false when the error is about something else; that one should
     * simply propagate.
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

        // SQLite reports "table.column" when the query qualifies the column and just
        // the column name otherwise; in the latter case the table comes from the
        // query itself.
        if (preg_match('/no such column: (?:([A-Za-z0-9_]+)\.)?([A-Za-z0-9_]+)/', $message, $matches) !== 1) {
            return false;
        }

        $table = $matches[1] !== ''
            ? $matches[1]
            : $this->tableFromQuery($exception);

        if ($table === null) {
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
     * The table name from the failed query, for errors that do not qualify the
     * column.
     */
    private function tableFromQuery(QueryException $exception): ?string
    {
        if (preg_match('/\bfrom\s+"?([A-Za-z0-9_]+)"?/i', $exception->getSql(), $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }
}
