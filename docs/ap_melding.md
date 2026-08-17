# Melding datalek bij de Autoriteit Persoonsgegevens

## Waarom geen automatische melding

De AP accepteert datalekmeldingen **uitsluitend** via het online meldformulier op
<https://datalekken.autoriteitpersoonsgegevens.nl>. Er is geen API, geen bestandsimport en geen
e-mail- of postkanaal. De AP schrijft dat zelf in de
[vragenlijst meldformulier datalekken](https://autoriteitpersoonsgegevens.nl/uploads/imported/vragenlijst_meldformulier_datalekken_augustus_2022.docx):

> Let op: u kunt dit document niet gebruiken om een datalekmelding per post of e-mail aan de AP te
> sturen. De AP accepteert alleen datalekmeldingen via het online meldformulier datalekken.

Het [NCSC](https://www.ncsc.nl/wet-en-regelgeving/melden-van-een-datalek) noemt naast het formulier
alleen een telefoonnummer als terugvaloptie.

Verder onderzocht en afgevallen:

- **Bulkmelding.** Het formulier kent een bulkmelding (vraag 1.1), maar die is beperkt tot een
  pilot voor pensioenfondsen, verzekeraars en banken, vereist uitdrukkelijke schriftelijke
  toestemming van de AP, en die toestemming is volgens de vragenlijst op dit moment niet te
  verkrijgen. Bovendien gaat het specifiek om grootschalige postverzending.
- **Het formulier scripten.** Het meldformulier is een Berkeley Bridge-beslisboom (model
  `datalekkenmeldformulier`) achter een F5-gateway, zonder login. Een melding is een wettelijke
  handeling met een termijn van 72 uur; een script dat stilzwijgend breekt of verkeerd invult is
  een te groot risico, en er is geen enkele toezegging waarop we kunnen bouwen.
- **`.cas`-sessiebestand.** Het formulier kan een sessie opslaan en terugladen als `.cas`-bestand.
  Als dat formaat te schrijven zou zijn, zou het formulier vooringevuld kunnen worden. Het formaat
  is echter ongedocumenteerd, ongeversioneerd en leveranciergebonden. De moeite waard om een keer
  bij de AP of de leverancier na te vragen, maar niets om nu op te bouwen.

## Wat OpenVWR wel doet

Per datalek is er een **AP-meldformulier voorbereiding** (knop op het datalek, of
`/datalekken/{record}/ap-melding`). Die volgt de hoofdstukindeling en de vraagnummers van het
online formulier, zodat het formulier van boven naar beneden overgenomen kan worden. De pagina is
ook als PDF te downloaden.

Elk antwoord heeft een herkomst (`App\Services\ApReport\AnswerSource`):

| Herkomst | Betekenis |
|----------|-----------|
| `RECORDED` | Staat in het datalekregister. |
| `DERIVED`  | Afgeleid uit gekoppelde inhoud; moet gecontroleerd worden. |
| `MISSING`  | Staat nergens in het register; de privacy officer vult dit zelf in. |

Bovenaan staan twee lijsten: wat nog verzameld moet worden, en welke afgeleide antwoorden
gecontroleerd moeten worden.

### Waarom afgeleide antwoorden niet als feit gelden

Een gekoppelde verwerking beschrijft wat díe verwerking kan bevatten, niet wat er bij dít datalek
werkelijk gelekt is. Als een datalek alleen de adresgegevens uit een zorgverwerking raakt, zou het
klakkeloos overnemen van "gegevens over iemands gezondheid" een onjuiste melding aan de
toezichthouder opleveren. Afgeleide antwoorden worden daarom altijd getoond mét de bron waaruit ze
komen, ter bevestiging door de privacy officer. Staat er een waarde op het datalek zelf, dan wint
die van de afleiding.

### Wat wordt afgeleid

| Vraag | Herkomst |
|-------|----------|
| 1.2 Wettelijke grondslag | Het register waarin de gekoppelde verwerking staat (WPG-koppeling ⇒ Wpg) |
| 3.1 Verantwoordelijke en adres | `responsibles` en hun `address` |
| 3.3 Andere organisaties | `processors` en `receivers` van de gekoppelde verwerkingen |
| 6.1 BSN / strafrechtelijke gegevens | `stakeholders.citizen_service_numbers` en `criminal_law` |
| 6.2 Bijzondere categorieën | De Art. 9-booleans op `stakeholders` |
| 7.2 Betrokkenen | `stakeholders.description` |
| 8.1 Maatregelen vooraf | `pseudonymization` van de verwerking (als context, niet als antwoord) |

WPG-verwerkingen kennen geen betrokkenen (`stakeholders`) en leveren daar dus niets aan.

## Bekende gaten

Deze vragen kan het register nog niet beantwoorden en verschijnen als "nog invullen": aantal
gegevensrecords (6.3), aantal betrokkenen (7.3), hoe het lek ontdekt is (4.3), reden van melding na
72 uur (4.5), gevolgen (9.1/9.2), ernstinschatting in de vier niveaus van de AP (9.3), KvK-nummer,
sector en FG-registratienummer (3.1/3.2), en de vervolgacties (10.x).
