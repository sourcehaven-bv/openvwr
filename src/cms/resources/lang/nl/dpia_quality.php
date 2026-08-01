<?php

declare(strict_types=1);

// Aandachtspunten bij een DPIA. Dit zijn adviezen, geen blokkades: de invuller
// beslist wanneer de DPIA af is. De teksten benoemen daarom wat er ontbreekt en
// waarom dat uitmaakt, zonder te stellen dat er iets fout is.
return [
    'paragraph' => 'Paragraaf :paragraph',
    'unnamed' => 'zonder omschrijving',

    'heading' => 'Aandachtspunten',
    'save_heading' => 'Er zijn aandachtspunten',
    'save_description' => 'De DPIA kan gewoon worden opgeslagen. Deze punten zijn het bekijken waard voordat u de DPIA laat vaststellen.',
    'save_anyway' => 'Toch opslaan',
    'back_to_form' => 'Terug naar het formulier',
    'none' => 'Er zijn op dit moment geen aandachtspunten gevonden.',
    'count' => '{1} 1 aandachtspunt|[2,*] :count aandachtspunten',
    'section_notice' => 'Let op bij deze paragraaf:',
    'section_risks_without_measure' => "Deze risico's zijn nog niet aan een maatregel gekoppeld:",
    'section_measures_without_risk' => "Deze maatregelen pakken nog geen risico aan:",
    'section_high_residual_risk' => 'Er blijft een hoog restrisico over. Lukt het niet dat te verlagen, raadpleeg dan de Autoriteit Persoonsgegevens voordat met de verwerking wordt begonnen (artikel 36 AVG).',
    'and_more' => '{1} En nog 1 ander aandachtspunt.|[2,*] En nog :count andere aandachtspunten.',

    'personal_data_without_exception_ground' =>
        'Bij ":gegeven" is het type ":type" gekozen. Die gegevens mogen in beginsel niet worden verwerkt; vul de uitzonderingsgrond in.',
    'transfer_without_mechanism' =>
        'Er worden gegevens buiten de EER verwerkt, maar er is nog geen doorgiftemechanisme beschreven.',
    'risk_without_measure' =>
        'Het risico ":risico" heeft nog geen maatregel. Is het risico aanvaard, licht dat dan toe bij de acceptatie van restrisico\'s.',
    'risk_deviates_without_motivation' =>
        'Het risiconiveau van ":risico" wijkt af van de matrix (:niveau). Licht die afwijking toe bij de motivatie.',
    'measure_without_risk' =>
        'De maatregel ":maatregel" is nog niet aan een risico gekoppeld. Het model vraagt te beschrijven welke maatregel welk risico aanpakt.',
    'high_residual_risk_without_ap' =>
        'Er blijft een hoog restrisico over. Lukt het niet dat te verlagen, dan moet de Autoriteit Persoonsgegevens vooraf worden geraadpleegd (artikel 36 AVG).',
    'high_residual_risk_without_acceptance' =>
        'Er blijft een hoog restrisico over zonder onderbouwing waarom dat aanvaardbaar is.',
];
