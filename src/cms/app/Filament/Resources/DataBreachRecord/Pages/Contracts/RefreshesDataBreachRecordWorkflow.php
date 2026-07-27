<?php

declare(strict_types=1);

namespace App\Filament\Resources\DataBreachRecord\Pages\Contracts;

/**
 * A page that shows the data breach workflow actions and can bring them back in
 * step with the record after a transition, without reloading the page.
 */
interface RefreshesDataBreachRecordWorkflow
{
    public function refreshDataBreachRecordHeaderActions(): void;
}
