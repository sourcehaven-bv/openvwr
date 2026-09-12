# OpenVWR achter de Pratique-proxy

Hoe je een OpenVWR-omgeving op de `pratique`-driver zet: de proxyconfiguratie,
de eis dat de app onbereikbaar is buiten de proxy om, en het inrichten van de
bestaande organisaties en gebruikers.

Alleen van toepassing op `AUTH_DRIVER=pratique`. Een omgeving op `builtin`
verandert niet.

## 1. Proxyconfiguratie

```yaml
upstream:
  # host:port, geen URL — geen schema, geen pad.
  target: "openvwr-app:9000"

  # MOET exact gelijk zijn aan PRATIQUE_AUDIENCE in de app. Dit is de
  # confused-deputy-guard: een assertion die voor een andere upstream is
  # uitgegeven, is geldig ondertekend maar niet voor ons bedoeld. Een verschil
  # hier is ook de meest voorkomende oorzaak van een eindeloze 403-lus.
  audience: "app://openvwr"

  mtls:
    enabled: true
    client_cert: "/etc/pratique/certs/proxy-client.pem"
    client_key: "/etc/pratique/certs/proxy-client-key.pem"
    ca_cert: "/etc/pratique/certs/ca.pem"
    server_name: "openvwr-app"

  # Paden die de app zonder authenticatie serveert. Ze bereiken de app zónder
  # identiteit, dus er mag niets gebruikersspecifieks achter zitten.
  public_paths:
    # Monitoring, dat geen sessie heeft en niets persoonlijks teruggeeft.
    - "/health"
    - "/up"
    # De webhook-ontvanger. Onvermijdelijk publiek — de proxy heeft geen sessie
    # als hij ons aanroept — en verifieert daarom zelf de ES256-handtekening
    # over de hele body. Zie docs/pratique_migration_plan.md §2a.
    - "/pratique/webhook"

rbac:
  # De rollen van deze applicatie, één op één. Pratiques eigen
  # owner/admin/member worden vervangen, niet aangevuld: twee parallelle
  # rollenstelsels naast elkaar is een bron van fouten.
  #
  # De permissies hieronder gelden ALLEEN voor Pratiques eigen beheerportaal.
  # Wie in OpenVWR wat mag, blijft staan in config/permissions.php.
  default_role: privacy-officer
  roles:
    chief-privacy-officer: ["*"]
    privacy-officer: ["members.*"]
    counselor: []
    data-protection-official: []
    input-processor: []
    input-processor-databreach: []
    mandate-holder: []

signup:
  # Tenancy is hier operator-gestuurd. Zelf een organisatie aanmaken zou een
  # lege parallelle tenant opleveren naast een echte.
  self_serve_orgs: false

webhooks:
  enabled: true
  endpoints:
    - url: "https://openvwr.example.nl/pratique/webhook"
      events:
        - "session.revoked"
        - "membership.updated"
        - "membership.deleted"
```

`functional-manager` staat er bewust niet bij: die rol is organisatie-overstijgend
en blijft in OpenVWR's eigen tabellen (migratieplan §3.2).

De bijbehorende app-instellingen staan in `.env.example` onder `AUTH_DRIVER`.

## 2. De app moet buiten de proxy om onbereikbaar zijn

Dit is geen aanbeveling maar de andere helft van het model. Wie de app direct op
poort 9000 kan bereiken, kan de assertion overslaan.

Drie lagen, in volgorde van belangrijkheid:

1. **Netwerk** — bind de app op een privé-interface; geen publieke route.
2. **mTLS** — met `upstream.mtls` aan presenteert de proxy een clientcertificaat.
   Termineer dat in nginx (`ssl_verify_client on`), niet in PHP.
3. **De applicatie zelf** — faalt sowieso dicht: zonder geldige assertion
   antwoordt `VerifyPratiqueAssertion` met 403, nooit met een redirect naar een
   loginpagina. Dat is de vangnetlaag, niet de eerste verdediging.

Controleer na uitrol dat een direct verzoek aan de app wordt geweigerd. Slaagt
het wél, dan is de isolatie stuk, ongeacht wat de proxy doet.

## 3. Bestaande organisaties en gebruikers inrichten

Pratique heeft geen bulk-import; het is één CLI-aanroep per rij. Genereer daarom
een plan uit de database:

```sh
# Lezen wat er is (verandert niets, veilig op productie):
php artisan pratique:export-provisioning > plan.json

# Of meteen een uitvoerbaar script:
php artisan pratique:export-provisioning --format=sh > provision.sh
```

Lees `plan.json` voordat je `provision.sh` draait. Het script is idempotent —
elke stap kijkt eerst of het al bestaat — dus na een afgebroken run kun je hem
opnieuw starten zonder te bedenken hoever hij kwam.

```sh
PRATIQUE=/usr/local/bin/pratique bash provision.sh
```

Wat het doet: organisaties aanmaken die nog niet bestaan, en per organisatie de
leden toevoegen met precies de rollen die ze in OpenVWR in díe organisatie
hebben. Gebruikers worden impliciet aangemaakt door `add-member`.

Wat het **niet** doet, en niet kan:

- **Inloggegevens overzetten.** Pratique kent geen wachtwoorden, en OpenVWR sinds
  2024 ook niet — inloggen ging via magic link + TOTP. Er gaat dus niets
  verloren, maar iedereen bootstrapt bij de eerste login met een gemailde code,
  en **bestaande TOTP-inschrijvingen vervallen**. Dat is een
  communicatie-onderwerp, niet alleen een technisch.
- **Globale rollen overzetten.** `functional-manager` blijft in OpenVWR.
- **Mail versturen.** Het script nodigt niemand uit; het richt alleen in.

Draai het eerst tegen een kopie van productiedata. Een fout in de slugs levert
lege parallelle tenants op naast de echte, en dat is achteraf lastig opruimen.

## 4. Na de omzetting controleren

- Een direct verzoek aan de app (dus buiten de proxy om) wordt geweigerd.
- Inloggen werkt, en na de login kom je op de pagina waar je heen wilde —
  inclusief query-parameters (dat vroeg een fix aan de proxykant, pratique #3).
- Elk van de zeven organisatierollen ziet wat hij hoort te zien. De
  autorisatielaag is niet meegemigreerd, dus dit zou ongewijzigd moeten werken.
- Een organisatie in de URL die niet bij de assertion hoort, geeft 403.
- Terugvallen kan met één configuratiewijziging: `AUTH_DRIVER=builtin`.
