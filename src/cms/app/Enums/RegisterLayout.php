<?php

declare(strict_types=1);

namespace App\Enums;

use App\Models\User;
use Filament\Facades\Filament;

enum RegisterLayout: string
{
    case STEPS = 'steps';
    case ONE_PAGE = 'one_page';

    /**
     * The layout to build a register schema in.
     *
     * Normally the acting user's own preference. A schema is also built where
     * there is nobody acting - the queued export job rebuilds the resource to
     * read its columns - and the layout is a display preference that means
     * nothing there, so it falls back to the column default rather than
     * insisting on a user that this context does not have.
     */
    public static function forActingUser(): self
    {
        $user = Filament::auth()->user();

        return $user instanceof User
            ? $user->register_layout
            : self::STEPS;
    }
}
