<!--
  Dit bestand wordt gegenereerd door `just docs-datamodel`.
  Wijzigingen hier gaan verloren.

  De veldtabellen komen uit de Filament-formulieren; pas die aan
  (labels en hulpteksten staan in resources/lang/). De omringende
  tekst staat in docs/prose/.
-->

# Inleiding

Dit document beschrijft welke gegevens in OpenVWR kunnen worden vastgelegd. Het is bedoeld voor
privacy officers, functionarissen gegevensbescherming en anderen die willen beoordelen of het
systeem aansluit bij hun registratiebehoefte. Er wordt geen technische kennis verondersteld.

## Leeswijzer

OpenVWR bestaat uit een aantal **registers**. Elk register is een aparte lijst met registraties van
een bepaald type. Welke registers beschikbaar zijn verschilt per installatie; de hoofdstukken
hierna beschrijven de registers die in deze versie aanwezig zijn.

Elke registratie wordt ingevuld via een formulier dat is opgedeeld in **stappen**. De tabellen
hierna volgen die stappen, zodat de indeling van dit document overeenkomt met wat een invuller in
het scherm ziet. De veldnamen en de toelichtingen zijn letterlijk overgenomen uit de applicatie.

In de kolom **Soort invoer** staat hoe een veld wordt ingevuld:

| Soort invoer | Betekenis |
| --- | --- |
| Tekst | Een korte regel tekst, bijvoorbeeld een naam. |
| Toelichting | Een langer tekstvak voor een omschrijving of onderbouwing. |
| Ja/nee | Een schakelaar of vinkje. |
| Datum | Een datum uit een kalender. |
| Keuze | Eén optie uit een vaste lijst. |
| Meerkeuze | Eén of meer opties uit een vaste lijst. |
| Meerkeuze (vrij) | Vrij te kiezen trefwoorden, niet uit een vaste lijst. |
| Koppeling | Een verwijzing naar een andere registratie, die daardoor hergebruikt en centraal beheerd wordt. |
| Lijst | Een herhaalbaar blok: de invuller kan er zoveel toevoegen als nodig, elk met eigen velden. |
| Bestand | Een te uploaden document. |

Een veld dat begint met **»** hoort bij het onderdeel erboven: het wordt per item van die lijst of
koppeling ingevuld. Een **vetgedrukte regel zonder soort invoer** is een tussenkop die bij elkaar
horende vragen groepeert.

Niet elk veld is altijd zichtbaar. Het formulier verbergt vragen die niet van toepassing zijn —
staat "Heeft verwerkers" bijvoorbeeld uit, dan verschijnt de tabel met verwerkers niet. Dit
document toont alle velden die het systeem kent; in de praktijk krijgt een invuller er dus minder
te zien.

---

# Verwerkingen AVG verantwoordelijke

Verwerkingen van persoonsgegevens waarbij uw organisatie zelf het doel en de middelen bepaalt.

## Naam verwerking

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Nummer verwerking | Tekst |  |
| Import nummer | Tekst |  |
| Naam verwerking | Tekst | Geef een korte, herkenbare naam die de verwerking beschrijft, niet het systeem of de afdeling. Gebruik het doel of de activiteit, bijvoorbeeld "Salarisadministratie" of "Cameratoezicht kantoorpanden". |
| Primair / Secundair | Keuze | Primair: hoort bij de eigen kerntaak. Secundair: bedrijfsvoering (zoals HR of ICT), vaak terugkerend en standaard. Keuze uit: Primair; Secundair. |
| AVG Verantwoordelijke Dienst | Koppeling | De dienst of afdeling die verantwoordelijk is voor deze verwerking. |
| Labels | Meerkeuze (vrij) |  |
| Periodieke review | Datum | Datum waarop deze verwerking opnieuw beoordeeld moet worden om te controleren of de gegevens nog kloppen. Standaard 2,5 jaar na livegang; rond die datum verschijnt de verwerking in het overzicht van te reviewen verwerkingen. |
| Hoofdverwerking | Koppeling | Alleen invullen als deze verwerking onderdeel is van een grotere verwerking. Kies dan de overkoepelende verwerking; deze verschijnt daar in de tabel "Subverwerkingen". Laat leeg voor een zelfstandige verwerking. |
| Subverwerkingen | Koppeling | De verwerkingen die onder deze verwerking vallen. Alleen zelfstandige verwerkingen kunnen worden gekoppeld; na het ontkoppelen is de verwerking weer zelfstandig. |

## Verwerkingsverantwoordelijke

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Verwerkingsverantwoordelijken | Koppeling | De organisatie(s) die het doel en de middelen van deze verwerking bepalen. Vul er meer in als de verantwoordelijkheid wordt gedeeld. |
| Verdeling verantwoordelijkheid | Toelichting | Alleen invullen bij meerdere verantwoordelijken: wie is waarvoor verantwoordelijk? |

## Verwerker

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Heeft verwerkers | Ja/nee | Is er sprake van (een of meerdere) verwerkers? |
| Verwerkers | Koppeling |  |

## Ontvanger

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Ontvangers | Koppeling | Aan wie de persoonsgegevens worden verstrekt, buiten uw eigen organisatie. Laat leeg als er geen ontvangers zijn. |

## Doel & grondslag

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| AVG doelen | Lijst |  |
| » Doel | Toelichting |  |
| » AVG rechtsgronden | Keuze | Keuze uit: Toestemming betrokkene; Uitvoering overeenkomst; Wettelijke verplichting; Vitaal belang betrokkene; Taak van algemeen belang; Gerechtvaardigd belang verantwoordelijke. |
| » Toelichting bij gekozen rechtsgrond | Toelichting |  |

## Betrokkenen en gegevens

Het meest gedetailleerde onderdeel van de registratie. Per categorie betrokkenen wordt vastgelegd welke gewone, bijzondere en gevoelige gegevens worden verwerkt, en per gegeven het verzameldoel, de bewaartermijn en de bron.

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Betrokkenen | Lijst |  |
| » Beschrijving | Toelichting |  |
| **Bijzondere gegevens** |  | Welke bijzondere gegevens die betrekking hebben op deze categorie van betrokkenen worden verwerkt? |
| » Biometrische gegevens met het oog op de unieke identificatie van een persoon | Ja/nee |  |
| » Persoonsgegevens waaruit religieuze of levensbeschouwelijke overtuigingen blijken | Ja/nee |  |
| » Genetische gegevens | Ja/nee |  |
| » Gegevens over gezondheid | Ja/nee |  |
| » Persoonsgegevens waaruit politieke opvattingen blijken | Ja/nee |  |
| » Persoonsgegevens waaruit ras of etnische afkomst blijkt | Ja/nee |  |
| » Gegevens met betrekking tot iemands seksueel gedrag of seksuele gerichtheid | Ja/nee |  |
| » Persoonsgegevens waaruit het lidmaatschap van een vakbond blijkt | Ja/nee |  |
| » Uitleg doorbreking verwerkingsverbod | Toelichting |  |
| **Gevoelige gegevens** |  | Welke gevoelige gegevens die betrekking hebben op deze categorie van betrokkenen worden verwerkt? |
| » Gegevens betreffende strafrechtelijke veroordelingen en strafbare feiten | Ja/nee |  |
| » Burgerservicenummers | Ja/nee |  |
| » Gegevens | Lijst |  |
| » » Beschrijving | Toelichting |  |
| » » Verzameldoel | Toelichting | Waarvoor juist deze gegevens nodig zijn binnen de verwerking. |
| » » Bewaartermijn | Toelichting | Hoe lang de gegevens bewaard worden en op welke grond; er wordt niet automatisch verwijderd. |
| » » Wie/wat is de bron van gegevens? | Toelichting | Van wie of waaruit de gegevens afkomstig zijn, bijvoorbeeld de betrokkene zelf of een basisregistratie. |
| » » Is betrokkene verplicht de gevraagde gegevens aan te leveren? | Ja/nee |  |
| » » Omschrijf de gevolgen voor de betrokkene als de gegevens niet worden aangeleverd | Toelichting | Wat er gebeurt als de betrokkene de gegevens niet verstrekt, bijvoorbeeld dat de aanvraag niet in behandeling kan worden genomen. |

## Besluitvorming

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Geautomatiseerde besluitvorming | Ja/nee | Zet aan als besluiten (deels) automatisch worden genomen, zonder betekenisvolle menselijke tussenkomst. Vul dan de toelichting hieronder in. |
| Toelichting besluitvorming | Toelichting | Welke gegevens tot welk besluit leiden, begrijpelijk beschreven en zonder technische details. |
| Toelichting belang en gevolgen | Toelichting | Wat het besluit concreet voor de betrokkene betekent, bijvoorbeeld toekenning of afwijzing. |

## Systemen / Applicaties

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Heeft systemen / applicaties | Ja/nee | Is er sprake van (een of meerdere) applicaties / systemen? |
| Systemen/Applicaties | Koppeling |  |

## Beveiliging

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Heeft beveiliging | Ja/nee | Beveiligt u de persoonsgegevens? |
| **Maatregelen** |  |  |
| Vastgesteld beveiligingsbeleid dat ook is geïmplementeerd | Ja/nee | Alleen aanvinken als het beleid formeel is vastgesteld én in de praktijk wordt toegepast. |
| Overige maatregelen | Ja/nee | Aanvinken als er maatregelen zijn buiten het vastgestelde beveiligingsbeleid. |
| Toelichting maatregelen | Toelichting | De concrete maatregelen, bijvoorbeeld versleuteling, autorisatiematrix of toegangscontrole. |
| Gebruikt u pseudonimisering | Ja/nee | Zet aan als de gegevens zijn vervangen door kenmerken die niet direct naar een persoon herleidbaar zijn. |
| Pseudonimisering | Toelichting | Hoe gegevens zijn vervangen door kenmerken die niet direct naar een persoon herleidbaar zijn. |

## Doorgifte

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Doorgifte buiten EER | Ja/nee | Geeft u bij uw gegevensverwerking persoonsgegevens door aan een of meer landen buiten de Europese Unie of aan een internationale organisatie? |
| Landen | Keuze | De landen buiten de EER waarnaar persoonsgegevens worden doorgegeven. Keuze uit: Andorra; Argentinië; Canada (alleen commerciële bedrijven); Faeröer Eilanden; Guernsey; Isle of Man; Israël; Japan; Jersey; Nieuw-Zeeland; Uruguay; Verenigd Koninkrijk; Verenigde Staten (organisaties die meedoen aan het Data Privacy Framework); Zwitserland; Zuid-Korea; Anders, namelijk:. |
| Anders, namelijk: | Tekst |  |
| Vallen alle doorgiftes onder een adequaatheidsbesluit? | Ja/nee | Zet aan als de gegevens uitsluitend worden doorgegeven aan landen of organisaties waarvan de Europese Commissie heeft vastgesteld dat zij een passend beschermingsniveau bieden (een adequaatheidsbesluit). Zet uit als dat niet zo is; beschrijf dan hieronder welke andere passende waarborgen de doorgifte afdekken. |
| Toelichting adequate bescherming | Toelichting | Beschrijf hier welke passende waarborgen (conform artikel 46 AVG) de doorgifte afdekken, bijvoorbeeld standaardcontractbepalingen of bindende bedrijfsvoorschriften. |
| Toelichting doorgifte | Toelichting | Welke gegevens worden doorgegeven en met welk doel. |

## GEB (DPIA)

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| GEB (DPIA) uitgevoerd | Ja/nee | Zet aan als er daadwerkelijk een GEB is uitgevoerd; de vragen hieronder bepalen of dat verplicht is. |
| **Is een GEB (DPIA) verplicht?** |  | Beantwoord de vragen één voor één. Zodra u één vraag met "ja" beantwoordt, is een GEB (DPIA) verplicht en vervallen de overige vragen. |
| Systematische en uitgebreide beoordeling van persoonlijke aspecten | Ja/nee |  |
| Grootschalige verwerking van bijzondere categorieën | Ja/nee |  |
| Grootschalige monitoring | Ja/nee |  |
| Autoriteit Persoonsgegevens gepubliceerde lijst | Ja/nee |  |
| De negen criteria van WP248 | Ja/nee |  |
| Hoog risico voor rechten en vrijheden | Ja/nee |  |

## Contactpersoon

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Primair contact | Koppeling | Wie als eerste benaderd wordt bij vragen over deze registratie. |
| Overige contactpersonen | Koppeling | Aanvullende contactpersonen, bijvoorbeeld de beheerder van het systeem. |

## Documenten & bijlagen

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Documenten | Koppeling |  |

## Opmerkingen

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Opmerkingen | Lijst |  |
| » Opmerking | Toelichting |  |

## Publiceren

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Publiceer vanaf | Datum |  |
| **Publieke beschikbaarheid** |  | Periodiek controleren we de publieke status van deze verwerking. In dit overzicht geven we de publieke beschikbaarheid weer. |


---

# Verwerkingen AVG verwerker

Verwerkingen van persoonsgegevens die uw organisatie in opdracht van een andere verantwoordelijke uitvoert.

## Naam verwerking

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Nummer verwerking | Tekst |  |
| Import nummer | Tekst |  |
| Naam verwerking | Tekst | Geef een korte, herkenbare naam die de verwerking beschrijft, niet het systeem of de afdeling. Gebruik het doel of de activiteit, bijvoorbeeld "Salarisadministratie" of "Cameratoezicht kantoorpanden". |
| Primair / Secundair | Keuze | Primair: hoort bij de eigen kerntaak. Secundair: bedrijfsvoering (zoals HR of ICT), vaak terugkerend en standaard. Keuze uit: Primair; Secundair. |
| AVG Verwerking Dienst | Koppeling | De dienst of afdeling die deze verwerking voor de verantwoordelijke uitvoert. |
| Labels | Meerkeuze (vrij) |  |
| Periodieke review | Datum | Datum waarop deze verwerking opnieuw beoordeeld moet worden om te controleren of de gegevens nog kloppen. Standaard 2,5 jaar na livegang; rond die datum verschijnt de verwerking in het overzicht van te reviewen verwerkingen. |
| Hoofdverwerking | Koppeling | Alleen invullen als deze verwerking onderdeel is van een grotere verwerking. Kies dan de overkoepelende verwerking; deze verschijnt daar in de tabel "Subverwerkingen". Laat leeg voor een zelfstandige verwerking. |
| Subverwerkingen | Koppeling | De verwerkingen die onder deze verwerking vallen. Alleen zelfstandige verwerkingen kunnen worden gekoppeld; na het ontkoppelen is de verwerking weer zelfstandig. |

## Verwerkingsverantwoordelijke

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Verwerkingsverantwoordelijken | Koppeling | De verantwoordelijke(n) in wiens opdracht u deze gegevens verwerkt. |

## Subverwerker

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Heeft subverwerkers | Ja/nee | Is er sprake van (een of meerdere) subverwerkers? |
| Subverwerkers | Koppeling |  |

## Ontvanger

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Ontvangers | Koppeling | Aan wie de persoonsgegevens worden verstrekt, buiten uw eigen organisatie. Laat leeg als er geen ontvangers zijn. |

## Doel & grondslag

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Doel & Grondslag | Ja/nee | Is er een specifiek doel voor deze verwerking vastgelegd? |
| AVG doelen | Lijst |  |
| » Doel | Toelichting |  |
| » AVG rechtsgronden | Keuze | Keuze uit: Toestemming betrokkene; Uitvoering overeenkomst; Wettelijke verplichting; Vitaal belang betrokkene; Taak van algemeen belang; Gerechtvaardigd belang verantwoordelijke. |
| » Toelichting bij gekozen rechtsgrond | Toelichting |  |

## Betrokkenen en gegevens

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Betrokkenen en gegevens | Ja/nee | Is er sprake van een (of meerdere) categorieën van betrokkenen? |
| Betrokkenen | Lijst |  |
| » Beschrijving | Toelichting |  |
| **Bijzondere gegevens** |  | Welke bijzondere gegevens die betrekking hebben op deze categorie van betrokkenen worden verwerkt? |
| » Biometrische gegevens met het oog op de unieke identificatie van een persoon | Ja/nee |  |
| » Persoonsgegevens waaruit religieuze of levensbeschouwelijke overtuigingen blijken | Ja/nee |  |
| » Genetische gegevens | Ja/nee |  |
| » Gegevens over gezondheid | Ja/nee |  |
| » Persoonsgegevens waaruit politieke opvattingen blijken | Ja/nee |  |
| » Persoonsgegevens waaruit ras of etnische afkomst blijkt | Ja/nee |  |
| » Gegevens met betrekking tot iemands seksueel gedrag of seksuele gerichtheid | Ja/nee |  |
| » Persoonsgegevens waaruit het lidmaatschap van een vakbond blijkt | Ja/nee |  |
| » Uitleg doorbreking verwerkingsverbod | Toelichting |  |
| **Gevoelige gegevens** |  | Welke gevoelige gegevens die betrekking hebben op deze categorie van betrokkenen worden verwerkt? |
| » Gegevens betreffende strafrechtelijke veroordelingen en strafbare feiten | Ja/nee |  |
| » Burgerservicenummers | Ja/nee |  |
| » Gegevens | Lijst |  |
| » » Beschrijving | Toelichting |  |
| » » Verzameldoel | Toelichting | Waarvoor juist deze gegevens nodig zijn binnen de verwerking. |
| » » Bewaartermijn | Toelichting | Hoe lang de gegevens bewaard worden en op welke grond; er wordt niet automatisch verwijderd. |
| » » Wie/wat is de bron van gegevens? | Toelichting | Van wie of waaruit de gegevens afkomstig zijn, bijvoorbeeld de betrokkene zelf of een basisregistratie. |
| » » Is betrokkene verplicht de gevraagde gegevens aan te leveren? | Ja/nee |  |
| » » Omschrijf de gevolgen voor de betrokkene als de gegevens niet worden aangeleverd | Toelichting | Wat er gebeurt als de betrokkene de gegevens niet verstrekt, bijvoorbeeld dat de aanvraag niet in behandeling kan worden genomen. |

## Besluitvorming

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Geautomatiseerde besluitvorming | Ja/nee | Is er sprake van geautomatiseerde besluitvorming? |
| Toelichting besluitvorming | Toelichting | Welke gegevens tot welk besluit leiden, begrijpelijk beschreven en zonder technische details. |
| Toelichting belang en gevolgen | Toelichting | Wat het besluit concreet voor de betrokkene betekent, bijvoorbeeld toekenning of afwijzing. |

## Systemen / Applicaties

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Heeft systemen / applicaties | Ja/nee | Is er sprake van (een of meerdere) applicaties / systemen? |
| Systemen/Applicaties | Koppeling |  |

## Beveiliging

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Heeft beveiliging | Ja/nee | Beveiligt u de persoonsgegevens? |
| **Maatregelen** |  |  |
| Vastgesteld beveiligingsbeleid dat ook is geïmplementeerd | Ja/nee | Alleen aanvinken als het beleid formeel is vastgesteld én in de praktijk wordt toegepast. |
| Overige maatregelen | Ja/nee | Aanvinken als er maatregelen zijn buiten het vastgestelde beveiligingsbeleid. |
| Toelichting maatregelen | Toelichting | De concrete maatregelen, bijvoorbeeld versleuteling, autorisatiematrix of toegangscontrole. |
| **Pseudonimisatie** |  |  |
| Pseudonimisering van gegevens | Ja/nee | Zet aan als de gegevens zijn vervangen door kenmerken die niet direct naar een persoon herleidbaar zijn. |
| Toelichting | Toelichting | Hoe gegevens zijn vervangen door kenmerken die niet direct naar een persoon herleidbaar zijn. |

## Doorgifte

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Doorgifte buiten EER | Ja/nee | Geeft u bij uw gegevensverwerking persoonsgegevens door aan een of meer landen buiten de Europese Unie of aan een internationale organisatie? |
| Landen | Keuze | De landen buiten de EER waarnaar persoonsgegevens worden doorgegeven. Keuze uit: Andorra; Argentinië; Canada (alleen commerciële bedrijven); Faeröer Eilanden; Guernsey; Isle of Man; Israël; Japan; Jersey; Nieuw-Zeeland; Uruguay; Verenigd Koninkrijk; Verenigde Staten (organisaties die meedoen aan het Data Privacy Framework); Zwitserland; Zuid-Korea; Anders, namelijk:. |
| Anders, namelijk: | Tekst |  |
| Vallen alle doorgiftes onder een adequaatheidsbesluit? | Ja/nee | Worden de gegevens uitsluitend doorgegeven aan landen waarvan de commissie heeft besloten dat zij een passend beschermingsniveau bieden? |
| Toelichting | Toelichting | Vermeld hier welke andere passende waarborgen (conform artikel 46 AVG) er zijn, hoe er een kopie van kan worden verkregen of waar deze waarborgen kunnen worden geraadpleegd. Indien van toepassing, de documenten inzake de passende waarborgen o.b.v. artikel 49, eerste lid, tweede alinea AVG moeten worden toegevoegd in het AVG-register (bij de Bijlagen). Dit gelet op artikel 30, eerste lid, onder de AVG. * |
| Toelichting | Toelichting | Welke gegevens worden doorgegeven en met welk doel. |

## GEB (DPIA)

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| GEB (DPIA) | Ja/nee | Is er een GEB (DPIA) door de verantwoordelijke uitgevoerd? |

## Contactpersoon

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Primair contact | Koppeling | Wie als eerste benaderd wordt bij vragen over deze registratie. |
| Overige contactpersonen | Koppeling | Aanvullende contactpersonen, bijvoorbeeld de beheerder van het systeem. |

## Documenten & bijlagen

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Documenten | Koppeling |  |

## Opmerkingen

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Opmerkingen | Lijst |  |
| » Opmerking | Toelichting |  |


---

# Verwerkingen WPG verantwoordelijke

Verwerkingen van politiegegevens die onder de Wet politiegegevens vallen in plaats van onder de AVG.

## Naam verwerking

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Nummer verwerking | Tekst |  |
| Import nummer | Tekst |  |
| Naam verwerking | Tekst | Geef een korte, herkenbare naam die de verwerking beschrijft, niet het systeem of de afdeling. Gebruik het doel of de activiteit, bijvoorbeeld "Salarisadministratie" of "Cameratoezicht kantoorpanden". |
| Primair / Secundair | Keuze | Primair: hoort bij de eigen kerntaak. Secundair: bedrijfsvoering (zoals HR of ICT), vaak terugkerend en standaard. Keuze uit: Primair; Secundair. |
| WPG Verwerking Dienst | Koppeling | De dienst of afdeling die verantwoordelijk is voor deze verwerking. |
| Labels | Meerkeuze (vrij) |  |
| Periodieke review | Datum | Datum waarop deze verwerking opnieuw beoordeeld moet worden om te controleren of de gegevens nog kloppen. Standaard 2,5 jaar na livegang; rond die datum verschijnt de verwerking in het overzicht van te reviewen verwerkingen. |
| Hoofdverwerking | Koppeling | Alleen invullen als deze verwerking onderdeel is van een grotere verwerking. Kies dan de overkoepelende verwerking; deze verschijnt daar in de tabel "Subverwerkingen". Laat leeg voor een zelfstandige verwerking. |
| Subverwerkingen | Koppeling | De verwerkingen die onder deze verwerking vallen. Alleen zelfstandige verwerkingen kunnen worden gekoppeld; na het ontkoppelen is de verwerking weer zelfstandig. |

## Verwerkingsverantwoordelijke

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Verwerkingsverantwoordelijken | Koppeling | De organisatie(s) die het doel en de middelen van deze verwerking bepalen. |

## Verwerker

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Heeft verwerkers | Ja/nee | Is er sprake van verwerkers |
| Verwerkers | Koppeling |  |

## Ontvanger

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| **Politiegegevens ter beschikking gesteld** |  |  |
| Artikel 15: Aan ontvangers binnen het Wpg-domein in Nederland | Ja/nee |  |
| Artikel 15a: Aan ontvangers belast met politietaken en EU-organisaties belast met opsporing binnen de EU maar anders dan Nederland | Ja/nee |  |
| Toelichting | Toelichting | Aan welke ontvangers politiegegevens ter beschikking worden gesteld en waarom. |
| **Politiegegevens verstrekt aan derden** |  |  |
| Artikel 16: aan het Openbaar Ministerie, de burgemeester of de verwerkingsverantwoordelijke zelf voor specifieke taken zoals genoemd in art. 16, lid 1 sub c Wpg | Ja/nee |  |
| Artikel 17: aan de Nederlandse veiligheidsdiensten | Ja/nee |  |
| Artikel 18: aan ontvangers genoemd in lagere regelgeving of op basis van een machtiging van de minister van J&V | Ja/nee |  |
| Artikel 19: aan ontvangers van incidentele gevallen | Ja/nee | Eenmalige verstrekking die per geval wordt afgewogen, niet structureel geregeld. |
| Artikel 20: aan ontvangers in samenwerkingsverbanden | Ja/nee |  |
| Artikel 22: aan ontvangers t.b.v. wetenschappelijk onderzoek en statistiek | Ja/nee |  |
| Artikel 23: rechtstreekse verstrekking | Ja/nee | De ontvanger heeft rechtstreeks geautomatiseerde toegang tot de politiegegevens. |
| Artikel 24: rechtstreekse verstrekking aan inlichtingen- en veiligheidsdiensten | Ja/nee | Alleen voor de inlichtingen- en veiligheidsdiensten; verstrekking aan de Nederlandse veiligheidsdiensten valt onder artikel 17. |
| Toelichting | Toelichting | Per aangevinkt artikel: welke gegevens aan wie worden verstrekt. |
| **Politiegegevens doorgegeven** |  |  |
| Artikel 17a: aan ontvangers in derde landen of aan internationale organisaties die belast zijn met opsporingstaken | Ja/nee |  |
| Toelichting | Toelichting | Naar welk land of welke organisatie wordt doorgegeven en welke waarborgen daarvoor gelden. |

## Doel & grondslag

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| WPG doelen | Lijst |  |
| » Omschrijving | Toelichting | Welke politiegegevens voor welk doel worden verwerkt. |
| » Art. 8: Uitvoering dagelijkse politietaak (signaalafhandeling) | Ja/nee | De dagelijkse politietaak: binnenkomende signalen en meldingen die worden afgehandeld. |
| » Art. 9: Onderzoek i.v.m. handhaving rechtsorde | Ja/nee | Een specifiek onderzoek naar een concreet strafbaar feit, met een afgebakende onderzoeksvraag. |
| » Art. 10, lid 1a: Register zware criminaliteit | Ja/nee |  |
| » Art. 10, lid 1b: Themaverwerkingen | Ja/nee | Verwerking rond een maatschappelijk verschijnsel in plaats van één concreet strafbaar feit. |
| » Art. 10, lid 1c: Regionale Inlichtingen diensten Openbare Orde | Ja/nee | Verwerking door de Regionale Inlichtingendienst ten behoeve van de openbare orde. |
| » Art. 12: Controle en beheer informanten | Ja/nee |  |
| » Art. 13, lid 1: Landelijk raadpleegbare politiegegevens | Ja/nee |  |
| » Art. 13, lid 2: Specialistische onderwerpen | Ja/nee | Gegevens over een specialistisch onderwerp, bijvoorbeeld verkeer of milieu. |
| » Art. 13, lid 3: Geautomatiseerde vergelijking | Ja/nee | Het geautomatiseerd vergelijken van gegevensbestanden met elkaar. |
| » Uitleg | Toelichting | Waarom de aangevinkte artikelen op deze verwerking van toepassing zijn. |

## Bijzondere politiegegevens

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| **Bijzondere politiegegevens** |  |  |
| Politiegegevens waaruit ras of etnische afkomst blijkt | Ja/nee |  |
| Politiegegevens waaruit politieke opvatting blijken | Ja/nee |  |
| Politiegegevens waaruit religieuze of levensbeschouwelijke overtuigingen blijken | Ja/nee |  |
| Politiegegevens waaruit het lidmaatschap van een vakbond blijkt | Ja/nee |  |
| Genetische politiegegevens | Ja/nee |  |
| Biometrische politiegegevens met het oog op de unieke identificatie van een persoon | Ja/nee |  |
| Politiegegevens over gezondheid | Ja/nee |  |
| Politiegegevens met betrekking tot iemands seksueel gedrag of seksuele geaardheid | Ja/nee |  |

## Besluitvorming

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| **Besluitvorming** |  |  |
| Geautomatiseerde besluitvorming | Ja/nee | Zet aan als besluiten (deels) automatisch worden genomen, zonder betekenisvolle menselijke tussenkomst. Vul dan de toelichting hieronder in. |
| Toelichting | Toelichting |  |
| Consequenties | Toelichting | Wat het besluit concreet voor de betrokkene betekent. |

## Systemen / Applicaties

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Heeft systeem / applicatie | Ja/nee | Is er sprake van een (of meerdere) systemen/applicaties |
| Systemen/Applicaties | Koppeling |  |

## Beveiliging

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Beveiliging | Ja/nee | Beveiligt u de politiegegevens? |
| **Maatregelen** |  |  |
| Vastgesteld beveiligingsbeleid dat ook is geïmplementeerd | Ja/nee | Alleen aanvinken als het beleid formeel is vastgesteld én in de praktijk wordt toegepast. |
| Overige maatregelen | Ja/nee | Aanvinken als er maatregelen zijn buiten het vastgestelde beveiligingsbeleid. |
| Toelichting maatregelen | Toelichting | De concrete maatregelen, bijvoorbeeld versleuteling, autorisatiematrix of toegangscontrole. |
| Gebruikt u pseudonimisering? | Ja/nee | Zet aan als de gegevens zijn vervangen door kenmerken die niet direct naar een persoon herleidbaar zijn. |
| Pseudonimisering | Toelichting | Hoe gegevens zijn vervangen door kenmerken die niet direct naar een persoon herleidbaar zijn. |

## GEB (DPIA)

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| GEB (DPIA) | Ja/nee | Is er sprake van een verwerking die een hoog risico voor de rechten van de vrijheden van personen oplevert? |

## Contactpersoon

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Primair contact | Koppeling | Wie als eerste benaderd wordt bij vragen over deze registratie. |
| Overige contactpersonen | Koppeling | Aanvullende contactpersonen, bijvoorbeeld de beheerder van het systeem. |

## Documenten & bijlagen

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Documenten | Koppeling |  |

## Opmerkingen

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Opmerkingen | Lijst |  |
| » Opmerking | Toelichting |  |

## Categorieën betrokkenen

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| **Categorieën betrokkenen** |  |  |
| Verdachten | Ja/nee | Zet aan als er politiegegevens van verdachten worden verwerkt. |
| Slachtoffers | Ja/nee | Zet aan als er politiegegevens van slachtoffers worden verwerkt. |
| Veroordeelden | Ja/nee | Zet aan als er politiegegevens van veroordeelden worden verwerkt. |
| Politie/Justitie | Ja/nee | Betrokkenen die werkzaam zijn bij politie of justitie. |
| Derden | Ja/nee | Overige betrokkenen, zoals getuigen, melders of contactpersonen. |
| Toelichting derden | Toelichting | Om welke groep derden het gaat en waarom hun gegevens nodig zijn. |


---

# Algoritmes

Algoritmes die uw organisatie inzet, met het oog op publicatie in het landelijke Algoritmeregister.

## Naam algoritme

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Nummer | Tekst |  |
| Naam | Tekst | Een korte, herkenbare naam voor het algoritme, zoals een burger het zou herkennen. |
| Korte omschrijving | Toelichting | Eén alinea in begrijpelijke taal voor een burger; maximaal 400 tekens. |
| Thema | Koppeling | Het beleidsterrein waarop het algoritme wordt ingezet, bijvoorbeeld Openbare orde en veiligheid. |
| Status | Koppeling | De fase waarin het algoritme zich bevindt: in ontwikkeling, in gebruik of uitgefaseerd. |
| Begindatum | Datum | De datum waarop het algoritme in gebruik is genomen. |
| Einddatum | Datum | Alleen invullen als het gebruik van het algoritme is beëindigd. |
| Contactgegevens | Tekst | Bij voorkeur een functioneel e-mailadres, geen persoonlijk adres. |
| Link naar publiekspagina | Tekst | Een pagina op uw eigen website met uitleg over dit algoritme voor burgers. |
| Publicatiecategorie | Koppeling | Bepaalt hoe uitgebreid het algoritme op de publieke website wordt gepubliceerd. |
| Link naar bronregistratie | Tekst | De registratie in een ander register; laat leeg als dit register de bron is. |

## Verantwoord gebruik

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Doel en impact | Toelichting |  |
| Afwegingen | Toelichting |  |
| Menselijke tussenkomst | Toelichting |  |
| Risicobeheer | Toelichting |  |
| Titel van wettelijke basis | Tekst |  |
| Wettelijke basis | Toelichting |  |
| Link naar wettelijke basis | Tekst |  |
| Link naar verwerkingsregister | Tekst |  |
| Impacttoetsen | Toelichting |  |
| Links naar impacttoetsen | Toelichting |  |
| Toelichting op impacttoetsen | Toelichting |  |

## Werking

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Titel van gegevensbron | Tekst |  |
| Gegevens | Toelichting |  |
| Links naar gegevensbronnen | Toelichting |  |
| Technische werking | Toelichting |  |
| Leverancier | Toelichting | Vul "Intern ontwikkeld" in als het algoritme niet is ingekocht. |
| Link naar broncode | Tekst |  |

## Metadata

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Datum van ontwikkeling | Datum | De datum waarop de ontwikkeling is afgerond, niet de datum van ingebruikname. |
| Eigenaar van het algoritme | Tekst | De organisatie of afdeling die verantwoordelijk is voor het algoritme. |
| Product owner van het algoritme | Tekst | De persoon of rol die inhoudelijk beslist over het algoritme. |
| Extern registratienummer | Tekst |  |
| Bron-ID | Tekst | Het identificatienummer uit uw eigen registratie of bronsysteem. |
| Zoektermen | Toelichting | Zoektermen gescheiden door komma's; deze zijn niet zichtbaar op de publieke website. |

## Impactvol algoritme

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Is er sprake van een proces met directe gevolgen voor burgers of organisaties? | Keuze | Denk aan een besluit over een uitkering, vergunning of handhaving; interne rapportage telt niet mee. Keuze uit: Ja; Nee. |
| Worden er in dit proces één of meerdere algoritmes toegepast? | Keuze | Ook eenvoudige beslisregels of rekenregels tellen mee als algoritme. Keuze uit: Ja; Nee. |
| Heeft het algoritme een significant effect op de uitkomst van het proces? | Keuze | Significant betekent dat de uitkomst zonder het algoritme wezenlijk anders had kunnen zijn. Keuze uit: Ja; Nee. |

## Validatie

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Antwoorden op de toetsvragen gecontroleerd door product owner | Keuze | Zet op Ja zodra de product owner de antwoorden op de impactvragen heeft bevestigd. Keuze uit: Ja; Nee. |

## Documenten & bijlagen

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Documenten | Koppeling |  |


---

# Datalekken

Inbreuken op de beveiliging van persoonsgegevens, inclusief de melding aan de Autoriteit Persoonsgegevens.

## Naam datalek

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Nummer | Tekst |  |
| Naam | Tekst | Een korte, herkenbare omschrijving van het datalek. |
| Datum melding | Datum | De datum waarop het datalek intern is gemeld. |
| Type | Keuze | Kies Voorlopig zolang het onderzoek loopt en Definitief zodra alle gegevens bekend zijn. Keuze uit: Voorlopig; Definitief. |
| Gemeld aan de autoriteit persoonsgegevens (AP) | Ja/nee | Bij het opslaan ontvangen de Chief Privacy Officers en Functionarissen Gegevensbescherming hierover automatisch een e-mail. |

## Verantwoordelijke

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Verwerkingsverantwoordelijken | Koppeling |  |

## Data

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Datum ontdekking datalek | Datum | Het moment waarop het datalek intern is ontdekt; dit is het startpunt voor de meldtermijn. |
| (Vermoedelijke) startdatum inbreuk | Datum | Een schatting volstaat als de exacte datum niet bekend is. |
| Einddatum inbreuk | Datum | De datum waarop het lek is gedicht. |
| Datum melding AP | Datum | De datum waarop het datalek bij de Autoriteit Persoonsgegevens is gemeld. |
| Datum afronding | Datum | De datum waarop het onderzoek en de maatregelen zijn afgerond. |

## Incident

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Aard van incident | Keuze | Wat er feitelijk is gebeurd, bijvoorbeeld verkeerd verzonden of kwijtgeraakte gegevens. Keuze uit: E-mail met persoonsgegevens verstuurd aan verkeerde ontvanger(s); E-mail verstuurd met persoonsgegevens met ontvangers in het aan-veld of in de cc, in plaats van bcc; Brief of postpakket met persoonsgegevens verstuurd of afgegeven aan de verkeerde ontvanger(s); Brief of postpakket met persoonsgegevens geopend retour ontvangen; Brief of postpakket met persoonsgegevens kwijtgeraakt; Autorisatie(s) van medewerker(s) verkeerd ingesteld.; Netwerkmappen of -locaties met persoonsgegevens zijn te breed toegankelijk ingesteld binnen de organisatie; Apparaat, gegevensdrager (bijv. USB-stick) en/of papier met persoonsgegevens kwijtgeraakt of gestolen; Persoonsgegevens per ongeluk gepubliceerd; Hacking, malware (bijv. ransomware) en/of phishing; Persoonsgegevens toegevoegd aan verkeer dossier; Overig. |
| Namelijk | Toelichting |  |
| Samenvatting incident | Toelichting | Wat er is gebeurd, wanneer het speelde en hoe het is ontdekt. |
| Betrokken groep(en) personen | Toelichting | Om welke groep het gaat en om hoeveel personen ongeveer. |
| Categorieën van persoonsgegevens | Meerkeuze | Kruis aan welke soorten persoonsgegevens bij het lek betrokken waren. Keuze uit: Naam; Geslacht; Geboortedatum en/of leeftijd; Burgerservicenummer (BSN); Contactgegevens; Adres en woonplaats; E-mailadres; Telefoonnummer; Toegangs- of identificatiegegevens; Financiële gegevens; Bankrekeningnummer / IBAN; Creditcardgegevens; Gegevens over (problematische) schulden; Gegevens over uitkering en/of schulden; Andere financiële gegevens, namelijk:[open veld]; (Kopieën van) paspoorten of andere legitimatiebewijzen; Locatiegegevens; Persoonsgegevens betreffende strafrechtelijke veroordelingen en strafbare feiten of daarmee verband houdende veiligheidsmaatregelen; Onbekend; Anders. |
| Namelijk | Toelichting |  |
| Bijzondere categorieën van persoonsgegevens | Meerkeuze | Bijzondere gegevens verhogen het risico en daarmee de kans op een meldplicht. Keuze uit: Persoonsgegevens waaruit iemands ras of etnische afkomst blijkt; Persoonsgegevens waaruit iemands politieke opvattingen blijken; Persoonsgegevens waaruit iemands religieuze of levensbeschouwelijke overtuigingen blijken; Persoonsgegevens waaruit iemands lidmaatschap van een vakbond blijkt; Gegevens met betrekking tot iemands seksueel gedrag of seksuele gerichtheid; Gegevens over iemands gezondheid; Genetische gegevens; Biometrische gegevens (bijvoorbeeld: vingerafdruk of irisscan). |
| Inschatting risico | Toelichting | De gevolgen voor de betrokkenen; bij een hoog risico moeten zij zelf geïnformeerd worden. |
| Maatregelen | Toelichting | Zowel de directe maatregelen als de maatregelen om herhaling te voorkomen. |
| Gemeld aan betrokkene | Ja/nee | Verplicht wanneer het datalek waarschijnlijk een hoog risico voor de betrokkenen oplevert. |
| Communicatiemiddel melding betrokkene | Meerkeuze | Keuze uit: Telefonisch; Per brief; Per e-mail; Via een mededeling op de website; Via social media; Via een advertentie in de krant; Anders. |
| Namelijk | Toelichting |  |
| Gemeld aan FG | Ja/nee | De Functionaris Gegevensbescherming moet bij ieder datalek betrokken worden. |

## Verwerkingen

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Verwerkingen AVG verantwoordelijke | Keuze | Koppel de verwerkingen waarop dit datalek betrekking heeft, zodat het lek vindbaar is vanuit die registraties. |
| Verwerkingen AVG verwerker | Keuze |  |
| Verwerkingen WPG verantwoordelijke | Keuze |  |

## Documenten & bijlagen

| Veld | Soort invoer | Toelichting |
| --- | --- | --- |
| Documenten | Koppeling |  |


---

# Gegevens die het systeem zelf bijhoudt

Naast de ingevulde velden legt OpenVWR een aantal zaken automatisch vast. Deze zijn van belang voor
de aantoonbaarheid van het register.

| Onderdeel | Wat wordt vastgelegd |
| --- | --- |
| **Wijzigingshistorie** | Van elke registratie wordt bijgehouden wie wat wanneer heeft gewijzigd. Versies zijn onderling te vergelijken, inclusief wijzigingen in gekoppelde onderdelen zoals betrokkenen of systemen. |
| **Versies en goedkeuring** | Registraties kunnen worden vastgelegd in een versie die ter goedkeuring wordt voorgelegd aan mandaathouders. Hun akkoord wordt met datum en persoon vastgelegd. |
| **Organisatie** | Elke registratie hoort bij één organisatie. Gebruikers zien uitsluitend de registraties van de organisatie(s) waartoe zij toegang hebben. |
| **Gebruikers en rollen** | Van gebruikers worden naam, e-mailadres en toegewezen rollen vastgelegd. Rollen bepalen wie mag lezen, invullen, goedkeuren of beheren. |
| **Aanmaak- en wijzigingsdatum** | Van elke registratie en elk gekoppeld onderdeel. |
| **Verwijderde registraties** | Verwijderde registraties blijven bewaard en zijn herstelbaar; ze verdwijnen uit de reguliere overzichten. |
| **Import-herkomst** | Bij registraties die uit een eerder register zijn overgenomen, blijft de oorspronkelijke herkomst bewaard. |

# Hergebruik van gegevens

Een aantal onderdelen wordt niet per verwerking opnieuw ingevoerd, maar één keer vastgelegd en
vervolgens aan meerdere verwerkingen gekoppeld. Dat voorkomt afwijkende schrijfwijzen en maakt het
mogelijk om vanuit één punt te overzien waar iets wordt gebruikt.

| Onderdeel | Hergebruikt over |
| --- | --- |
| Verwerkingsverantwoordelijken | Verwerkingen AVG verantwoordelijke, Verwerkingen AVG verwerker, Verwerkingen WPG verantwoordelijke, Datalekken |
| Verwerkers | Verwerkingen AVG verantwoordelijke, Verwerkingen AVG verwerker, Verwerkingen WPG verantwoordelijke |
| Ontvangers | Verwerkingen AVG verantwoordelijke, Verwerkingen AVG verwerker |
| Systemen / applicaties | Verwerkingen AVG verantwoordelijke, Verwerkingen AVG verwerker, Verwerkingen WPG verantwoordelijke |
| Contactpersonen | Verwerkingen AVG verantwoordelijke, Verwerkingen AVG verwerker, Verwerkingen WPG verantwoordelijke |
| Documenten | Alle registers |
| Labels | Verwerkingen AVG verantwoordelijke, Verwerkingen AVG verwerker, Verwerkingen WPG verantwoordelijke |

Zo is bijvoorbeeld van één leverancier in één oogopslag te zien bij welke verwerkingen die
betrokken is, en van één systeem welke verwerkingen erin plaatsvinden.

Diensten en afdelingen zijn hierop een uitzondering: elk register heeft daarvoor een eigen lijst.

# Publicatie

Verwerkingen, algoritmes en de bijbehorende gegevens kunnen worden gepubliceerd op een openbare
website, zodat burgers het register kunnen inzien. Publicatie gebeurt niet automatisch: per
registratie wordt een datum ingesteld vanaf wanneer publicatie is toegestaan. Zonder die datum
blijft de registratie intern. Het systeem controleert vooraf of een registratie geschikt is voor
publicatie en toont eventuele aandachtspunten.

DPIA's en pre-scans kennen deze publicatiefunctie niet: die registraties blijven intern. Dat sluit
aan bij de praktijk, waarin een DPIA doorgaans niet integraal openbaar wordt gemaakt.

Daarnaast kunnen registraties worden geëxporteerd, onder meer naar PDF, voor gebruik buiten het
systeem.
