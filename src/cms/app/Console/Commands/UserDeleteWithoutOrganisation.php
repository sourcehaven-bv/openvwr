<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UserDeleteWithoutOrganisation extends Command
{
    protected $signature = 'user:delete-without-organisation';
    protected $description = 'Delete users that are not attached to an organisation';

    public function handle(): int
    {
        $users = User::doesntHave('organisations')
            ->limit(100)
            ->get();

        foreach ($users as $user) {
            $user->delete();
        }

        return self::SUCCESS;
    }
}
