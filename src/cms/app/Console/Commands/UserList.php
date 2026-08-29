<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

class UserList extends Command
{
    protected $signature = 'user:list {--filter=} {--json}';
    protected $description = 'List current users';

    public function handle(): int
    {
        $userQuery = User::query();

        $filter = $this->option('filter');
        if ($filter !== null) {
            $filter = sprintf('%%%s%%', $filter);
            $userQuery->whereLike('email', $filter);
            $userQuery->orWhereLike('name', $filter);
        }

        $users = $userQuery->get(['name', 'email'])->toArray();

        if ($this->option('json')) {
            $this->line(json_encode($users, JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }

        $this->table(['Name', 'Email'], $users);

        return self::SUCCESS;
    }
}
