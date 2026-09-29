<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Bewaartermijn voor verwijderde gegevens
    |--------------------------------------------------------------------------
    |
    | Aantal dagen dat een soft-deleted record herstelbaar blijft. Daarna ruimt
    | `cleanup:soft-deleted` het definitief op (forceDelete).
    |
    | Onderbouwing van de standaard van 90 dagen: lang genoeg om een vergissing
    | te herstellen -- de reden dat soft-delete bestaat -- en kort genoeg om
    | tegenover een betrokkene te verdedigen bij een verwijderverzoek
    | (art. 17 AVG). 30 dagen is krap rond vakanties; een jaar is lastig uit te
    | leggen.
    |
    */
    'retention_days' => (int) env('CLEANUP_RETENTION_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | Maximum aantal records per model per run
    |--------------------------------------------------------------------------
    |
    | Begrenst de duur van een enkele geplande run, zodat een grote achterstand
    | over meerdere nachten wordt weggewerkt in plaats van in één lange
    | transactie. Nul betekent onbeperkt.
    |
    */
    'batch_size' => (int) env('CLEANUP_BATCH_SIZE', 500),
];
