<?php

declare(strict_types=1);

namespace Tests\Fixtures\Filament;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

enum FormattedExportLabel: string implements HasLabel
{
    case EXAMPLE = 'example';

    public function getLabel(): Htmlable
    {
        return new HtmlString('<strong>Leesbare waarde</strong>');
    }
}
