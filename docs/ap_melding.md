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

### Wat het register zelf hoort vast te leggen

Een datalek raakt meestal maar een deel van een verwerking. Uit de gekoppelde verwerking is dus
wel af te leiden welke gegevens er in het spel *kunnen* zijn, maar niet welke er werkelijk gelekt
zijn.

Een voorbeeld. Aan een zorgverwerking hangen gezondheidsgegevens. Raakt een datalek alleen de
adresgegevens uit die verwerking, dan zou "gegevens over iemands gezondheid" onterecht in de
melding belanden.

Voor de vragen waar het datalek zelf een veld voor heeft — welke persoonsgegevens (6.1), welke
bijzondere categorieën (6.2) en welke betrokkenen (7.2) — vult OpenVWR daarom niets in. Is het veld
leeg, dan blijft de vraag op "nog invullen" staan, met daarbij wat de gekoppelde verwerking noemt
als aanwijzing. Zo legt de privacy officer in het datalek vast wat er werkelijk gelekt is, wat
artikel 33 lid 5 sowieso van het register vraagt.

Antwoorden die over de verwerking zelf gaan en niet op het datalek thuishoren — de wettelijke
grondslag (1.2), de betrokken verwerkers en ontvangers (3.3) en de pseudonimisering (8.1) — worden
wel overgenomen, met vermelding van de verwerking waaruit ze komen.
