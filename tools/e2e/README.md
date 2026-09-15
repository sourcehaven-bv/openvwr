# End-to-end: OpenVWR behind the Pratique proxy

Runs the whole chain locally and natively — no Docker.

```
Postgres :5432   already serving OpenVWR; Pratique gets its own database
Mailpit  :1025   SMTP for the login codes (web UI on :8025)
OpenVWR  :8000   php artisan serve, AUTH_DRIVER=pratique
Pratique :8080   the proxy — browse here
```

## Running it

```sh
mailpit --listen 127.0.0.1:8025 --smtp 127.0.0.1:1025 &   # if not already up
bash tools/e2e/run.sh
bash tools/e2e/stop.sh                                     # when done
```

`run.sh` builds the proxy, migrates, generates a signing key, provisions the
tenants and members straight out of OpenVWR's database, and starts both
processes. It is re-runnable: provisioning probes before it writes.

## Worth checking once it is up

- `http://localhost:8080/` redirects to login, then lands on the dashboard
- `http://localhost:8000/` answers **403** — the app failing closed without an
  assertion is the property the whole design rests on
- login codes arrive at `http://localhost:8025`
- **open a record and edit it.** Server-rendered pages are not enough: every
  interactive part of the panel goes through `POST /livewire/update`, which
  Livewire registers itself on the `web` group. A driver whose identity does not
  come from a session has to gate that route explicitly, and when it is missed
  the app looks perfectly healthy until you click something — lists and
  dashboards render, while modals, selects and form fields come back 404.
  Check the Network tab: every `/livewire/update` should be 200.

## ⚠️ It shares a Postgres server with the test suite

The suite runs in parallel and clones `testing` into `testing_test_1..10`. Those
clones are made once and reused, so anything that changes the schema out from
under them — a `migrate:fresh` while working on e2e, for instance — leaves some
workers on an old schema and others current.

The symptom is nasty: a **wandering** number of failures between runs (each run
hits a different mix of good and stale workers), in tests that pass perfectly in
isolation and in CI. It reads exactly like a bug in your own change.

If that happens, drop the clones and let them rebuild:

```sh
for n in $(seq 1 10); do
  psql -h localhost -p 5432 -U postgres -c "DROP DATABASE IF EXISTS testing_test_$n"
done
```

Then re-run the suite. Check `testing` itself is migrated first — the clones
inherit whatever it has.
