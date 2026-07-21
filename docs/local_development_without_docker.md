# Lokale ontwikkeling zonder Docker

De standaard setup draait op Laravel Sail (Docker). Op machines waar Docker
traag of onbetrouwbaar is, kan de applicatie ook volledig native draaien. Dit is
met name handig voor het genereren van screenshots voor de handleiding.

De services die niet native beschikbaar zijn, hebben allebei een ingebouwde
uitweg: de virusscanner en de OTP-verificatie kennen een `fake` driver. Voor
bestandsopslag is helemaal geen vervanging nodig — de uploads- en exports-disks
zijn sinds het verwijderen van MinIO gewoon lokale schijven.

## Vereisten

| Onderdeel  | Versie | Opmerking                                              |
|------------|--------|--------------------------------------------------------|
| PHP        | 8.4    | `composer.json` vereist `^8.4`; 8.5 wordt niet geaccepteerd |
| Extensies  | —      | `pdo_pgsql`, `fileinfo`, `sockets`, `zip`              |
| PostgreSQL | 15     | Gelijk aan `postgres:15` uit docker-compose            |
| Node       | LTS    | Voor `npm run build`                                   |

Op macOS met Homebrew:

```bash
brew install php@8.4 node
# PostgreSQL: Postgres.app of `brew install postgresql@15`
```

Draai onderstaande commando's met de 8.4-binary als je systeem-PHP nieuwer is:

```bash
PHP=/opt/homebrew/opt/php@8.4/bin/php
```

## Setup

```bash
cd src/cms

# 1. Database + rol
psql -h 127.0.0.1 -d postgres -c "CREATE ROLE sail LOGIN PASSWORD 'password' SUPERUSER;"
psql -h 127.0.0.1 -d postgres -c "CREATE DATABASE openvwr_local OWNER sail;"

# 2. Environment
cp .env.nodocker.example .env
$PHP artisan key:generate   # vóór het seeden, zie waarschuwing hieronder

# 3. Dependencies en assets
$PHP /opt/homebrew/bin/composer install
npm install && npm run build          # zonder assets rendert de UI ongestyled

# 4. Database vullen
$PHP artisan migrate
$PHP artisan db:seed --class=TestDataSeeder

# 5. Starten
$PHP artisan serve --host=127.0.0.1 --port=8000
```

De applicatie draait nu op <http://127.0.0.1:8000>.

> **Let op:** `otp_secret` wordt versleuteld opgeslagen met `APP_KEY`. Draai je
> `key:generate` ná het seeden, dan zijn de bestaande secrets niet meer te
> ontsleutelen ("Could not decrypt the data") en mislukt het inloggen. Genereer
> de key dus vóór stap 4, of draai daarna opnieuw:
>
> ```bash
> $PHP artisan migrate:fresh && $PHP artisan db:seed --class=TestDataSeeder
> ```

## Wat wijkt af van de Docker-setup

| Sail-service | Lokaal alternatief          | Sleutel in `.env.nodocker.example` |
|--------------|-----------------------------|------------------------------------|
| `pgsql`      | Lokale PostgreSQL           | `DB_HOST=127.0.0.1`                |
| `clamav`     | Fake virusscanner           | `VIRUSSCANNER_DEFAULT=fake`        |
| `mailpit`    | Mail naar het logbestand    | `MAIL_MAILER=log`                  |
| `web` (nginx)| `php artisan serve`         | `APP_URL=http://127.0.0.1:8000`    |

Daarnaast staat `DEBUGBAR_ENABLED=false` (de debugbalk rendert over de UI heen
in screenshots) en `ONE_TIME_PASSWORD_DRIVER=fake`, waardoor elke OTP-code wordt
geaccepteerd. Zet die laatste op `timed` om de echte tweefactorauthenticatie te
testen.

## Inloggen

De applicatie kent geen wachtwoorden: inloggen gaat via een ondertekende
magic link. `MAIL_MAILER=log` logt alleen metadata, niet de link zelf. Genereer
daarom een login-URL via artisan:

```bash
$PHP artisan tinker --execute='
$u = App\Models\User::where("email", "admin@example.com")->firstOrFail();
app(App\Services\UserLoginToken\UserLoginService::class)->sendPasswordLessLoginLink($u, "/");
$t = $u->userLoginTokens()->orderByDesc("expires_at")->firstOrFail();
echo (new App\Mail\Authentication\PasswordLessLoginLink($t))->link;
'
```

Open de URL, klik op "Inloggen", en vul op het tweefactorscherm een willekeurige
code in (met de `fake` driver). `TestDataSeeder` maakt naast `admin@example.com`
voor elke rol een testgebruiker aan op `<rol>@example.com`.

## Beperkingen

Deze setup is bedoeld voor lokale ontwikkeling en het genereren van
screenshots, niet als vervanging van de Docker-omgeving:

- Versies van PHP en PostgreSQL kunnen afwijken van de containers, waardoor
  lokaal gedrag niet gegarandeerd gelijk is aan CI of productie.
- `QUEUE_CONNECTION=sync` verwerkt jobs synchroon. Zodra het project een echte
  queue-driver gebruikt, worden jobs lokaal niet meer verwerkt zonder worker.
- Virusscanning en objectopslag worden niet echt getest.

Draai de testsuite en CI daarom op de Docker-setup.
