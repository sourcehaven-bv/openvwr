# Stijlgids voor de handleiding

Deze gids beschrijft hoe de teksten in de ingebouwde handleiding zijn geschreven. De handleiding
staat niet in markdown-bestanden, maar in PHP: de takenlaag in
[`src/cms/app/Manual/Content/TaskContent.php`](../src/cms/app/Manual/Content/TaskContent.php) en de
naslaglaag in [`src/cms/app/Manual/Content/Chapters/`](../src/cms/app/Manual/Content/Chapters/).
Wie daar tekst toevoegt of wijzigt, volgt de regels hieronder.

De gids codificeert wat de handleiding al doet. Hij is bedoeld om nieuwe tekst in dezelfde toon te
houden, niet om een herschrijving af te dwingen. Waar de bestaande tekst afwijkt, staat dat
genoemd bij de regel.

## 1. De twee lagen

De handleiding heeft twee lagen met een verschillende schrijfstijl. Dat onderscheid is de
belangrijkste regel in deze gids.

| | Takenlaag (`Task`, `Step`) | Naslaglaag (`Topic`) |
| --- | --- | --- |
| Vraag | "Wat wilt u doen?" | "Hoe zit dit in elkaar?" |
| Vorm | gebiedende wijs, één of twee zinnen per stap | beschrijvend, alinea's |
| Lengte | een stap past op twee regels | zo lang als de uitleg vraagt |
| Uitleg | staat er niet; er wordt naar gelinkt | staat hier, precies één keer |

Een stap die uitdijt tot een alinea hoort niet in de takenlaag. Verplaats de uitleg naar een
Topic en laat de stap ernaar linken via `topicIds`.

**Goed** (`TaskContent::verwerkingVastleggen`):

```
title: 'Vul de bewaartermijnen in',
body: 'Kies per gegeven een bewaartermijn uit de lijst, of beschrijf de termijn zelf als geen
       van de opties past.',
topicIds: ['bewaartermijnen'],
```

**Fout** — dezelfde stap met de uitleg erin:

```
body: 'Kies per gegeven een bewaartermijn. OpenVWR slaat de gekozen bewaartermijn op als tekst
       bij het gegeven zelf en niet als verwijzing naar de lijst, omdat een bewaartermijn een
       verantwoording is over de verwerking zoals die op dat moment gold. Past geen van de
       termijnen, kies dan ...'
```

Die uitleg staat al in het Topic `bewaartermijnen`. Herhaal hem niet.

## 2. Aanspreekvorm en toon

- Spreek de lezer aan met **u**. De hele handleiding doet dat; wissel nooit binnen een tekst.
- Schrijf over het product in de derde persoon: "het portaal stuurt een e-mail", "OpenVWR slaat
  de termijn op". Niet "wij sturen u een e-mail".
- Toon is zakelijk en behulpzaam, niet joviaal en niet plechtig. Geen uitroeptekens, geen
  "handig, toch?", geen "helaas".
- Ga niet in de plaats van de lezer staan: schrijf niet "we vullen nu het veld in".
- De lezer is een professional. Leg de applicatie uit, niet het vak. "Een DPIA" hoeft niet te
  worden uitgelegd; "de opzoeklijst *Bewaartermijnen*" wel.

**Goed**: `Voert u een onjuiste code in, dan meldt de applicatie dat en kunt u het opnieuw
proberen.`

**Fout**: `Als je een verkeerde code invoert krijg je helaas een foutmelding!`

> Afwijking in de bestaande tekst: het Topic `over-openvwr` bevat de technische regel
> `codebase: PHP, Laravel, Filament`. Dat is informatie voor beheerders, niet voor de gebruiker
> die de handleiding leest.

## 3. Taalniveau en zinslengte

De handleiding richt zich op professionals, maar volgt wel de uitgangspunten van
begrijpelijk Nederlands zoals de Rijksoverheid die hanteert.

- Houd zinnen op **15 tot 20 woorden**. Wissel de lengte af; niet elke zin even kort.
- Schrijf **actief**. Vermijd de lijdende vorm waar de handelende partij bekend is.
- Vermijd **naamwoordstijl**: gebruik het werkwoord.
- Vermijd **tangconstructies**: houd woorden die bij elkaar horen bij elkaar.
- Eén onderwerp per alinea; maximaal vier à vijf zinnen.

**Goed**: `Een Privacy Officer beoordeelt de versie en keurt hem goed.`

**Fout** (lijdende vorm, handelende partij verstopt): `De versie dient beoordeeld en goedgekeurd
te worden.`

**Goed**: `Klik op "Inschakelen".`

**Fout** (naamwoordstijl): `Het inschakelen dient plaats te vinden via de knop "Inschakelen".`

**Goed**: `De gemeente neemt een besluit na overleg met de Privacy Officer.`

**Fout** (tangconstructie): `De gemeente neemt, na overleg met de Privacy Officer over de
procedure, een besluit.`

> Afwijking in de bestaande tekst: de oudere Topics (`versiestatussen`, `goedkeuren`,
> `opzoeklijsten`, `gebruikers`) bevatten opvallend veel lijdende vorm — "kunnen toegevoegd
> worden", "is te vinden", "kan aangepast worden". De nieuwere Topics (`waarom-labels`,
> `bewaartermijnen`, `notificaties`) zijn actiever geschreven. Nieuwe tekst volgt de nieuwe stijl.

## 4. Stappen schrijven

Een `Step` heeft een `title` en een `body`. Beide volgen vaste regels.

**De titel** is een gebiedende wijs, zonder punt, en beschrijft de handeling:

- Goed: `Dien de conceptversie in`, `Ken de rollen toe`, `Filter de tabel`
- Fout: `Het indienen van de conceptversie`, `Stap 3: indienen`, `U dient de versie in.`

Houd de titels binnen één taak **parallel**: allemaal gebiedende wijs, allemaal even concreet.

**De body** bestaat uit één of twee volledige zinnen met een punt aan het eind. De body zegt
eerst *waar* de handeling plaatsvindt en dan *wat* de lezer doet.

**Goed**: `Kies in het navigatiemenu het register en klik op "Verwerking aanmaken". U komt op de
detailpagina.`

**Fout** (plaats komt na de handeling): `Klik op "Verwerking aanmaken", die u vindt nadat u in het
navigatiemenu het register heeft gekozen.`

Een voorwaarde staat vooraan, niet achteraan:

**Goed**: `Werkt uw organisatie met Mandaathouders, voeg ze dan toe onder "Ondertekeningen".`

**Fout**: `Voeg Mandaathouders toe onder "Ondertekeningen", als uw organisatie daarmee werkt.`

Elke taak sluit af met een `done`: één zin die de eindtoestand beschrijft, in de voltooide tijd.

**Goed**: `De versie is vastgesteld en geldt als de geldende versie van de verwerking.`

**Fout**: `Klaar!` of `U heeft nu succesvol een versie vastgesteld.`

In de naslaglaag zijn genummerde lijsten toegestaan, bijvoorbeeld in het Topic
`authenticator-instellen`. Ook daar geldt: één handeling per punt, gebiedende wijs, punt aan het
eind.

## 5. UI-elementen aanhalen

De handleiding kent drie vormen. Gebruik ze consequent.

| Vorm | Waarvoor | Voorbeeld |
| --- | --- | --- |
| `"Dubbele aanhalingstekens"` | knoppen, velden, menu-items, tabbladen, paginanamen | `Klik op "Inschakelen".` |
| `*Cursief*` | kolomnamen in een tabel en namen van opzoeklijsten | `de opzoeklijst *Bewaartermijnen*` |
| `` `code` `` | bestandsextensies en technische waarden | ``een `.csv` of `.xlsx` bestand`` |

Regels daarbij:

- Neem het label **letterlijk** over uit de applicatie, inclusief hoofdletters. Het is
  `"Start vaststellen"`, niet `"start vaststellen"` en niet `"Vaststellen starten"`.
- Zet alleen het label tussen aanhalingstekens, niet de hele zin, en laat de punt erbuiten:
  `Klik op "Goedkeuren".`
- Beschrijf de plaats zoals de lezer hem ziet: `rechtsbovenin`, `boven de tabel`,
  `in het navigatiemenu links`.
- Een pad door menu's schrijft u met `>` en spaties eromheen: `"Beheer" > "Labels"`,
  `"Profiel" > "Instellingen"`.
- Verzin geen labels. Klopt het label in de applicatie niet, wijzig dan de applicatie of meld het;
  schrijf het niet mooier op in de handleiding.

**Goed**: `Klik rechtsbovenin op "Start vaststellen".`

**Fout**: `Klik op de knop rechtsboven om het vaststellen te starten.` (label ontbreekt, lezer moet
gokken)

**Fout**: `Klik op de "Start vaststellen knop."` (aanhalingsteken om het verkeerde stuk, punt binnen)

Een status uit het goedkeuringsproces schrijft u als code-span in de vorm `status:kind:label`; de
renderer maakt daar een gekleurde markering van. Zie
[`ManualMarkdown.php`](../src/cms/app/Manual/ManualMarkdown.php). Buiten de lijst met statussen in
`versiestatussen` verwijst u naar een status gewoon tussen aanhalingstekens: `de status "Concept"`.

## 6. Links en het één-plek-principe

Een uitleg staat op precies één plek. Alle andere plekken linken ernaar. Dit is het principe dat de
handleiding onderhoudbaar houdt, en het staat ook in de PHPDoc van `Topic` en `Step`.

- Een `Step` linkt via `topicIds`, niet met een link in de tekst.
- Een `Topic` linkt naar een ander Topic met een markdown-link naar het anker:
  `[Bewaartermijnen](#bewaartermijnen)`.
- De linktekst is de **titel van het doel**, of een omschrijving waaruit het doel blijkt. Nooit
  "hier", "deze pagina" of een kale URL. Dit is een eis uit WCAG 2.4.4 en geldt voor dit product
  als overheidsproduct.

**Goed**: `Voor meer informatie over exporteren: zie [Export](#export).`

**Goed**: `Zie [Bewaartermijnen](#bewaartermijnen) voor de reden.`

**Fout**: `Klik [hier](#export) voor meer informatie.`

**Fout**: `Meer informatie: https://openvwr.nl/handleiding#export`

Merkt u tijdens het schrijven dat u iets uitlegt wat elders al staat: haal het weg en link. Merkt u
dat er nog geen plek voor is: maak het Topic aan, en laat de andere plekken ernaar linken.

## 7. Hint versus Let op

De renderer kent twee callouts. Ze zijn niet uitwisselbaar.

**`> **Hint**:`** — iets wat het werk makkelijker maakt, maar dat de lezer mag overslaan. Een
handigheidje, een alternatieve route, een tip voor de inrichting.

```
> **Hint**: Dit overzicht kan gebruikt worden als To Do lijst. Filter op alle versies die de
> status "In review" hebben.
```

**`> **Let op**:`** — iets waarvan de lezer schade ondervindt als hij het niet weet. Gegevens
die verdwijnen, een handeling die onomkeerbaar is, een verwachting die niet klopt.

```
> **Let op**: Het verwijderen van een label verwijdert het overal: de onderdelen zelf blijven
> bestaan, maar het label is er overal af.
```

Regels:

- Gebruik `Let op` alleen bij een echt risico. Wordt elke tweede alinea een waarschuwing, dan
  leest niemand ze meer.
- Een callout is nooit de enige plek waar iets staat. De hoofdtekst moet zonder de callouts te
  begrijpen zijn.
- Houd een callout kort: drie tot vier zinnen. Wordt hij langer, dan hoort de inhoud in de
  lopende tekst.
- Andere labels bestaan niet. `> **Tip**:` of `> **Waarschuwing**:` rendert als een gewoon citaat.

> Afwijking in de bestaande tekst: de Hint in het Topic `verwerkingsregisters` is negen regels lang
> en legt het hele goedkeuringsproces uit. Dat hoort in de lopende tekst of in het Topic
> `versie-indienen`, waar het al staat. Het Topic `opzoeklijsten` gebruikt bovendien een
> uitroepteken in een `Let op`; dat past niet bij de toon.

## 8. Rollen benoemen

- Schrijf rolnamen met **hoofdletter**: Privacy Officer, Chief Privacy Officer, Invoerder,
  Invoerder Datalekken, Mandaathouder, Raadpleger, Functionaris Gegevensbescherming, Functioneel
  beheerder. Ze zijn de naam van een functie in het portaal, geen gewoon zelfstandig naamwoord.
- De verkorte vorm voor beide Officer-rollen is `(Chief) Privacy Officer`. Gebruik die waar een
  regel voor allebei geldt.
- Gebruik het onbepaald lidwoord waar het om een willekeurige houder van de rol gaat:
  `Een Privacy Officer beoordeelt de versie`. Gebruik `uw Chief Privacy Officer` waar de lezer een
  concrete persoon moet benaderen.
- Vermeld een rol in de tekst alleen als hij het gedrag verklaart. Wie mag wat, staat in de velden
  `roles` en `availability` van het Topic en in het Topic `rechten-per-onderdeel`; herhaal die tabel
  niet in proza.
- Vermijd "hij of zij" waar het kan: schrijf over de rol, niet over de persoon.

**Goed**: `Een Invoerder kan bestaande labels wel toekennen en weghalen, maar geen nieuwe labels
aanmaken: die knop is voor deze rol niet zichtbaar.`

**Fout**: `De gebruiker met de rol invoerder kan geen labels aanmaken.` (kleine letter, omslachtig)

> Afwijking in de bestaande tekst: het Topic `rollen` schrijft `akkoord of niet akkoord geven op
> versies waarvoor hij of zij is uitgenodigd`. Beter: `waarvoor de Mandaathouder is uitgenodigd`.

## 9. Optionele functionaliteit

Sommige onderdelen staan achter een feature flag (`FeatureGate::WPG`, `FeatureGate::PUBLISHING`).
Andere hangen af van hoe de organisatie zich heeft ingericht. Die twee vragen om een verschillende
aanpak.

**Feature flag**: zet de `gate` op het Topic of de Task. De inhoud verdwijnt dan volledig uit de
handleiding — uit de navigatie, de zoekindex en de backlinks. Schrijf de tekst daarom alsof de
functie gewoon bestaat; geen "als uw organisatie de Wpg-module heeft".

**Goed** (Topic `wpg-register`, met `gate: FeatureGate::WPG`):
`Naast de AVG-registers kent OpenVWR het register WPG Verantwoordelijke Verwerkingen.`

**Fout**: `Als de Wpg-module is aangezet, ziet u mogelijk ook het register WPG Verantwoordelijke
Verwerkingen.`

**Inrichtingskeuze**: die kunt u niet weggateen, dus benoemt u hem in de tekst. Doe dat één keer,
kort, en zonder de lezer te laten raden.

**Goed**: `Werkt uw organisatie bijvoorbeeld niet met Mandaathouders, dan slaat u het ophalen van
een akkoord over en stelt een Privacy Officer de versie zelf vast.`

**Goed**: `Deze stap is alleen aan de orde als uw organisatie met Mandaathouders werkt en er
Mandaathouders aan de versie zijn gekoppeld.`

**Fout**: `Mogelijk ziet u deze knop, mogelijk ook niet.`

## 10. Koppen en titels

- Een `Task`-titel is een handeling in de infinitief: `Een verwerking vastleggen`,
  `Labels gebruiken`, `Een overzicht opvragen of exporteren`.
- Een `Topic`-titel is een zelfstandig naamwoord of naamwoordgroep: `Bewaartermijnen`,
  `Versie indienen en Mandaathouders koppelen`, `De rollen in het portaal`.
- Een `Chapter`-titel is één woord of een korte groep: `Registers`, `Goedkeuringsproces`,
  `Rollen en rechten`.
- Alleen de eerste letter is een hoofdletter, behalve bij eigennamen en rolnamen. Dus
  `Rollen en rechten`, niet `Rollen En Rechten`.
- Geen punt aan het eind van een kop.
- Binnen een Topic gebruikt u `###` voor tussenkoppen. Begin niet op `#` of `##`: de titel van het
  Topic is al de kop van dat blok.
- Een `summary` is één zin die zegt wat de lezer in dat blok vindt, met een punt:
  `De registratie indelen naar afdeling, locatie of werkterrein.`

## 11. Screenshots

Figuren zijn gewone markdown-afbeeldingen. De alt-tekst wordt door de renderer ook als bijschrift
onder de afbeelding gezet. Eén tekst dus, die twee dingen moet doen.

- Beschrijf **wat er te zien is en waarom het er staat**, niet het bestandspad en niet "screenshot
  van".
- Houd het onder de tien woorden.
- Geen punt aan het eind.
- Vermeld het onderwerp, niet alleen de schermnaam, als hetzelfde scherm vaker voorkomt.

**Goed**: `![Labels op de detailpagina van een verwerking](/handleiding/06_labels/02_...png)`

**Goed**: `![Hetzelfde labelveld bij Systemen/Applicaties](/handleiding/06_labels/04_systems_labels.png)`

**Fout**: `![Screenshot](/handleiding/06_labels/02_...png)` (zegt niets, en het bijschrift wordt
"Screenshot")

**Fout**: `![](/handleiding/06_labels/02_...png)` (geen alt-tekst; ontoegankelijk én geen bijschrift)

Een afbeelding vervangt geen tekst. Wie de afbeelding niet ziet, moet de stap toch kunnen
uitvoeren.

## 12. Woordenlijst

Vaste schrijfwijze van termen die in de handleiding voorkomen. Wijk hier niet van af; een tweede
spelling maakt de tekst onvindbaar voor wie zoekt.

| Schrijf | Niet | Toelichting |
| --- | --- | --- |
| AVG | A.V.G., avg | afkorting, hoofdletters |
| Wpg | WPG, WpG | wet: alleen de eerste letter een hoofdletter |
| WPG Verantwoordelijke Verwerkingen | Wpg-register | de registernaam luidt zo in de applicatie |
| Wpg-verwerking | WPG verwerking | samenstelling met afkorting krijgt een koppelteken |
| AVG-register, AVG-registers | AVG register | idem |
| Autoriteit Persoonsgegevens (AP) | autoriteit persoonsgegevens | eigennaam; kort daarna "de AP" |
| DPIA | dpia | |
| FG | F.G. | naast voluit: Functionaris Gegevensbescherming |
| verwerking | Verwerking | gewoon zelfstandig naamwoord |
| verwerkingsregister | verwerkings register | |
| verwerkingsverantwoordelijke | verwerkings-verantwoordelijke | |
| versie | Versie | |
| conceptversie | concept versie, concept-versie | |
| vaststellen | vast stellen | |
| vastgestelde versie | vastgesteld versie | |
| goedkeuren | goed keuren | |
| datalek, datalekken | data lek | |
| datalekregister | datalek register | |
| opzoeklijst | opzoek lijst, lookup list | in gebruikerstekst nooit "lookup list" |
| bewaartermijn | bewaar termijn | |
| label | tag | in de code heet het `Tag`, in de tekst altijd "label" |
| detailpagina | detail pagina | |
| navigatiemenu | navigatie menu | |
| e-mail, e-mailadres | email, e-mail adres | koppelteken, volgens de Woordenlijst |
| tweefactorauthenticatie | twee-factor-authenticatie | |
| notificatie | melding | "melding" is gereserveerd voor de melding bij de AP |
| het portaal | het systeem, de tool | naast "OpenVWR" en "de applicatie" |
| csv, xlsx | CSV, XLSX | als extensie: `.csv`, `.xlsx` |

Voor rolnamen: zie [Rollen benoemen](#8-rollen-benoemen).

> Afwijking in de bestaande tekst: `Wpg` en `WPG` staan door elkaar, deels omdat de registernaam in
> de applicatie `WPG Verantwoordelijke Verwerkingen` is. De wet is `Wpg`; de registernaam blijft
> zoals hij in het scherm staat. Ook staan `email` (zonder koppelteken) en `To Do lijst` nog in de
> Topics `akkoord-geven`, `goedkeuren` en `rollen`. En `de datalek` in `datalekken` moet
> `het datalek` zijn.

## Checklist voor een pull request

Loop deze lijst af voordat u een wijziging aan de handleiding indient.

**Structuur**

- [ ] Staat de uitleg op precies één plek, en linken de andere plekken ernaar?
- [ ] Blijft elke `Step` binnen één of twee zinnen, en heeft hij de juiste `topicIds`?
- [ ] Heeft elke nieuwe `Task` een `done` die de eindtoestand beschrijft?
- [ ] Heeft nieuwe optionele functionaliteit een `gate`, of is de inrichtingskeuze in de tekst
      benoemd?

**Taal**

- [ ] Overal u-vorm, geen "je" en geen "we"?
- [ ] Zinnen van 15 tot 20 woorden, actief geformuleerd?
- [ ] Steptitels in de gebiedende wijs en onderling parallel?
- [ ] Staat in elke stap de plaats vóór de handeling, en de voorwaarde vooraan?

**Details**

- [ ] Zijn alle UI-labels letterlijk overgenomen, tussen dubbele aanhalingstekens?
- [ ] Zijn rolnamen met hoofdletter geschreven?
- [ ] Volgt elke term de woordenlijst hierboven?
- [ ] Beschrijft elke linktekst het doel, zonder "hier" of een kale URL?
- [ ] Is elke `Let op` een echt risico, en elke `Hint` echt overslaanbaar?
- [ ] Heeft elke afbeelding een alt-tekst die als bijschrift leesbaar is?

**Controle**

- [ ] Is de tekst te begrijpen zonder de screenshots?
- [ ] Werken de ankers van alle interne links, en is er niets dubbel uitgelegd?

## Bronnen

- [Taalniveau B1](https://www.communicatierijk.nl/vakkennis/rijkswebsites/aanbevolen-richtlijnen/taalniveau-b1) — CommunicatieRijk: actieve schrijfstijl, duidelijke koppen, eenvoudige woorden.
- [Schrijftips voor taalniveau B1](https://iplo.nl/digitaal-stelsel/toepasbare-regels/maken-testen/schrijfwijzer/schrijftips-taalniveau-b1/) — Informatiepunt Leefomgeving: zinnen van 15 tot 20 woorden, actieve vorm, geen tangconstructies, geen naamwoordstijl.
- [Duidelijke teksten schrijven](https://www.gebruikercentraal.nl/themas/direct-duidelijk/duidelijke-teksten-schrijven/) — Direct Duidelijk / Gebruiker Centraal: korte zinnen en woorden, spreek de taal van de gebruiker.
- [Writing step-by-step instructions](https://learn.microsoft.com/en-us/style-guide/procedures-instructions/writing-step-by-step-instructions) — Microsoft Writing Style Guide: gebiedende wijs, volledige zinnen, plaats vóór handeling, parallelle koppen, `>` voor menupaden.
- [Link text](https://developers.google.com/style/link-text) — Google developer documentation style guide: geen "klik hier", geen kale URL's, gebruik de titel van het doel.
- [Succescriterium 2.4.4 Linkdoel (in context)](https://www.w3.org/Translations/WCAG21-nl/#link-purpose-in-context) — WCAG 2.1, niveau A: het doel van een link moet uit de linktekst blijken.
- [e-mailadres / emailadres](https://onzetaal.nl/taalloket/e-mailadres-emailadres) — Genootschap Onze Taal: koppelteken in samenstellingen met "e-mail".
- [Afkorting in samenstelling](https://onzetaal.nl/taalloket/afkorting-in-samenstelling) — Genootschap Onze Taal: een afkorting in een samenstelling krijgt een koppelteken (AVG-register, Wpg-verwerking).
