<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Facades\Authentication;
use App\Models\Contracts\EntityNumerable;
use App\Services\EntityNumberService;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use Webmozart\Assert\Assert;

use function __;

abstract class EntityNumberCreateRecord extends CreateRecord
{
    /**
     * @param array<string, mixed> $data
     *
     * @throws Halt
     */
    protected function handleRecordCreation(array $data): Model
    {
        /** @var EntityNumberService $entityNumberService */
        $entityNumberService = App::get(EntityNumberService::class);

        try {
            $model = DB::transaction(function () use ($entityNumberService, $data): Model {
                $model = static::getModel();
                Assert::subclassOf($model, EntityNumerable::class);

                $entityNumber = $entityNumberService->generate(Authentication::organisation(), $model);
                $data['entity_number_id'] = $entityNumber->id;

                return parent::handleRecordCreation($data);
            });
        } catch (Throwable $exception) {
            Log::warning('Core entity create failed', ['exception' => $exception]);

            Notification::make()
                ->title(__('general.number_create_failed'))
                ->send();

            // A PDOException carries a SQLSTATE such as "23502" as its code, which
            // is a string; Halt inherits Exception and only takes an int. Passing
            // it through would replace the real failure with a TypeError, so the
            // code is dropped and the original exception kept as the previous one.
            throw new Halt($exception->getMessage(), 0, $exception);
        }

        return $model;
    }
}
