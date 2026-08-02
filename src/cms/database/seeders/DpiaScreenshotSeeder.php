<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Dpia\DpiaSubjectType;
use App\Enums\Dpia\MeasureType;
use App\Enums\Dpia\PersonalDataType;
use App\Enums\Dpia\PrescanOutcome;
use App\Enums\Dpia\RiskLevel;
use App\Enums\EntityNumberType;
use App\Models\Dpia\DpiaMeasure;
use App\Models\Dpia\DpiaPersonalData;
use App\Models\Dpia\DpiaPrescanRecord;
use App\Models\Dpia\DpiaRecord;
use App\Models\Dpia\DpiaRisk;
use App\Models\Organisation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use function array_map;
use function count;

/**
 * Deterministic DPIA content for screenshots and demos.
 *
 * DpiaRecordFactory fills the seventeen paragraphs with faker latin
 * ("Fugiat culpa alias voluptas et."), which is unusable in material shown to
 * someone evaluating the product: the paragraphs of the Model DPIA Rijksdienst
 * are exactly what a reader wants to judge, so their content has to read as a
 * real DPIA rather than as placeholder text.
 *
 * This seeder replaces the generated DPIA records of the demo organisation with
 * one fully worked-out case - cameratoezicht in a parkeergarage - covering the
 * pre-scan, all seventeen paragraphs, the persoonsgegevens, the risks and the
 * measures including their residual risk.
 *
 * The case is deliberately mundane and self-contained: it triggers exactly one
 * AP criterion (cameratoezicht), which is enough to make a DPIA mandatory
 * without needing a contrived scenario, and it keeps every residual risk at or
 * below "gemiddeld" so no AP consultation is implied.
 *
 * Run after TestDataSeeder:
 *   php artisan db:seed --class=TestDataSeeder
 *   php artisan db:seed --class=DpiaScreenshotSeeder
 */
class DpiaScreenshotSeeder extends Seeder
{
    use CreatesEntityNumbers;
    use WithoutModelEvents;

    private const ORGANISATION_SLUG = 'nipg';

    private const NAME = 'Cameratoezicht parkeergarage';

    public function run(): void
    {
        $organisation = Organisation::query()
            ->where('slug', self::ORGANISATION_SLUG)
            ->firstOrFail();

        $this->removeGeneratedRecords($organisation);

        $prescan = $this->createPrescan($organisation);
        $record = $this->createRecord($organisation, $prescan);

        $this->createPersonalData($record);
        $this->createRisksAndMeasures($record);
    }

    /**
     * The factory-generated records carry latin paragraphs and names like
     * "test", and several are near-duplicates of each other. Removing them
     * leaves a single, coherent case rather than a list in which the worked-out
     * DPIA has to compete with noise.
     *
     * Force-deleted rather than soft-deleted: a soft-deleted record still shows
     * up in the "verwijderde items" filter, which is itself a screen worth
     * showing without prior latin in it.
     */
    private function removeGeneratedRecords(Organisation $organisation): void
    {
        DpiaRecord::query()
            ->withTrashed()
            ->where('organisation_id', $organisation->id)
            ->get()
            ->each(static fn (DpiaRecord $record) => $record->forceDelete());

        DpiaPrescanRecord::query()
            ->withTrashed()
            ->where('organisation_id', $organisation->id)
            ->get()
            ->each(static fn (DpiaPrescanRecord $prescan) => $prescan->forceDelete());
    }

    /**
     * The pre-scan that establishes the DPIA is mandatory. Cameratoezicht is an
     * AP criterion, and one AP hit is by itself decisive, so the outcome is
     * "verplicht" and the motivation says which criterion carried it.
     */
    private function createPrescan(Organisation $organisation): DpiaPrescanRecord
    {
        $prescan = new DpiaPrescanRecord();
        $prescan->organisation_id = $organisation->id;
        // WithoutModelEvents suppresses EntityNumerableObserver, which normally
        // issues the number, so it is drawn from the counter here instead -
        // otherwise the "Nummer verwerking" column is empty in every figure.
        $prescan->setEntityNumberId(
            $this->createEntityNumber($organisation, EntityNumberType::REGISTER)->id,
        );

        $prescan->fill([
            'name' => self::NAME,
            'description' => 'Voorgenomen cameratoezicht in de parkeergarage onder het hoofdkantoor, '
                . 'gericht op het voorkomen en oplossen van diefstal uit en van voertuigen en van '
                . 'vernieling van eigendommen. De garage is toegankelijk voor medewerkers en voor '
                . 'bezoekers met een afspraak.',
            'new_legislation' => false,
            'departmental_policy' => false,
            'public_cloud' => false,
            'ap_criteria' => ['cameratoezicht'],
            'edpb_criteria' => ['stelselmatige_monitoring'],
            'international_transfer' => false,
            'outside_eea' => false,
            'digital_service' => false,
            'minors' => false,
            'algorithm' => false,
            'high_risk_ai' => false,
            'outcome' => PrescanOutcome::REQUIRED,
            'outcome_motivation' => 'De verwerking valt onder het AP-criterium cameratoezicht: er wordt '
                . 'stelselmatig en grootschalig gemonitord in een voor een besloten groep toegankelijke '
                . 'ruimte. Eén AP-criterium is voldoende om een DPIA verplicht te maken. Daarnaast is '
                . 'sprake van stelselmatige monitoring in de zin van de EDPB-richtsnoeren.',
            'assessed_at' => '2026-02-10',
        ]);

        $prescan->save();

        return $prescan;
    }

    /**
     * The seventeen paragraphs of the Rijksmodel, filled as an invuller would.
     *
     * The text is kept to a few sentences per paragraph: long enough to show
     * what belongs in the field, short enough that a screenshot of the
     * paragraph stays readable.
     */
    private function createRecord(Organisation $organisation, DpiaPrescanRecord $prescan): DpiaRecord
    {
        $record = new DpiaRecord();
        $record->organisation_id = $organisation->id;
        $record->setEntityNumberId(
            $this->createEntityNumber($organisation, EntityNumberType::REGISTER)->id,
        );

        $record->fill([
            'dpia_prescan_record_id' => $prescan->id,
            'name' => self::NAME,
            'subject_type' => DpiaSubjectType::PROCESSING,

            // 1. Voorstel
            'proposal_description' => 'In de parkeergarage onder het hoofdkantoor worden acht vaste '
                . 'camera\'s geplaatst: bij de in- en uitrit, bij de twee looproutes naar de liften en '
                . 'verspreid over de parkeerdekken. De camera\'s registreren beeld, geen geluid. De '
                . 'beelden worden opgenomen en zijn alleen achteraf te raadplegen na een melding van '
                . 'een incident; er wordt niet live meegekeken.',
            'proposal_motivation' => 'In de afgelopen twee jaar zijn zestien incidenten gemeld: '
                . 'inbraak in geparkeerde auto\'s, diefstal van een dienstfiets en herhaalde '
                . 'vernieling van slagbomen. Zonder beeldmateriaal konden deze incidenten niet worden '
                . 'opgehelderd en kon de politie geen aangifte in behandeling nemen. Toegangsbeheer en '
                . 'extra verlichting zijn eerder ingevoerd maar hebben het aantal incidenten niet '
                . 'teruggebracht.',

            // 2. Persoonsgegevens - the repeater rows carry the detail
            'personal_data_description' => 'Het gaat om camerabeelden waarop personen herkenbaar in '
                . 'beeld komen, in combinatie met het tijdstip en de locatie van de opname en het '
                . 'kenteken van het voertuig waarmee de garage wordt binnengereden. Bij het '
                . 'raadplegen van beelden na een incident wordt vastgelegd wie de beelden heeft '
                . 'bekeken en met welke reden.',
            'personal_data_sources' => 'De beelden komen uit de camera\'s zelf. De kentekens worden '
                . 'gelezen door de kentekenherkenning bij de slagboom, die ook de toegang regelt. Er '
                . 'worden geen gegevens uit externe bronnen of basisregistraties toegevoegd.',

            // 3. Gegevensverwerkingen
            'processing_description' => 'De camera\'s nemen continu op. De beelden worden opgeslagen '
                . 'op een server in het eigen datacentrum en na de bewaartermijn automatisch '
                . 'overschreven. Alleen na een gemelde en geregistreerde incidentmelding worden '
                . 'beelden van het betreffende tijdvak bekeken door twee daartoe aangewezen '
                . 'medewerkers van Facilitair Beheer. Beelden worden uitsluitend verstrekt aan de '
                . 'politie op grond van een vordering.',
            'techniques_description' => 'Vaste IP-camera\'s met opname op een lokale server, en '
                . 'kentekenherkenning bij de slagboom voor de toegangsverlening. Er wordt geen '
                . 'gezichtsherkenning toegepast en beelden worden niet geanalyseerd op gedrag. Er '
                . 'vindt geen koppeling plaats met het personeelssysteem.',
            'automated_decision_making' => false,
            'profiling' => false,
            'cloud_processing' => false,
            'big_data_processing' => false,
            'techniques_explanation' => 'De opnameapparatuur staat in de serverruimte van het '
                . 'hoofdkantoor en is niet vanaf internet benaderbaar. Beheer op afstand is '
                . 'uitgeschakeld.',

            // 5. Verwerkingsdoeleinden
            'purpose_description' => 'Het beschermen van eigendommen van de organisatie, van '
                . 'medewerkers en van bezoekers tegen diefstal en vernieling, en het kunnen '
                . 'ophelderen van incidenten die zich in de garage voordoen. De beelden dienen '
                . 'daarnaast als bewijsmateriaal bij aangifte.',

            // 6. Betrokken partijen
            'parties_description' => 'De organisatie is verwerkingsverantwoordelijke. Het onderhoud '
                . 'van de camera-installatie is belegd bij een externe installateur, die als '
                . 'verwerker optreedt en uitsluitend bij storingen toegang heeft. De politie kan '
                . 'beelden vorderen en is dan zelfstandig verwerkingsverantwoordelijke.',
            'parties_access' => 'Twee aangewezen medewerkers van Facilitair Beheer kunnen beelden '
                . 'raadplegen na een geregistreerde melding. De installateur heeft alleen toegang '
                . 'onder begeleiding en met een vastgelegde reden. Overige medewerkers, waaronder '
                . 'leidinggevenden, hebben geen toegang tot beelden.',

            // 7. Belangen
            'interests_description' => 'Het belang van de organisatie is het beschermen van '
                . 'eigendommen en het kunnen ophelderen van incidenten. Het belang van medewerkers '
                . 'en bezoekers is een veilige parkeervoorziening waar hun voertuig en bezittingen '
                . 'niet worden beschadigd of ontvreemd.',
            'interests_data_subjects' => 'Betrokkenen hebben er belang bij niet permanent te worden '
                . 'gevolgd op hun werkplek en niet het gevoel te krijgen dat hun komst- en '
                . 'vertrektijden worden bijgehouden. Dat belang is meegewogen door niet live mee te '
                . 'kijken, geen beelden bij de werkplekken te maken en raadpleging te beperken tot '
                . 'concrete incidenten.',

            // 8. Verwerkingslocaties
            'processing_locations' => 'Nederland. De beelden staan op een server in het eigen '
                . 'datacentrum in Utrecht en verlaten dat datacentrum niet.',
            'outside_eea' => false,

            // 9. Juridisch en beleidsmatig kader
            'legal_policy_framework' => 'Artikel 6, eerste lid, onder f, AVG voor de verwerking zelf. '
                . 'Voor cameratoezicht op de werkvloer geldt daarnaast het instemmingsrecht van de '
                . 'ondernemingsraad op grond van artikel 27, eerste lid, onder l, van de Wet op de '
                . 'ondernemingsraden. Intern is het cameraprotocol van toepassing.',

            // 10. Bewaartermijnen
            'retention_periods' => 'Camerabeelden worden 28 dagen bewaard en daarna automatisch '
                . 'overschreven. Beelden die deel uitmaken van een lopend incidentonderzoek of van '
                . 'een aangifte worden apart bewaard tot dat onderzoek is afgerond. '
                . 'Raadplegingslogs worden één jaar bewaard.',
            'retention_motivation' => 'Vier weken is de termijn die de AP voor cameratoezicht als '
                . 'richtsnoer hanteert en sluit aan bij de praktijk: schade in de garage wordt in de '
                . 'regel binnen enkele dagen ontdekt, maar na een vakantieperiode kan dat langer '
                . 'duren. Een langere standaardtermijn is niet nodig gebleken.',
            'retention_responsible' => 'Facilitair Beheer bewaakt de bewaartermijn en controleert '
                . 'jaarlijks of de automatische overschrijving werkt.',

            // 11. Rechtsgrond
            'legal_basis' => 'Gerechtvaardigd belang, artikel 6, eerste lid, onder f, AVG.',
            'legal_basis_conditions' => 'Het gerechtvaardigd belang is de bescherming van eigendommen '
                . 'en de veiligheid in de garage. De verwerking is noodzakelijk omdat lichtere '
                . 'maatregelen het aantal incidenten niet hebben teruggebracht. Bij de afweging '
                . 'weegt mee dat niet live wordt meegekeken, dat er geen camera\'s op werkplekken '
                . 'staan en dat raadpleging alleen na een concreet incident plaatsvindt, waardoor '
                . 'het privacyverlies beperkt blijft.',

            // 12. Bijzondere persoonsgegevens
            'special_categories' => false,
            'national_identification_number' => false,

            // 13. Doelbinding
            'further_processing' => false,
            'purpose_limitation' => 'De beelden worden uitsluitend gebruikt voor het vastgestelde '
                . 'beveiligingsdoel. Gebruik voor het beoordelen of controleren van medewerkers, '
                . 'bijvoorbeeld voor aanwezigheid of werktijden, is in het cameraprotocol '
                . 'uitdrukkelijk uitgesloten en technisch beperkt doordat leidinggevenden geen '
                . 'toegang hebben.',

            // 14. Noodzaak en evenredigheid
            'necessity_proportionality' => 'Het aantal camera\'s is beperkt tot de plaatsen waar de '
                . 'incidenten zich voordeden: de in- en uitrit en de parkeerdekken. De looproutes '
                . 'naar de kantoren en de fietsenstalling met zicht op de kantine zijn buiten beeld '
                . 'gelaten. Door niet live mee te kijken en beelden alleen na een melding te '
                . 'raadplegen, staat de inbreuk in verhouding tot het beveiligingsdoel.',
            'necessity_subsidiarity' => 'Eerder zijn toegangspassen voor de garage, extra verlichting '
                . 'en periodieke surveillance ingevoerd. Die maatregelen blijven van kracht maar '
                . 'hebben de incidenten niet teruggebracht en maken het niet mogelijk incidenten '
                . 'achteraf op te helderen. Een bewakingsdienst met permanente aanwezigheid is '
                . 'overwogen maar is voor deze locatie disproportioneel duur en betekent voortdurende '
                . 'observatie door een persoon.',

            // 15. Rechten van de betrokkenen
            'data_subject_rights_procedure' => 'Bij de ingang van de garage en op het intranet staat '
                . 'vermeld dat cameratoezicht plaatsvindt, met welk doel en bij wie betrokkenen '
                . 'terechtkunnen. Verzoeken om inzage worden behandeld door de privacy officer, '
                . 'binnen een maand. Bij inzage in beelden waarop ook anderen herkenbaar zijn worden '
                . 'die anderen onherkenbaar gemaakt.',
            'rights_restricted' => false,

            // 16/17 toelichting
            'risks_additional_information' => 'De risico\'s zijn beoordeeld met Facilitair Beheer, de '
                . 'security officer en een vertegenwoordiging van de ondernemingsraad. De kans is '
                . 'geschat op basis van de incidenten van de afgelopen twee jaar.',
            'measures_additional_information' => 'De maatregelen zijn belegd bij Facilitair Beheer en '
                . 'opgenomen in het cameraprotocol. De werking wordt jaarlijks getoetst in de '
                . 'interne controle.',
            'residual_risk_acceptance' => 'Na de maatregelen resteert geen hoog risico. Het hoogste '
                . 'restrisico is gemiddeld en betreft onbedoeld breder gebruik van beelden; dat '
                . 'risico wordt aanvaard omdat het door protocol, autorisatie en logging voldoende '
                . 'wordt beheerst. Raadpleging van de Autoriteit Persoonsgegevens is daarmee niet '
                . 'aan de orde.',

            // Consultatie en advies
            'data_subjects_consulted' => true,
            'data_subjects_consultation' => 'De ondernemingsraad heeft op 3 maart 2026 ingestemd met '
                . 'het cameratoezicht, onder de voorwaarde dat er geen camera\'s bij de '
                . 'fietsenstalling en de kantine komen en dat de beelden niet worden gebruikt voor '
                . 'het beoordelen van medewerkers. Beide voorwaarden zijn overgenomen.',
            'fg_advice' => 'De functionaris voor gegevensbescherming adviseert positief, mits de '
                . 'bewaartermijn op 28 dagen blijft, de autorisaties beperkt blijven tot de twee '
                . 'aangewezen medewerkers en elke raadpleging wordt gelogd en jaarlijks '
                . 'gecontroleerd.',
            'fg_advice_followup' => 'De drie punten uit het advies zijn overgenomen in het '
                . 'cameraprotocol. De jaarlijkse controle op de raadpleginglogs is belegd bij de '
                . 'security officer.',
            'fg_advice_received_at' => '2026-03-17',
            'ap_consultation_required' => false,

            'assessed_at' => '2026-03-24',
            'review_at' => '2027-03-24',
            'management_summary' => 'Cameratoezicht in de parkeergarage is noodzakelijk gebleken na '
                . 'zestien incidenten in twee jaar die met toegangsbeheer en verlichting niet konden '
                . 'worden voorkomen of opgehelderd. De inbreuk is beperkt gehouden: acht camera\'s '
                . 'op de plaatsen waar incidenten plaatsvonden, geen zicht op werkplekken, geen live '
                . 'meekijken, raadpleging alleen na een gemelde incident en een bewaartermijn van 28 '
                . 'dagen. Na de maatregelen resteert geen hoog risico; de ondernemingsraad heeft '
                . 'ingestemd en de FG adviseert positief.',
        ]);

        $record->save();

        return $record;
    }

    /**
     * Paragraaf 2, as repeater rows. Kenteken is included because the slagboom
     * reads it, which is easy to overlook and makes the example realistic.
     */
    private function createPersonalData(DpiaRecord $record): void
    {
        $rows = [
            [
                'description' => 'Camerabeelden waarop personen herkenbaar in beeld komen',
                'type' => PersonalDataType::ORDINARY,
                'data_subject_category' => 'Medewerkers en bezoekers van de parkeergarage',
                'source' => 'Camera\'s bij de in- en uitrit en op de parkeerdekken',
                'retention_period' => '28 dagen, daarna automatisch overschreven',
            ],
            [
                'description' => 'Kenteken van het voertuig waarmee de garage wordt binnengereden',
                'type' => PersonalDataType::ORDINARY,
                'data_subject_category' => 'Bestuurders van voertuigen in de garage',
                'source' => 'Kentekenherkenning bij de slagboom',
                'retention_period' => '28 dagen',
            ],
            [
                'description' => 'Tijdstip en locatie van de opname',
                'type' => PersonalDataType::ORDINARY,
                'data_subject_category' => 'Medewerkers en bezoekers van de parkeergarage',
                'source' => 'Registratie door de camera-installatie',
                'retention_period' => '28 dagen',
            ],
            [
                'description' => 'Registratie van wie beelden heeft geraadpleegd en met welke reden',
                'type' => PersonalDataType::ORDINARY,
                'data_subject_category' => 'Medewerkers van Facilitair Beheer',
                'source' => 'Logging van het camerasysteem',
                'retention_period' => '1 jaar',
            ],
        ];

        foreach ($rows as $order => $attributes) {
            $personalData = new DpiaPersonalData();
            $personalData->organisation_id = $record->organisation_id;
            $personalData->dpia_record_id = $record->id;
            $personalData->fill($attributes + ['order_column' => $order + 1]);
            $personalData->save();
        }
    }

    /**
     * Paragraaf 16 and 17, with the pivot between them filled so the "welke
     * risico's dekt deze maatregel" field is not empty in the figures.
     *
     * Every residual level stays at or below gemiddeld, which keeps
     * requiresApConsultation() false and matches the acceptance text above.
     */
    private function createRisksAndMeasures(DpiaRecord $record): void
    {
        $risks = [];

        $riskDefinitions = [
            'onbevoegde_inzage' => [
                'title' => 'Onbevoegde inzage in camerabeelden',
                'description' => 'Medewerkers zonder rol in de afhandeling van incidenten, of de '
                    . 'installateur tijdens onderhoud, bekijken beelden zonder dat daar een melding '
                    . 'aan ten grondslag ligt.',
                'origin' => 'Ruime autorisaties en onderhoud op afstand',
                'likelihood' => RiskLevel::MEDIUM,
                'likelihood_motivation' => 'De installatie wordt enkele keren per jaar onderhouden en '
                    . 'het systeem kent van huis uit een gedeeld beheeraccount.',
                'impact' => RiskLevel::HIGH,
                'impact_motivation' => 'Beelden laten zien wie wanneer aanwezig was; oneigenlijk '
                    . 'gebruik raakt medewerkers direct in hun werkomgeving.',
                'level' => RiskLevel::HIGH,
                'level_motivation' => 'Reële kans in combinatie met een grote impact op betrokkenen.',
            ],
            'functieverandering' => [
                'title' => 'Gebruik van beelden voor het beoordelen van medewerkers',
                'description' => 'Beelden worden gebruikt om aanwezigheid, werktijden of gedrag van '
                    . 'medewerkers te controleren, terwijl ze voor beveiliging zijn verzameld.',
                'origin' => 'Doelverschuiving na verzoek van een leidinggevende',
                'likelihood' => RiskLevel::MEDIUM,
                'likelihood_motivation' => 'Verzoeken van leidinggevenden om "even mee te kijken" zijn '
                    . 'in de praktijk een bekend patroon bij cameratoezicht.',
                'impact' => RiskLevel::HIGH,
                'impact_motivation' => 'Betrokkenen worden beoordeeld op basis van gegevens die daar '
                    . 'niet voor zijn verzameld, wat de arbeidsverhouding raakt.',
                'level' => RiskLevel::HIGH,
                'level_motivation' => 'Zonder expliciete begrenzing is dit het meest waarschijnlijke '
                    . 'misbruikscenario.',
            ],
            'te_lang_bewaren' => [
                'title' => 'Beelden worden langer bewaard dan nodig',
                'description' => 'De automatische overschrijving faalt of wordt uitgezet, waardoor '
                    . 'beelden onbeperkt beschikbaar blijven.',
                'origin' => 'Technische storing of handmatige ingreep in de instellingen',
                'likelihood' => RiskLevel::LOW,
                'likelihood_motivation' => 'De overschrijving is standaard ingeschakeld; falen komt '
                    . 'voor maar is zeldzaam.',
                'impact' => RiskLevel::MEDIUM,
                'impact_motivation' => 'Een grotere hoeveelheid beelden blijft beschikbaar voor '
                    . 'raadpleging dan noodzakelijk.',
                'level' => RiskLevel::MEDIUM,
                'level_motivation' => 'Kleine kans, maar het effect houdt aan zolang het onopgemerkt '
                    . 'blijft.',
            ],
            'datalek' => [
                'title' => 'Datalek door toegang tot de opnameserver',
                'description' => 'Een aanvaller of onbevoegde krijgt toegang tot de server waarop de '
                    . 'beelden staan en kopieert beeldmateriaal.',
                'origin' => 'Kwetsbaarheid in de camera- of serversoftware',
                'likelihood' => RiskLevel::LOW,
                'likelihood_motivation' => 'De server staat in het eigen datacentrum en is niet vanaf '
                    . 'internet benaderbaar.',
                'impact' => RiskLevel::HIGH,
                'impact_motivation' => 'Uitstroom van herkenbare beelden van medewerkers en bezoekers '
                    . 'is niet terug te draaien.',
                'level' => RiskLevel::MEDIUM,
                'level_motivation' => 'De lage kans compenseert de hoge impact niet volledig.',
            ],
            'onwetendheid' => [
                'title' => 'Betrokkenen weten niet dat er cameratoezicht is',
                'description' => 'Bezoekers en nieuwe medewerkers worden niet geïnformeerd over het '
                    . 'cameratoezicht en kunnen hun rechten daardoor niet uitoefenen.',
                'origin' => 'Ontbrekende of onduidelijke informatievoorziening',
                'likelihood' => RiskLevel::MEDIUM,
                'likelihood_motivation' => 'Bezoekers lezen het intranet niet en bebording wordt in de '
                    . 'praktijk vaak over het hoofd gezien.',
                'impact' => RiskLevel::MEDIUM,
                'impact_motivation' => 'Betrokkenen kunnen geen inzage of bezwaar vragen omdat zij de '
                    . 'verwerking niet kennen.',
                'level' => RiskLevel::MEDIUM,
                'level_motivation' => 'Raakt de transparantieverplichting rechtstreeks.',
            ],
        ];

        foreach ($riskDefinitions as $key => $attributes) {
            $risk = new DpiaRisk();
            $risk->organisation_id = $record->organisation_id;
            $risk->dpia_record_id = $record->id;
            $risk->fill($attributes + ['order_column' => count($risks) + 1]);
            $risk->save();

            $risks[$key] = $risk;
        }

        $measureDefinitions = [
            [
                'description' => 'Toegang tot beelden is beperkt tot twee aangewezen medewerkers van '
                    . 'Facilitair Beheer, met persoonlijke accounts en tweefactorauthenticatie. Het '
                    . 'gedeelde beheeraccount is opgeheven en onderhoud op afstand is uitgeschakeld.',
                'type' => MeasureType::TECHNICAL,
                'origin' => 'Cameraprotocol, artikel 4',
                'residual_level' => RiskLevel::LOW,
                'owner' => 'Facilitair Beheer',
                'risks' => ['onbevoegde_inzage', 'datalek'],
            ],
            [
                'description' => 'Elke raadpleging van beelden wordt gelogd met naam, tijdstip en de '
                    . 'melding waarop zij berust. De security officer controleert de logs jaarlijks '
                    . 'en rapporteert de bevindingen aan de FG.',
                'type' => MeasureType::ORGANISATIONAL,
                'origin' => 'Advies FG, 17 maart 2026',
                'residual_level' => RiskLevel::LOW,
                'owner' => 'Security officer',
                'risks' => ['onbevoegde_inzage', 'functieverandering'],
            ],
            [
                'description' => 'Het cameraprotocol sluit gebruik van beelden voor beoordeling, '
                    . 'aanwezigheidscontrole of functioneren van medewerkers uitdrukkelijk uit. '
                    . 'Leidinggevenden krijgen geen autorisatie en verzoeken om inzage lopen '
                    . 'uitsluitend via de privacy officer.',
                'type' => MeasureType::ORGANISATIONAL,
                'origin' => 'Instemming ondernemingsraad, 3 maart 2026',
                'residual_level' => RiskLevel::MEDIUM,
                'owner' => 'Directie Bedrijfsvoering',
                'risks' => ['functieverandering'],
            ],
            [
                'description' => 'De bewaartermijn van 28 dagen is technisch afgedwongen in de '
                    . 'opnamesoftware. Facilitair Beheer controleert jaarlijks of de automatische '
                    . 'overschrijving werkt en legt die controle vast.',
                'type' => MeasureType::TECHNICAL,
                'origin' => 'Cameraprotocol, artikel 6',
                'residual_level' => RiskLevel::LOW,
                'owner' => 'Facilitair Beheer',
                'risks' => ['te_lang_bewaren'],
            ],
            [
                'description' => 'De opnameserver staat in het eigen datacentrum, is niet vanaf '
                    . 'internet benaderbaar en valt onder het reguliere patchbeheer. De camera\'s '
                    . 'staan in een apart netwerksegment.',
                'type' => MeasureType::TECHNICAL,
                'origin' => 'Baseline informatiebeveiliging',
                'residual_level' => RiskLevel::LOW,
                'owner' => 'Directie Informatievoorziening',
                'risks' => ['datalek'],
            ],
            [
                'description' => 'Bij beide ingangen van de garage staan borden met de vermelding van '
                    . 'cameratoezicht, het doel en de contactgegevens. Op het intranet en in de '
                    . 'introductie voor nieuwe medewerkers is dezelfde informatie opgenomen.',
                'type' => MeasureType::ORGANISATIONAL,
                'origin' => 'Artikel 13 AVG',
                'residual_level' => RiskLevel::LOW,
                'owner' => 'Directie Communicatie',
                'risks' => ['onwetendheid'],
            ],
            [
                'description' => 'Met de installateur is een verwerkersovereenkomst gesloten waarin '
                    . 'toegang alleen onder begeleiding en met vastgelegde reden is toegestaan.',
                'type' => MeasureType::LEGAL,
                'origin' => 'Verwerkersovereenkomst, 12 februari 2026',
                'residual_level' => RiskLevel::LOW,
                'owner' => 'Directie Juridische Zaken',
                'risks' => ['onbevoegde_inzage'],
            ],
        ];

        foreach ($measureDefinitions as $order => $attributes) {
            $linked = $attributes['risks'];
            unset($attributes['risks']);

            $measure = new DpiaMeasure();
            $measure->organisation_id = $record->organisation_id;
            $measure->dpia_record_id = $record->id;
            $measure->fill($attributes + ['order_column' => $order + 1]);
            $measure->save();

            $measure->risks()->sync(
                array_map(static fn (string $key): string => (string) $risks[$key]->id, $linked),
            );
        }
    }
}
