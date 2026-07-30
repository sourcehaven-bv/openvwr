<?php

declare(strict_types=1);

namespace App\Documentation;

use Attribute;

/**
 * Toelichting bij een formuliersectie, bedoeld voor de gegenereerde
 * documentatie (`php artisan docs:datamodel`).
 *
 * Dit is een ander soort tekst dan de InformationBlockSection in het formulier
 * zelf: die legt aan een invuller uit hoe hij het veld moet invullen, terwijl
 * deze notitie aan een lezer buiten de organisatie uitlegt wat het systeem op
 * dit punt kan vastleggen. Beide staan los van elkaar en hebben een eigen
 * publiek.
 *
 * De notitie staat bij de schema-methode zodat hij in dezelfde wijziging
 * meegaat als de velden die hij beschrijft:
 *
 *     #[DocNote('Per categorie betrokkenen wordt vastgelegd welke gegevens
 *                worden verwerkt.')]
 *     public static function getStakeholder(): array
 *
 * Heeft geen effect op de applicatie; alleen de documentatiegenerator leest
 * deze attributen.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class DocNote
{
    public function __construct(
        public string $text,
    ) {
    }
}
