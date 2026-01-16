<?php

declare(strict_types=1);

namespace App\Import;

use App\Components\Uuid\UuidInterface;
use Illuminate\Database\Eloquent\Model;

interface Importer
{
    /**
     * @param class-string<Factory<Model>> $factoryClass
     */
    public function import(string $filename, string $input, string $factoryClass, UuidInterface $userId, string $organisationId): void;
}
