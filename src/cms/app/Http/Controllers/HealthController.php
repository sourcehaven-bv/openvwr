<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DatabaseHealthService;
use App\Services\Virusscanner\Virusscanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

use function response;
use function time;

use const JSON_PRETTY_PRINT;

readonly class HealthController
{
    public function __construct(
        private DatabaseHealthService $databaseHealthService,
        private Virusscanner $virusscanner,
    ) {
    }

    public function up(): Response
    {
        $isHealthy = $this->databaseHealthService->isHealthy()
            && $this->virusscanner->isHealthy();

        return response('', $isHealthy ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE);
    }

    public function __invoke(): JsonResponse
    {
        $databaseHealthy = $this->databaseHealthService->isHealthy();
        $virusscannerHealthy = $this->virusscanner->isHealthy();

        return response()->json(
            [
                'finishedAt' => time(),
                'checkResults' => [
                    [
                        'name' => 'Database',
                        'label' => 'Database Connection',
                        'status' => $databaseHealthy ? 'ok' : 'failed',
                        'notificationMessage' => $databaseHealthy ? '' : 'Database connection failed',
                        'shortSummary' => $databaseHealthy ? 'Connected' : 'Failed',
                        'meta' => [],
                    ],
                    [
                        'name' => 'Virusscanner',
                        'label' => 'Virus Scanner',
                        'status' => $virusscannerHealthy ? 'ok' : 'failed',
                        'notificationMessage' => $virusscannerHealthy ? '' : 'Virus scanner unavailable',
                        'shortSummary' => $virusscannerHealthy ? 'Available' : 'Unavailable',
                        'meta' => [],
                    ],
                ],
            ],
            options: JSON_PRETTY_PRINT,
        );
    }
}
