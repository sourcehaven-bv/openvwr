# DPIA in Nederland (AVG-context) — referentie

Onderzoeksnotitie, 28 juli 2026. Bronnen zijn primair: het officiële Rijksmodel (PDF, tekst geëxtraheerd) en de broncode van `MinBZK/par-dpia-form` (lokaal gekloond en geïnspecteerd). Waar iets niet geverifieerd is, staat dat er expliciet bij.

---

## 1. De normatieve basis

| Laag | Wat | Rol |
|---|---|---|
| AVG art. 35 | DPIA-verplichting + minimale inhoud (lid 7) | Wettelijke ondergrens |
| Richtlijn (EU) 2016/680 art. 27 | Idem voor opsporing/vervolging | Parallel regime |
| EDPB WP248 (4 apr 2017) | 9 criteria voor "hoog risico" | Drempeltoets |
| AP-lijst (Stcrt. 27 nov 2019) | 17 verwerkingssoorten met verplichte DPIA | Nationale aanscherping |
| **Model DPIA Rijksdienst v3.0** (sept 2023) | 17 punten in 4 onderdelen | **De facto NL-standaard** |

Art. 35 lid 7 AVG schrijft vier verplichte elementen voor: (a) systematische beschrijving van de verwerkingen en doeleinden, (b) beoordeling van noodzaak en evenredigheid, (c) beoordeling van de risico's voor betrokkenen, (d) de beoogde maatregelen. **De A/B/C/D-indeling van het Rijksmodel is rechtstreeks hierop gebaseerd** — dat is expliciet zo verantwoord in het model (voetnoot 25). Elk instrument dat je bouwt moet die vier blokken herkenbaar houden; dat is de audit-trail richting de AP.

Boete bij ontbrekende of gebrekkig uitgevoerde DPIA: tot €10 mln (art. 83 lid 4a AVG).

---

## 2. Het Rijksmodel: 17 punten, 4 onderdelen

Uit `Model DPIA Rijksdienst v3.0`, Deel II. Dit is de kern van je datamodel.

### A. Beschrijving algemene kenmerken gegevensverwerkingen (de feiten)

| # | Punt | Wat er ingevuld moet worden |
|---|---|---|
| 1 | **Voorstel** | Hoofdlijnen + totstandkoming + beweegredenen |
| 2 | **Persoonsgegevens** | Alle gegevens, geclassificeerd als: *gewoon / gevoelig / bijzonder / strafrechtelijk / wettelijk identificatienummer*, met bron per categorie |
| 3 | **Gegevensverwerkingen** | Alle verwerkingen × welke categorieën daarin; optioneel stroomschema |
| 4 | **Technieken en methoden** | Middelen; expliciet benoemen: (semi-)geautomatiseerde besluitvorming, profilering, cloud, big data |
| 5 | **Verwerkingsdoeleinden** | Doel per verwerking |
| 6 | **Betrokken partijen** | Rollen: verwerkingsverantwoordelijke, gezamenlijk vwv, verwerker, sub-verwerker, verstrekker, ontvanger, betrokkene, derde. Plus welke functionarissen toegang krijgen tot welke categorieën |
| 7 | **Belangen** | Belangen van álle partijen; mening van betrokkenen indien relevant |
| 8 | **Verwerkingslocaties** | Landen; bij buiten-EER het doorgiftemechanisme + aanvullende maatregelen |
| 9 | **Juridisch en beleidsmatig kader** | Alle relevante wet-/regelgeving en beleid (AVG en Richtlijn hoeven níét genoemd) |
| 10 | **Bewaartermijnen** | Termijn + motivering "niet langer dan strikt noodzakelijk" + wie erop toeziet + vernietiging/archivering |

### B. Beoordeling rechtmatigheid (het juridische oordeel)

| # | Punt | Beslissing die genomen moet worden |
|---|---|---|
| 11 | **Rechtsgrond** | Per verwerking; incl. hoe aan de voorwaarden van díé grond wordt voldaan |
| 12 | **Bijzondere persoonsgegevens** | Verwerking is in principe *verboden* — welke wettelijke uitzondering geldt? Idem toets op wettelijk identificatienummer |
| 13 | **Doelbinding** | Bij verdere verwerking: toelaatbaar op grond van Unie-/lidstaatrecht, óf verenigbaar met oorspronkelijk doel? |
| 14 | **Noodzaak en evenredigheid** | **a. Proportionaliteit** (staat inbreuk in verhouding tot doel?) **b. Subsidiariteit** (kan het minder nadelig?) |
| 15 | **Rechten van de betrokkenen** | Procedure per recht; bij beperking: op grond van welke wettelijke uitzondering |

### C. Risico's voor betrokkenen

| # | Punt | Verplichte elementen |
|---|---|---|
| 16 | **Risico's** | a. negatieve gevolgen voor rechten en vrijheden (bijv. discriminatieverbod) · b. oorsprong · c. kans · d. impact |

Risiconiveau = **kans × impact**, met de schaal **laag / gemiddeld / hoog**.

> Let op de reikwijdte: het gaat om *rechten en vrijheden*, niet alleen om privacy. Het model noemt het discriminatieverbod expliciet. Dit is breder dan een klassieke security-risicoanalyse.

### D. Maatregelen

| # | Punt | Verplichte elementen |
|---|---|---|
| 17 | **Maatregelen** | Technische, organisatorische én juridische maatregelen; **welke maatregel welk risico aanpakt**; plus de **resterende risico's** met niveau per stuk |

**Het model is expliciet iteratief.** Letterlijk: als bij C/D blijkt dat risico's te groot zijn, moeten de verwerkingen wijzigen — en dan moeten A en B óók worden bijgewerkt "om de realiteit te reflecteren". Een DPIA-tool die A→D als eenrichtingswizard modelleert, vecht tegen de methode.

Ook expliciet: detailniveau mag variëren naar aard en omvang, **maar alle 17 punten moeten altijd worden nagelopen en de afweging per punt moet worden opgeschreven**. Leeg laten is geen optie; "niet van toepassing, want…" wel.

---

## 3. Wanneer is een DPIA verplicht?

Vier ingangen (Deel I §1.2):
1. Ontwikkeling van beleid/regelgeving waaruit verwerkingen voortvloeien
2. Verplichting op basis van departementaal beleid
3. Publieke cloudvoorziening in specifieke omstandigheden (Rijksbreed cloudbeleid 2022)
4. Verwerkingen met waarschijnlijk hoog risico

**EDPB-telregel:** 2 criteria → waarschijnlijk verplicht. 1 criterium → beoordelen of sprake is van hoog risico. Besluit je géén DPIA te doen, dan moet die afweging schriftelijk worden vastgelegd. Bij grote politiek-bestuurlijke/maatschappelijke vraagstukken is een DPIA "te allen tijde gewenst".

**AP-criteria** (in het model opgesomd, ook verwerkt in de Pre-scan): heimelijk onderzoek · zwarte lijsten · fraudebestrijding · financiële situatie · samenwerkingsverbanden · cameratoezicht · controle werknemers · locatiegegevens · communicatiegegevens · profilering · observatie/beïnvloeding van gedrag · biometrische gegevens.

**Uitzonderingen** (art. 35 lid 5/10): rechtsgrond ligt in wettelijke plicht/algemeen belang én er is al een DPIA gedaan bij het vaststellen daarvan; of de AP heeft geoordeeld dat het niet hoeft. Maar het model waarschuwt: vaak is een DPIA op uitvoeringsniveau alsnog wenselijk (systeemkeuze, beveiligingsmaatregelen).

Eén DPIA mag een *reeks* vergelijkbare verwerkingen dekken (art. 35 lid 1, overweging 92) — relevant voor gedeelde applicaties tussen overheidsorganen.

---

## 4. Het proces (11 stappen) — dit is wat een tool moet ondersteunen

Uit Deel I §1.6. De inhoudelijke 17 punten zijn maar één stap hiervan; de rest is workflow.

1. **Pre-scan DPIA** als drempeltoets + bepaal wie verwerkingsverantwoordelijke is. Ook een negatieve uitkomst moet gedocumenteerd en gearchiveerd worden.
2. Bespreek **in groepsverband** met diverse expertises (beleid, recht, security, ICT); minimaal iemand met privacydeskundigheid.
3. Leg vast via het **Rapportagemodel**, met Deel III (toelichting) ernaast.
4. **Consulteer betrokkenen** / vertegenwoordigers (art. 35 lid 9). Bij eigen personeel: departementale of groepsondernemingsraad. Neem op wát geadviseerd is en wat ermee is gedaan. Geen consultatie → motiveer dat.
5. **FG om advies vragen is verplicht** (art. 35 lid 2). Vroeg betrekken, niet pas bij het eindconcept. Leg advies én opvolging vast.
6. Bij ICT-bouw: **CIO consulteren**.
7. **Voorafgaande raadpleging AP** (art. 36) als restrisico hoog blijft en niet tot acceptabel niveau te brengen is. Bij regelgeving: altijd ter consultatie naar de AP. Termijn: 8 weken, verlengbaar met 6.
8. Stuur het definitieve rapport naar alle betrokkenen (tenzij geheimhouding).
9. **Voeg toe aan het verwerkingsregister**; bij een (zelflerend) algoritme óók aan het **algoritmeregister**.
10. **Evalueer minimaal elke 3 jaar**.
11. Bij doorgifte aan een zelfstandige derde: licht toe wat die ermee doet; rechtsgrond vereist.

**Timing:** vroeg in de ontwikkeling (privacy by design, art. 25). Bij aanbesteding: vóór de aanbesteding, zodat de uitkomst in de offerteaanvraag landt. Bij regelgeving: vóór de internetconsultatie.

**Herziening verplicht** als: de verwerking wijzigt (andere gegevens, andere partijen, andere wijze), óf er 3 jaar geen herziening is geweest.

**Verantwoordelijkheid:** formeel de minister; in de praktijk gemandateerd (DG/directeur). Bij meerdere ministers: gezamenlijk, met de voortrekker als initiatiefnemer.

---

## 5. Open source & overheidssystemen

### 5.1 `MinBZK/par-dpia-form` — het directe precedent ⭐

Dit is verreweg de belangrijkste vondst: het ministerie van BZK (Privacy Adviseurs Rijk) heeft de invulhulp **open source** gepubliceerd.

- Repo: https://github.com/MinBZK/par-dpia-form · Live: https://invulhulpen.rijksapp.nl/
- **Licentie EUPL-1.2**, `publiccode.yaml` aanwezig, release 2026-06-14, actief onderhouden (laatste commit die ik zag: 2026-07-15)
- Ondersteunt **Pre-scan, volledige DPIA (Rijksmodel v3.0) én IAMA**
- Stack: Vue 3 + TS + Vite + Pinia; Fastify 5 + Drizzle + PostgreSQL; Keycloak OIDC; **NL Design System / RVO**; pdfmake. Inclusief een `standalone-form` die naar één offline HTML-bestand bouwt.

**De schema's zijn het herbruikbare goud** (JSON Schema draft 2020-12):
- `schemas/assessment-definition.v2.schema.json` — de vragenboom
- `schemas/assessment-output.v2.schema.json` — de antwoorden
- `sources/dpia.yaml` (67 KB), `prescan.yaml` (42 KB), `iama.yaml` (69 KB), `begrippenkader_dpia.yaml` (285 KB)

Een `task` is recursief: `{task, description, id, is_official_id, type[], repeatable, options[], valueType, dependencies, calculation, sources, tasks[]}`. Veldtypen: `text_input, open_text, select_option, multiselect_scrollable, checkbox_option, radio_option, task_group, informational, date, image`.

De 17 officiële paragrafen dragen `is_official_id: true`; daarnaast niet-officiële blokken 0 (Inleiding), 18 (Managementsamenvatting), 19 (Versie/Status/Dossier/Documentbeheerder/Advies FG), 20 (Vaststelling en ondertekening).

**Risico's en maatregelen — geverifieerd uit de bron.** §16.1 is een `repeatable` group (`item_name: risico`) met: `16.1.1` Beschrijving · `16.1.2` Oorsprong · `16.1.3` Kans · `16.1.4` Motivatie kans · `16.1.5` Impact · `16.1.6` Motivatie impact · `16.1.7` Risiconiveau · `16.1.8` Motivatie risicoinschatting. De drie select-velden hebben opties **`laag / midden / hoog`** — let op: het PDF-model schrijft "gemiddeld", de YAML gebruikt "midden".

De koppeling risico→maatregel is declaratief en elegant:

```yaml
- task: Risico
  id: "17.1.1"
  type: [checkbox_option]
  valueType: "string[]"
  dependencies:
    - type: source_options
      condition: { id: "16.1.1", operator: any }
      action: options
```

Oftewel: de maatregelentabel trekt zijn keuzelijst uit de ingevulde risicobeschrijvingen. Datzelfde `source_options`-patroon koppelt `3.1.3` aan de persoonsgegevenslijst uit `2.1.1`. Verder: `17.1.4` Resterend risico; staat die op `hoog`, dan verschijnen conditioneel `17.1.5` (advies AP) en `17.1.6` (land van monitoring) — precies de art. 36-escalatie. Plus `17.3` Onderbouwing acceptatie resterende risico's.

**Pre-scan als expressie-engine** (`urn:nl:prescan` v2.0). Vier uitkomsten — DPIA, DTIA, KIA, IAMA — elk met `required`/`recommended` niveaus. Geverifieerd:

```yaml
- id: "DPIA"
  levels:
    - level: "required"
      expression: "criteria.wetgeving || criteria.riskscore || criteria.aplist || criteria.edpblist2"
      result: "DPIA verplicht"
```
met `aplist` = `countSelectedOptions('3.1') >= 1`, `edpblist2` = `countSelectedOptions('4.1') >= 2`, en `riskscore` = een gewogen som over persoonsgegevens, betrokkenen, frequentie, bewaartermijn, internationale doorgifte etc. `> 4`. `recommended` bij precies 1 EDPB-item. Mooi detail: elk criterium draagt een `explanation`, zodat de uitkomst uitlegbaar is — dat is precies de schriftelijke motiveringsplicht uit §1.2.

Ook: https://github.com/MinBZK/dpia-pdf-poc (Python, EUPL-1.2, sinds 2025-04 stil).

### 5.2 Formeel informatiemodel — `modellen.jenvgegevens.nl/dpia/`

JenV CDO Office + CIO Rijk, v1.0.0, 1 juni 2025. Conceptueel (CIM) + logisch (LGM) informatiemodel bij Rijksmodel v3.0. ~45 objecttypen: `DPIA` (met subtypes algoritme/big data/cloud/geautomatiseerde besluitvorming/profilering), `Gegevensverwerking`, `Gegevenstype → Persoonsgegeven → Bijzonder persoonsgegeven`, `Betrokken partij` met rolvlaggen, `Risico voor betrokkenen → Inherent risico | Resterend risico`, `Bewaartermijn`, `Recht van de betrokkene`, `Beperking op recht`. 18 LGM-submodellen die de paragrafen spiegelen.

⚠️ **Niet machine-leesbaar**: `.ttl`/`.jsonld`/`.xmi`/`.json` geven 404; alleen ReSpec-Markdown en SVG's. Bronrepo niet gevonden (onzeker). Bruikbaar als entiteit/relatie-laag, niet als importformaat.

### 5.3 CNIL PIA — het volwassen precedent, andere methodiek

https://github.com/LINCnil/pia (+ `pia-back` Rails/PostgreSQL, `pia-app` Electron), GPL-3.0, actief. **Nederlandse vertaling is compleet** (1070 keys in `nl.json`).

Entiteiten: `Pia` (met status-enum doing/refused/simple_validation/signed_validation/archived, `dpo_status`, `concerned_people_opinion`, rollen author/evaluator/validator/guest), `Answer`, `Measure`, `Evaluation` (met `gauges:{x,y}`), `Comment`, `Attachment`, `Revision` (volledige JSON-snapshot — versiebeheer), `Structure` (vragenlijst forken), `Knowledge`.

**Belangrijk methodisch verschil:** CNIL werkt met **drie vaste risico-archetypen** (onrechtmatige toegang / ongewenste wijziging / verdwijnen van gegevens), elk met hetzelfde stramien van gevolgen, bedreigingen, risicobronnen, maatregelen + twee gauges. Het Rijksmodel werkt **vrije-vorm**: een herhaalbare lijst van zelf-geïdentificeerde risico's. Neem CNIL dus over als *UX- en datamodel-inspiratie* (met name `Revision`, de rollen en de validatie-workflow), **niet** als methodiek — die is Frans, niet AVG-NL.

### 5.4 Wat er níét is

- **Geen DPIA-register en geen systematische publicatieplicht.** DPIA's vallen niet onder de Woo-categorieën voor actieve openbaarmaking; ze komen via Woo-verzoeken naar buiten. `avgregisterrijksoverheid.nl` is een *verwerkings*register, geen DPIA-archief.
- **Geen officieel JSON/XML-schema voor het verwerkingsregister** — niet bij Forum Standaardisatie, NEN, AP of EDPB. De AP schrijft inhoud voor, geen vorm ("er is geen standaardmodel").
- `componentencatalogus.commonground.nl` is dood; `developer.overheid.nl` vindt `par-dpia-form` niet (zoek liever op https://code.overheid.nl).
- Zoeken op "gegevensbeschermingseffectbeoordeling" op GitHub: 0 repos.

### 5.5 Gepubliceerde voorbeeld-DPIA's

- **DPIA + FRAIA Microsoft 365 Copilot** (SLM Rijk / Privacy Company, 17-12-2024) — https://www.rijksoverheid.nl/documenten/2024/12/17/dpia-en-fraia-microsoft-365-copilot
- SURF-kopie (direct downloadbaar): https://www.surf.nl/files/2024-12/20241218-dpia-microsoft-365-copilot.pdf
- **CoronaMelder DPIA** (07-07-2020, 72 pp) — https://www.rijksoverheid.nl/documenten/rapporten/2020/07/07/gegevensbeschermingseffectbeoordeling-dpia-covid-19-notificatie-app
- SLM-themapagina (Google Cloud, AWS+DTIA, ESET, STACKIT, Teams/OneDrive/SharePoint, Azure AD, Dynamics): https://www.rijksoverheid.nl/themas/overheid-en-democratie/zakendoen-met-het-rijk/strategisch-leveranciersmanagement-microsoft-rijk
- **IBD DPIA-bibliotheek** — 25+ collectieve gemeentelijke DPIA's: https://www.informatiebeveiligingsdienst.nl/collectieve-dpias/

De Privacy Company-DPIA's volgen de A/B/C/D-indeling herkenbaar (Part A description → Part B lawfulness → Part C risks → Part D mitigating measures), met veel meer technische diepgang (telemetrie-onderzoek) in deel A.

---

## 6. Aanpalende instrumenten

| Instrument | Wanneer | Verhouding tot DPIA |
|---|---|---|
| **Pre-scan DPIA** | Altijd vooraf | Drempeltoets; uitkomst archiveren, ook bij "niet nodig" |
| **IAMA** | Algoritmes/AI | **Vervangt de DPIA niet** — v1 zegt letterlijk: "Een DPIA kan de toetsing aan het IAMA dan ook niet vervangen". IAMA is breder (alle grondrechten), DPIA smaller maar dieper (persoonsgegevens) |
| **DTIA** | Doorgifte buiten EER met SCC/overig mechanisme | Aparte assessment |
| **KIA** | Digitale dienst gericht op minderjarigen | Aanbevolen |

**IAMA v2 (16 feb 2026)** — https://www.rijksoverheid.nl/documenten/2026/02/16/impact-assessment-mensenrechten-en-algoritmes — herstructureerd t.o.v. v1 (2021): Deel 1 Waarom? · Deel 2 Wat? · Deel 3 Hoe? · Deel 4 Mensenrechten · 5 Afsluiting (~61 vragen). Deel 4 is een 7-staps grondrechtentoets. Expliciet uitgelijnd op **art. 27 AI-verordening (FRIA)**. Vraag 4.2.2 stuurt door naar de pre-scan DPIA. Engelse v1 = FRAIA. IAMA is *aanbevolen*, niet wettelijk verplicht (motie-Bouchallikh/Dekker-Abdulaziz is aangenomen maar geen wet).

**Te volgen:** de **EDPB heeft op 10 maart 2026 een geharmoniseerd DPIA-template v1.0 aangenomen** (consultatie liep tot 9 juni 2026), waarin inherente risico's worden onderscheiden van risico's uit toevallige/abnormale gebeurtenissen. Een documenttemplate, geen schema — maar het gaat de Europese veldstructuur beïnvloeden. Uitkomst van de consultatie heb ik niet geverifieerd.

**DPV (Data Privacy Vocabulary)** v2.3, 25 feb 2026 — https://w3c-cg.github.io/dpv/ — de enige serieuze machine-leesbare doeltaal (RDFS+SKOS, JSON-LD beschikbaar, `dpv:ROPA` en `dpv:ImpactAssessment` zijn first-class concepten, met EU-GDPR-module). Let op: Community Group Report, geen W3C Recommendation; de ROPA/DPIA-mappinggidsen zijn nog Work in Progress.

---

## 7. Consequenties voor een DPIA-builder

1. **Neem de 17 punten met hun officiële nummering als canonieke sleutels over.** Ze zijn de gedeelde taal met FG's, AP en auditors. `par-dpia-form` doet dit met `is_official_id: true` — die scheiding tussen officiële en eigen velden is het overnemen waard.
2. **Modelleer risico's en maatregelen als aparte, herhaalbare entiteiten met een expliciete relatie.** Punt 17 eist "beschrijf welke maatregel welk risico aanpakt", plus een *resterend* risiconiveau per stuk. Dus: `Risico{beschrijving, oorsprong, kans, impact, niveau, motivaties}` ←→ `Maatregel{type, beschrijving, beheerder, resterend_risico}`. Twee losse tekstvelden voldoen niet.
3. **Bouw geen strakke wizard.** A→B→C→D is iteratief; C/D slaan terug op A/B. Sla per punt de status op ("nagelopen, n.v.t. omdat…") in plaats van alleen ingevuld/leeg.
4. **Workflow is een first-class concern, geen bijzaak:** FG-advies (verplicht), consultatie betrokkenen + wat ermee gedaan is, CIO, eventueel AP-raadpleging bij hoog restrisico, OR-traject, vaststelling/ondertekening. CNIL's `Pia.status` + rollen + `Revision` is hier een bruikbaar patroon.
5. **Herzieningstermijn van 3 jaar hoort in het datamodel** (herinnering + versievergelijking). Dit project heeft al versievergelijking — dat sluit hier direct op aan.
6. **Escalatielogica bij restrisico "hoog"** → conditioneel veld voor AP-raadpleging (art. 36). Overneembaar één-op-één uit `17.1.4/17.1.5`.
7. **Uitlegbare pre-scan:** genereer niet alleen een verdict maar ook de reden per criterium — dat is precies de vastleggingsplicht bij een negatieve uitkomst.
8. **Let op de laag/midden/hoog-discrepantie** tussen het PDF-model ("gemiddeld") en de MinBZK-YAML ("midden") als je op hun output wilt aansluiten.

### Directe herbruikbaarheid

`MinBZK/par-dpia-form` is **EUPL-1.2** — compatibel met hergebruik, met de gebruikelijke copyleft-voorwaarden bij distributie van afgeleide werken (juridisch zelf verifiëren voor jullie licentiecontext). De `sources/*.yaml` + `schemas/*.json` geven je Rijksmodel v3.0, Pre-scan en IAMA als machine-leesbare vragenbomen mét officiële paragraafnummers, conditionele logica en cross-assessment pre-fill. Dat is maanden werk dat al gedaan is.

Lokale klonen voor inspectie:
- `<scratchpad>/par-dpia` (MinBZK)
- `<scratchpad>/pia` (CNIL)
- `<scratchpad>/model-dpia-v3.txt` (volledige tekst van het Rijksmodel)

---

---

## 8. Wat er in OpenVWR is gebouwd

Twee registers onder een eigen navigatiegroep **DPIA**, los van de verwerkingsregisters maar eraan gekoppeld.

| Onderdeel | Waar |
|---|---|
| Pre-scan DPIA | `DpiaPrescanRecord` + `DpiaPrescanRecordResource` |
| DPIA (17 paragrafen) | `DpiaRecord` + `DpiaRecordResource` |
| Persoonsgegevens (§2) | `DpiaPersonalData`, `DpiaPersonalDataRepeater` |
| Aandachtspunten | `DpiaQualityChecker`, `DpiaSectionNotice`, `DpiaQualityNotification` |
| Risico's (§16) | `DpiaRisk`, `DpiaRisksRepeater` |
| Maatregelen (§17) | `DpiaMeasure`, `DpiaMeasuresRepeater`, pivot `dpia_measure_risk` |
| Uitkomstlogica | `PrescanEvaluator`, `PrescanAssessment`, `PrescanCriteria` |
| Risicomatrix | `RiskLevel::suggest()` |
| Koppeling naar DPIA | `dpia_record_relatables` (polymorf) + trait `HasDpiaRecords` |

### Ontwerpbeslissingen die niet vanzelf spreken

1. **Eigen register, geen veld op de verwerking.** Een DPIA is een document met 17 paragrafen, een eigen workflow en versiegeschiedenis; dat past niet in een verwerkingsrecord. De koppeling is polymorf en many-to-many, omdat één DPIA een reeks vergelijkbare verwerkingen mag bestrijken (art. 35 lid 1 AVG, overweging 92).
2. **Pre-scan is een eigen register, geen wizard-stap.** Het Rijksmodel eist dat óók een negatieve uitkomst wordt vastgelegd en gearchiveerd. Een pre-scan zonder vervolg blijft daarom als record bestaan.
3. **De uitkomst wordt opgeslagen én uitgelegd.** `PrescanAssessment` levert per assessment een zin met de reden. Dat is de schriftelijke onderbouwing die §1.2 vereist. De uitkomst wordt bij opslaan bevroren, zodat een later gewijzigde criterialijst het archief niet met terugwerkende kracht verandert.
4. **De risicomatrix adviseert, hij beslist niet.** Het model noemt de matrix illustratief. `RiskLevel::suggest()` toont een suggestie en waarschuwt bij afwijking, maar overschrijft de keuze van de invuller nooit — die moet de afwijking wel motiveren.
5. **Artikel 36 is afgeleid, niet onthouden.** Zodra een maatregel een hoog restrisico laat, verschijnen de AP-velden en een waarschuwing, en geeft `requiresApConsultation()` true. De invuller hoeft dat niet zelf te bedenken.
6. **Maatregel→risico via een expliciete pivot.** §17 eist "beschrijf welke maatregel welk risico aanpakt". De koppeling wordt na het opslaan gelegd door `DpiaMeasureRiskLinker`, omdat een risico dat in dezelfde sessie is toegevoegd pas ná het opslaan een id heeft. Anders zou je halverwege §16 moeten opslaan voordat je in §17 naar dat risico kunt verwijzen.
7. **Een DPIA is niet publiceerbaar naar de statische website.** DPIA's vallen niet onder de Woo-categorieën voor actieve openbaarmaking en bevatten routinematig beveiligingsmaatregelen en restrisico's. Publicatie blijft een aparte, bewuste keuze.
8. **Paragraaf 2 is een gestructureerde lijst, geen tekstblok.** Het model vraagt de persoonsgegevens te classificeren naar gewoon, gevoelig, bijzonder, strafrechtelijk en wettelijk identificatienummer. Als die classificatie een veld is in plaats van proza, kan de rest van de DPIA erop steunen.
9. **Paragraaf 12 vraagt niets opnieuw.** Welke gegevens een uitzonderingsgrond nodig hebben volgt uit de classificatie in paragraaf 2, en de grond wordt vastgelegd naast het gegeven dat hij rechtvaardigt. Paragraaf 12 rapporteert alleen wat er nog ontbreekt. Dezelfde vraag twee keer stellen is precies hoe een DPIA zichzelf gaat tegenspreken.
10. **Een DPIA wordt vastgesteld, net als een verwerking.** De DPIA hangt aan hetzelfde snapshot-mechanisme als de registers: een versie gaat van *ter review* naar *goedgekeurd* naar *vastgesteld*, met een audit trail per overgang. Zo kan een FG of (C)PIO een vastgestelde versie aanwijzen en de volgende ernaast leggen. De snapshot legt de hele DPIA vast, inclusief persoonsgegevens, risico's en maatregelen.
11. **Vaststellen bouwt de statische website niet.** De snapshot-observer bouwde tot nu toe bij elke vastgestelde versie. Een DPIA verschijnt daar nooit, dus die bouw is nu beperkt tot bronnen die publiceerbaar zijn.
12. **Een risico heeft een naam en een beschrijving.** De naam is wat paragraaf 17 in de aanvinklijst toont en wat boven een ingeklapt risico staat; de beschrijving houdt de redenering. Een beschrijving van meerdere zinnen maakt een onleesbaar label.
13. **Aandachtspunten adviseren, ze blokkeren nooit.** Opslaan lukt altijd. Een DPIA die half af is, is normaal — dat is geen fout maar werk in uitvoering. De controles kijken daarom niet naar lege velden, maar naar antwoorden die elkaar tegenspreken of een AVG-verplichting open laten: een risico zonder maatregel, een hoog restrisico zonder AP-raadpleging, bijzondere gegevens zonder grond, doorgifte zonder mechanisme, een afwijking van de matrix zonder motivering. Blokkeren zou alleen maar aanzetten tot placeholderteksten.

### Nog niet gebouwd

- IAMA als eigen register (de pre-scan signaleert het wel).
- Import/export van PDF of JSON (de invulhulp van BZK kan dat wel).
- Publicatie naar de statische website, bewust — zie punt 7.
- FG-advies en vaststelling lopen nu via velden plus het bestaande snapshot-mechanisme; een aparte goedkeuringsstroom met verplichte FG-handtekening is nog niet ingericht.

---

## Bronnen

- [Model DPIA Rijksdienst v3.0 (PDF)](https://www.kcbr.nl/sites/default/files/2023-09/Model%20DPIA%20Rijksdienst%20v3.0.pdf)
- [Rapportagemodel DPIA Rijksdienst v3.0 (DOCX)](https://www.kcbr.nl/sites/default/files/2023-08/Rapportagemodel%20DPIA%20Rijksdienst%20v3.0.docx)
- [Handreiking Pre-scan DPIA v2.0](https://www.cip-overheid.nl/media/o3ta5xd1/20240823-handreiking-pre-scan-dpia-v20.pdf)
- [KCBR — Data Protection Impact Assessment](https://www.kcbr.nl/ontwikkelen-beleid-en-regelgeving/beleidskompas/verplichte-kwaliteitseisen/data-protection-impact-assessment)
- [AP — Lijst verplichte DPIA](https://www.autoriteitpersoonsgegevens.nl/documenten/lijst-verplichte-dpia) · [Besluit BWBR0042812](https://wetten.overheid.nl/BWBR0042812)
- [MinBZK/par-dpia-form](https://github.com/MinBZK/par-dpia-form) · [invulhulpen.rijksapp.nl](https://invulhulpen.rijksapp.nl/)
- [Informatiemodel DPIA (JenV)](https://modellen.jenvgegevens.nl/dpia/)
- [LINCnil/pia (CNIL)](https://github.com/LINCnil/pia)
- [IAMA v2 (16 feb 2026)](https://www.rijksoverheid.nl/documenten/2026/02/16/impact-assessment-mensenrechten-en-algoritmes)
- [EDPB harmonised DPIA template (10 mrt 2026)](https://www.edpb.europa.eu/news/news/2026/enhancing-compliance-and-consistency-edpb-adopts-dpia-template_en)
- [Data Privacy Vocabulary v2.3](https://w3c-cg.github.io/dpv/)
- [DPIA + FRAIA Microsoft 365 Copilot](https://www.rijksoverheid.nl/documenten/2024/12/17/dpia-en-fraia-microsoft-365-copilot)
- [IBD collectieve DPIA's](https://www.informatiebeveiligingsdienst.nl/collectieve-dpias/)
