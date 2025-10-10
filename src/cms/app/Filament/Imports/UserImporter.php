<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Facades\Authentication;
use App\Filament\Resources\Resource;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

use function __;
use function number_format;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label(__('user.name'))
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('email')
                ->label(__('user.email'))
                ->requiredMapping()
                ->castStateUsing(static function (string $state): string {
                    return Str::lower($state);
                })
                ->rules(['required', 'max:255']),
        ];
    }

    public function afterSave(): void
    {
        $user = $this->record;
        Assert::isInstanceOf($user, User::class);

        $user->organisations()->attach(Authentication::organisation()->id);
        $user->save();
    }

    public function resolveRecord(): User
    {
        return User::withoutGlobalScopes()
            ->firstOrNew([
                'email' => $this->data['email'],
                'deleted_at' => null,
            ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        /** @var class-string<Model> $model */
        $model = $import->importer::$model;

        /** @var class-string<Resource> $resource */
        $resource = Filament::getModelResource($model);

        return __('import.notification.body', [
            'total_rows' => number_format($import->total_rows),
            'successful_rows' => number_format($import->successful_rows),
            'failed_rows' => number_format($import->getFailedRowsCount()),
            'model' => $resource::getPluralModelLabel(),
        ]);
    }
}
