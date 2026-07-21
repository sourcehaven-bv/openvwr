# Welkom

OpenVWR is het centrale platform waarmee organisaties alle verwerkingen van persoonsgegevens kunnen bijhouden, laten goedkeuren en publiceren. Dit document is de bijbehorende handleiding.

## Over OpenVWR

OpenVWR is een webapplicatie met formulieren die een eenduidige en gestructureerde invulling van het verwerkingsregister faciliteert. De belangrijkste functionaliteiten:

- Toegankelijk voor medewerkers van de gehele organisatie, inclusief onderliggende organisatieonderdelen.
- Importfunctionaliteit voor bestaande registers, met de mogelijkheid geïmporteerde gegevens handmatig aan te vullen.
- Koppeling van documenten aan een verwerking, waarbij deze documenten ook in het systeem opgeslagen worden.
- Een administratieportaal voor gebruikers en daaraan gerelateerde rollen en rechten.
- Online waarschuwingen bij (binnenkort) verlopen documenttermijnen, bijvoorbeeld van DPIA's.
- Registratie van algoritmes, als een bijzondere vorm van verwerkingen.
- Een datalekregister waar Privacy Officers datalekken kunnen registreren, exporteren en eventueel kunnen melden bij de Chief Privacy Officer.
- Vastlegging van relaties tussen entiteiten en (sub)verwerkingen, met geautomatiseerde voorgestelde veranderingen.
- Alerting, bijvoorbeeld een email wanneer een document zijn geldigheid gaat verliezen of wanneer een taak voor een gebruiker klaar staat.

## Details

Generieke informatie omtrent OpenVWR:

- website: [https://openvwr.nl/](https://openvwr.nl/)
- codebase: PHP, Laravel, Filament
- helpdesk: neem contact op met uw Chief Privacy Officer

## Inloggen

![Login pagina](./imgs/01_welkom/01_login.png)

Voor toegang tot OpenVWR zult u moeten beschikken over een account en een authenticator applicatie.

### Een account op OpenVWR

Voordat u in kunt loggen heeft u een account nodig: neem hiervoor contact op met uw Chief Privacy Officer.

### Een authenticator applicatie

De applicatie is beschermd met 2 Factor Authentication, ook wel bekend als One Time Password protection. Hiervoor heeft u een van de volgende apps nodig op uw mobiele device:

 1. Microsoft Authenticator: [Android App](https://play.google.com/store/apps/details?id=com.azure.authenticator&hl=nl&gl=US) / [iPhone App](https://apps.apple.com/nl/app/microsoft-authenticator/id983156458)
 2. Google Authenticator: [Android App](https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2) / [iPhone App](https://apps.apple.com/nl/app/google-authenticator/id388497605)
 3. FreeOTP Authenticator: [Website](https://freeotp.github.io/)
