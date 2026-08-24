# Vaste lijsten (fixed lists)

Sommige keuzelijsten volgen uit een externe bron — een wet, een besluit van de Europese Commissie, een
standaard — en zijn daarmee voor elke organisatie gelijk. Die lijsten staan in code, niet in de database.

Dit is iets anders dan een *lookup list* (`App\Models\LookupListModel`): die is per organisatie en wordt door
de organisatie zelf beheerd. Een vaste lijst hoort bij de applicatie en verandert alleen bij een release.

De lijsten staan in `src/cms/app/FixedLists/Lists/`.

## Waarom een waarde niet verdwijnt

Een waarde die ooit is gekozen, blijft bestaan. Neem de adequaatheidsbesluiten: als de Europese Commissie het
besluit voor een land intrekt, dan waren de verwerkingen die dat land noemen op dat moment gewoon correct
vastgelegd. Het verwijderen van de waarde zou die registraties onbedoeld ongeldig maken — de gebruiker krijgt
dan een validatiefout op een veld dat hij niet heeft aangeraakt.

Daarom wordt een waarde *ingetrokken* in plaats van verwijderd:

```php
FixedListEntry::current('Japan'),
FixedListEntry::retired('Land X', 'adequaatheidsbesluit ingetrokken per 07-06-2027'),
```

Dat heeft drie gevolgen, zonder datamigratie:

1. Nieuwe invoer kan de waarde niet meer kiezen (het formulier toont hem grijs).
2. Bestaande registraties met die waarde blijven geldig en kunnen gewoon worden opgeslagen.
3. `fixed-lists:audit` rapporteert welke registraties het betreft, met de reden.

## Een lijst wijzigen

Pas de betreffende klasse in `app/FixedLists/Lists/` aan en deploy. Voeg een nieuwe waarde toe met
`FixedListEntry::current()`, trek een bestaande in met `FixedListEntry::retired()`.

**Wijzig nooit de tekst van een bestaande waarde.** Bij de landenlijst is de opgeslagen waarde de tekst zelf;
die aanpassen maakt de registraties die eraan hangen onvindbaar. Trek in dat geval de oude waarde in en voeg
een nieuwe toe.

## Controleren wat er in de database staat

```bash
sail artisan fixed-lists:audit
```

Het commando vergelijkt de opgeslagen waarden met de lijst waar ze vandaan komen en rapporteert drie soorten
bevindingen:

| Type      | Betekenis                                              | Actie                                     |
| --------- | ------------------------------------------------------ | ----------------------------------------- |
| `retired` | De waarde bestaat nog, maar is ingetrokken             | Grondslag opnieuw beoordelen              |
| `unknown` | De lijst heeft deze waarde nooit bevat                 | Datakwaliteit: import- of typefout        |
| `unused`  | De lijst bevat een waarde die niemand gebruikt         | Opruimen (optioneel)                      |

`unused` wordt standaard weggelaten, omdat het geen signaal is dat om actie vraagt. Met `--type=unused` is het
wel op te vragen; `--type=retired` en `--type=unknown` filteren op de andere soorten.

De telling gaat over alle organisaties heen en telt ook verwijderde (soft deleted) registraties mee, omdat die
hersteld kunnen worden.

## Een kolom onder de audit brengen

Registreer de combinatie van model, kolom en lijst in `App\FixedLists\FixedListRegistry`. Alleen wat daar
staat, wordt gecontroleerd.

Houdt de kolom daarnaast waarden vast die geldig zijn maar niet in de lijst horen, geef die dan mee als
`ignoredValues`. Bij de landenlijst is dat de "Anders, namelijk:"-waarde: die verwijst door naar het vrije
tekstveld `country_other` en is dus geen land, maar ook geen fout. Zonder die uitzondering zou elke registratie
met een land buiten de adequaatheidsbesluiten als `unknown` worden gerapporteerd.
