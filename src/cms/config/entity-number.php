<?php

declare(strict_types=1);

use App\Enums\EntityNumberType;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\DataBreachRecord;
use App\Models\Dpia\DpiaPrescanRecord;
use App\Models\Dpia\DpiaRecord;
use App\Models\Wpg\WpgProcessingRecord;

return [
    'model_types' => [
        // register
        AlgorithmRecord::class => EntityNumberType::REGISTER,
        AvgProcessorProcessingRecord::class => EntityNumberType::REGISTER,
        AvgResponsibleProcessingRecord::class => EntityNumberType::REGISTER,
        DpiaPrescanRecord::class => EntityNumberType::REGISTER,
        DpiaRecord::class => EntityNumberType::REGISTER,
        WpgProcessingRecord::class => EntityNumberType::REGISTER,

        // databreach
        DataBreachRecord::class => EntityNumberType::DATABREACH,
    ],
];
