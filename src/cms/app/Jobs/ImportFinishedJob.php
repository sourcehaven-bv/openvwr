<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Components\Uuid\UuidInterface;
use App\Enums\Queue;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use function __;
use function e;

class ImportFinishedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $filename,
        public readonly UuidInterface $userId,
    ) {
        $this->onQueue(Queue::DEFAULT);
    }

    public function handle(): void
    {
        $user = User::where('id', $this->userId)->first();

        if ($user === null) {
            return;
        }

        Notification::make()
            ->title(__('import.finished'))
            ->body(e($this->filename))
            ->icon('heroicon-o-document-plus')
            ->success()
            ->sendToDatabase($user);
    }
}
