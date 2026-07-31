<?php

declare(strict_types=1);

use App\Documentation\FormEnvironment;
use App\Models\Organisation;
use Filament\Facades\Filament;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

afterEach(function (): void {
    if (isset($this->environment)) {
        $this->environment->restore();
    }
});

it('sets up a tenant and a user', function (): void {
    $this->environment = new FormEnvironment();
    $this->environment->boot();

    expect(Filament::getTenant())->toBeInstanceOf(Organisation::class);
    expect(Auth::user())->not->toBeNull();
});

it('switches to an empty database', function (): void {
    $this->environment = new FormEnvironment();
    $this->environment->boot();

    expect(DB::getDefaultConnection())->toBe('docs');
});

it('puts the previous connection back', function (): void {
    $before = DB::getDefaultConnection();

    $environment = new FormEnvironment();
    $environment->boot();
    $environment->restore();

    expect(DB::getDefaultConnection())->toBe($before);
});

it('does nothing when restoring twice', function (): void {
    $before = DB::getDefaultConnection();

    $environment = new FormEnvironment();
    $environment->boot();
    $environment->restore();
    $environment->restore();

    expect(DB::getDefaultConnection())->toBe($before);
});

it('creates a missing table on the fly', function (): void {
    $this->environment = new FormEnvironment();
    $this->environment->boot();

    // The table does not exist; run() should create it and let the query
    // succeed. Empty is exactly right: this is about the structure of the
    // form, not the contents of a register.
    $result = $this->environment->run(
        static fn (): mixed => DB::table('een_tabel_die_niet_bestaat')->get(),
    );

    expect($result)->toHaveCount(0);
});

it('creates a missing column on the fly', function (): void {
    $this->environment = new FormEnvironment();
    $this->environment->boot();

    $result = $this->environment->run(
        static fn (): mixed => DB::table('nog_een_tabel')->where('een_kolom', true)->get(),
    );

    expect($result)->toHaveCount(0);
});

it('creates a column that the query names with its table', function (): void {
    $this->environment = new FormEnvironment();
    $this->environment->boot();

    // Filament often builds "table"."column"; SQLite then reports the column
    // qualified. Both forms must be recognised.
    $result = $this->environment->run(
        static fn (): mixed => DB::table('derde_tabel')
            ->where('derde_tabel.gekwalificeerd', true)
            ->get(),
    );

    expect($result)->toHaveCount(0);
});

it('passes on an error it cannot repair', function (): void {
    $this->environment = new FormEnvironment();
    $this->environment->boot();

    // A malformed query is not a missing table; it should propagate.
    expect(fn () => $this->environment->run(
        static fn (): mixed => DB::select('dit is geen sql'),
    ))->toThrow(QueryException::class);
});

it('provides a host for building a form', function (): void {
    $this->environment = new FormEnvironment();
    $this->environment->boot();

    expect($this->environment->makeFormHost())
        ->toBeInstanceOf(HasForms::class);
});

it('has a host that renders nothing', function (): void {
    $this->environment = new FormEnvironment();
    $this->environment->boot();

    expect($this->environment->makeFormHost()->render())->toBe('');
});

it('leaves the mass-assignment guard alone', function (): void {
    // Model::unguard() is global and permanent: switching it off here would let
    // unrelated tests write to columns that do not exist, far away from this
    // code and hard to trace back.
    $this->environment = new FormEnvironment();
    $this->environment->boot();

    expect(Model::isUnguarded())->toBeFalse();
});

it('gives up when the repairs never end', function (): void {
    $this->environment = new FormEnvironment();
    $this->environment->boot();

    // A query naming a new table every time can never be satisfied by creating
    // one more table; the loop must stop rather than spin forever.
    $attempt = 0;

    expect(fn () => $this->environment->run(static function () use (&$attempt): mixed {
        $attempt++;

        return DB::table('endless_table_' . $attempt)->get();
    }))->toThrow(RuntimeException::class, 'Too many missing tables');
});

it('creates a column the query names without its table', function (): void {
    $this->environment = new FormEnvironment();
    $this->environment->boot();

    // Not every query qualifies its columns; SQLite then reports just the
    // column name and the table has to come from the query itself.
    $result = $this->environment->run(
        static fn (): mixed => DB::select('select unqualified from fourth_table'),
    );

    expect($result)->toHaveCount(0);
});

it('cannot repair a missing column without a table to add it to', function (): void {
    $this->environment = new FormEnvironment();
    $this->environment->boot();

    // A column error from a query without a FROM clause leaves nowhere to add
    // the column, so the error propagates.
    expect(fn () => $this->environment->run(
        static fn (): mixed => DB::select('select a_missing_column'),
    ))->toThrow(QueryException::class);
});
