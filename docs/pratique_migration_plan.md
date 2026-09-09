# Plan: replace OpenVWR's auth with a Pratique proxy

Status: in progress. Phase 0 (decisions), 1a (strategy seam + dev driver, #92)
and 1b (the Pratique strategy) are done; Phase 2 onwards is still proposal.

## 1. What we have today

OpenVWR's auth is hand-rolled (no Fortify/Sanctum/Breeze, no `spatie/permission`). The
pieces:

| Concern | Where |
| --- | --- |
| Login (magic link) | `app/Http/Controllers/Authentication/PasswordlessLoginController.php`, `app/Filament/Pages/Login.php`, `routes/web.php` `/login/consume` |
| Second factor (TOTP) | `app/Services/OtpService.php`, `app/Http/Middleware/EnforceOneTimePassword.php`, `app/Filament/Pages/OneTimePasswordValidation.php`, `users.otp_secret` / `otp_confirmed_at` |
| Login tokens | `app/Services/UserLoginToken/UserLoginService.php`, `user_login_tokens` table, `app/Mail/Authentication/*` |
| Sign-by-link for mandate holders | `app/Http/Controllers/Authentication/SnapshotSignLoginController.php` |
| Session/guard config | `config/auth.php`, `config/session.php`, `app/Http/Kernel.php`, `app/Http/Middleware/Authenticate.php` |
| IP allowlist per org | `app/Http/Middleware/IPAllowFilter.php` (+ `organisations.allowed_ips`) |
| Tenancy | Filament panel: `->tenant(Organisation::class, 'slug', 'organisation')` in `app/Providers/FilamentServiceProvider.php`; `User::canAccessTenant()` / `getTenants()` |
| Roles/permissions | `app/Enums/Authorization/Role.php`, `config/permissions.php`, `user_global_roles` + `organisation_user_roles`, `app/Policies/*` |
| The seam | **`app/Services/AuthenticationService.php`** — exposes `user()`, `organisation()`, `principal()` behind the `Authentication` facade |

**The single most important fact for this migration:** the app almost never calls
`Auth::` directly. There are ~6 such call sites; ~50 files go through the
`Authentication` facade instead. So "who is the user / what org / what roles" has
exactly one implementation to swap. That is what makes this tractable.

Two structural details that are easy to miss and will cause bugs:

- **There are two parallel middleware stacks.** The Filament panel is mounted at
  `->path('/')` with its own `->middleware([...])` list, which *replaces* the
  `web` group from `app/Http/Kernel.php` rather than extending it. Anything we add
  must go in both places.
- **`AuthenticationService::principal()` memoises** into `?Principal $principal`.
  Once we're resolving identity per-request from a JWT, that cache must be keyed to
  the request (or dropped) or a stale role set can leak.

Also worth noting: there is **no password anywhere** — the `password` column was
dropped in 2024. Login is magic-link + mandatory TOTP. So we are not migrating
credentials; there are none to migrate.

## 2. What Pratique gives us

Pratique terminates the session and forwards to the app with a signed ES256 JWT in
`Authorization: Bearer`. Claims (`internal/core/core.go:231`):

```go
Issuer, Subject, Email, EmailVerified,
OrgID, OrgSlug, Roles []string,
Audience, IssuedAt, NotBefore, Expiry, JTI, PrincipalType
```

`OrgSlug` is the piece that makes this fit: Filament's tenancy is already
slug-keyed, so the assertion's org maps onto the existing `/{tenant}/...` URLs
without changing the URL scheme.

Two hard numbers: the assertion TTL is **9 minutes, hardcoded**
(`internal/signer/signer.go:21`), and the reference verifier applies **zero clock
skew leeway**. NTP sync between the proxy host and the app host is a hard
requirement, not a nicety — though our PHP verifier should allow a small leeway
anyway (see Phase 1).

Pratique owns (`internal/web/web.go:504`) exactly: `/__pratique/*`,
`/.well-known/pratique/*`, `/.well-known/oauth-authorization-server`, `/healthz`,
`/readyz`. Everything else is forwarded. Note the real auth routes are
*prefixed* — `/__pratique/auth/login`, not `/login`; the README's shorthand is
misleading. Provisioning is via portal, invites, SCIM, or
`pratique admin create-org` / `add-member`.

## 3. Constraints that shape everything

These are the reason this is not a pure delete-and-wire job. All were verified in
the source, not assumed.

### 3.1 Unauthenticated paths — supported (corrected)

**This section previously said no public-path allowlist existed. That is no longer
true**, and the correction matters because the original claim drove a decision
below. Pratique now ships *two* mechanisms, both verified in the source rather
than the docs (`docs/05-mvp-plan.md:383` still lists this as unbuilt open item
#4 — the docs lag the code).

The hot path (`internal/proxy/proxy.go`, `handle`) is now:

1. `IsOwned` — Pratique's own routes, served locally.
2. **`upstream.public_paths`** — bypasses authentication *entirely*, before any
   credential is examined. Inbound trusted headers are still stripped, so the
   request arrives upstream genuinely anonymous. Every caller is anonymous,
   always.
3. token credentials (OAuth bearer / PAT), then the session cookie.
4. **`upstream.anonymous_paths`** — evaluated *after* every credential class has
   failed. The request is forwarded with an `X-Pratique-Anonymous` marker instead
   of an assertion, so the app decides what to serve. Dual-mode: an authenticated
   caller on the same path still gets a full assertion and every session gate.

Matching is exact, or a prefix with a trailing `/*`, for both. Wired end to end:
`config.UpstreamConfig.IsPublicPath` → `app.go:296` → `proxy.go:275`.

Which to reach for:

| Need | Use |
| --- | --- |
| An endpoint specified to answer *before* credentials exist (RFC 6764 discovery) | `public_paths` |
| A path served to both anonymous and signed-in users, or where the app owns the challenge | `anonymous_paths` |
| An OAuth-protected API path needing an RFC 9728 challenge | `protected_resources` |

**Scope, and the trap:** both exempt a path from *authentication only*. The
request reaches the app with **no identity**, so the app must do its own
authorization there. Pratique cannot help — with no org there is nothing for
suspension or tenant scoping to act on. A path listed here that then serves
user-specific data is a hole the proxy cannot close.

Consequence for us: pre-auth static assets and any future inbound webhook are
solvable with config rather than by routing around the proxy.

**Decided: the mandate-holder signed-link flow is still dropped — but now by
choice, not by force.** The earlier reasoning leaned partly on "the proxy cannot
serve it anyway", which §3.1 shows is no longer true: `/snapshot/sign/*` could be
listed in `anonymous_paths` and kept.

It should still go, on its own merits. The existing `SnapshotSignLoginController`
discloses the target user's name to anyone holding the signed URL
(`'user' => $user` in the view), bypasses `Login::authenticate()`'s rate limiting
and its "user must have ≥1 organisation" check, and logs an
`AuthenticationSuccessEvent` on a mere page view. Deleting it is a net security
improvement independent of this migration.

And note what preserving it would now require: an `anonymous_paths` entry reaches
the app with **no identity**, so the app would have to do its own authorization
for that route — which is exactly the hand-rolled, easy-to-get-wrong code the
migration is meant to remove. Keeping it is possible; it is not attractive.

So the flow becomes: mandate holder clicks the notification link → Pratique login
→ lands on the approval page. Which requires post-login redirect, and Pratique
does support it — verified, see §3.1.1.

#### 3.1.1 Post-login redirect: supported (query-string gap fixed upstream)

The proxy encodes the originally-requested target as an `rd` parameter
(`internal/app/app.go`, `loginRedirect`). `rd` is threaded through every stage of
the login funnel — email entry, code verification, tenant selection,
SSO start/callback, passkey login, and the reproof gate — carried in hidden form
fields between steps. Every read goes through `safeRedirect`
(`internal/web/auth_handlers.go`), which is properly hardened against open
redirect: it rejects backslashes and CRLF before parsing, then rejects absolute
URLs, any `Host`, any userinfo, and non-`/`-prefixed paths, collapsing anything
suspicious to `/`.

**The query-string gap is fixed and merged** — pratique PR #3
(`fix/login-redirect-preserve-query`), now on `develop`. `loginRedirect` had used
`r.URL.EscapedPath()`, which dropped the query from every bounced request, so
`/{tenant}/snapshots/{id}?activeTab=unreviewed` came back as the bare path. It
now carries path *and* query, escaped as a single parameter value.

Verifying that fix surfaced three more instances of the same class of bug —
`rd` (and an invited email) interpolated into URLs by raw concatenation, so a
target's own `&` split the parameter and dropped the remainder. They were latent
while `rd` was path-only and became reachable once the query was preserved, so
they were fixed in the same PR: the select-tenant hop, the passkey reproof
redirect, and invite accept.

**Consequence for us: no workaround needed, and no outstanding dependency.**
Deep links survive login intact, including tab/filter state. PR #3 has landed, so
the Phase 2 note about waiting for it is discharged.

### 3.2 Two tenancy systems must agree

Filament resolves the tenant from the **URL** (`/{tenant}/...`); Pratique resolves
it from the **assertion**. If a user is in org A per the JWT and browses to
`/org-b/...`, that must be a hard failure, not a silent switch. The bridge
middleware has to assert `route('tenant') == claims.org_slug` and reject
otherwise. Filament's tenant menu must be replaced by Pratique's switcher widget,
or the two will disagree about what "current org" means.

Related: OpenVWR has **global** roles (`FUNCTIONAL_MANAGER`, cross-org) but
Pratique's `roles` claim is scoped to the active org. **Decided: keep
`user_global_roles` app-side** and take only org roles from the assertion.

The deciding evidence: global roles are assigned in exactly one place —
`app/Console/Commands/UserCreateAdmin.php:168`, via the `user:create-admin` CLI.
There is no UI path. So `FUNCTIONAL_MANAGER` is a rare, deliberately
operator-assigned role held by a handful of people, not something needing
per-tenant lifecycle management in Pratique. Mirroring it into every org's role
list would be strictly more machinery for strictly less clarity.

Concretely, `AuthenticationService::principal()` keeps its current shape —
global roles from the local table, org roles from the assertion instead of from
`organisationRoles()`. The union semantics are unchanged, so
`AuthorizationService` and all 14 policies are untouched.

### 3.3 The session cookie and the user FK

Two smaller landmines, both worth knowing before Phase 1 rather than during it:

- `config/session.php` sets the cookie to `__HOST-verwerkingsregister_session`
  with `same_site: 'strict'` and a 15-minute lifetime. `__HOST-` requires
  `Secure`, `Path=/`, no `Domain`. Since Pratique fronts everything on one origin
  this should hold, but `SameSite=Strict` is exactly what breaks redirect-back
  flows from an external login — and our login is now external. Expect to relax
  this to `Lax` (Pratique's own cookie is `Lax`).
- `sessions.user_id` has a **real FK to `users`** (non-standard; stock Laravel
  leaves it unconstrained). Any principal that gets a Laravel session must already
  exist in the local `users` table — which is why JIT provisioning in Phase 1 has
  to happen *before* the session is written, not lazily.

## 4. Target architecture

**Selectable auth strategies, not a one-way rip-out.** OpenVWR keeps its built-in
auth (magic-link mail + TOTP) as one strategy and gains Pratique as another; a
config value picks which is active. A third, `dev`, is a non-production user
picker (§ Phase 1a). This supersedes the delete-everything framing of earlier
drafts.

`AUTH_DRIVER`: `builtin` (default) · `pratique` · `dev` (non-production only).

```
AUTH_DRIVER=builtin                     AUTH_DRIVER=pratique
────────────────────                    ────────────────────
browser → Laravel                       browser → Pratique (:443)
  Filament Login page                     owns /__pratique/*, session cookie
  magic link + TOTP                            │ Authorization: Bearer <ES256>
  session guard                                ↓
                                          Laravel (:9000, no login routes,
                                          verifies assertion, trusts nothing else)
```

Either way the app above the seam is identical: `Authentication::user()` /
`::organisation()` / `::principal()` answer the same questions, so the ~50
consuming files, `AuthorizationService`, and all 14 policies never learn which
strategy is running.

Why this is the better shape:

- **De-risks cutover.** The strategy is a config flip, so rollback is a config
  flip too — not a revert-and-redeploy. Both paths can run in different
  environments simultaneously (builtin in dev/CI, pratique in staging).
- **Keeps the test suite runnable.** ~380 feature tests authenticate through the
  session guard. Under builtin they keep working untouched; only the
  Pratique-strategy tests need the new assertion-minting helper. That removes the
  single largest risk in the old Phase 4.
- **Keeps TOTP as a real option.** It stops being "a control we drop"
  (§ Phase 0.2) and becomes "a control the builtin strategy still offers." If
  Pratique's passkey-only second factor turns out not to satisfy a compliance
  requirement, that's no longer a blocker — it's a reason to keep some
  deployments on builtin.
- **Local dev doesn't require Pratique + Postgres + SMTP** just to log in.

Cost: OpenVWR carries two auth paths instead of zero. That is a real maintenance
burden, and worth accepting only because the builtin path already exists and is
already tested — this is *retaining* working code behind an interface, not
writing new code. If Pratique proves out everywhere, deleting the builtin
strategy later is a clean, isolated change.

Laravel keeps its `users` / `organisations` / `organisation_user` /
`organisation_user_roles` tables under both strategies — they stay the app's own
record of who exists. Under `pratique` they're reconciled from the assertion on
each request (just-in-time provisioning); under `builtin` they're authoritative,
exactly as today. The app's domain data (snapshot approvals, audit log,
mandate-holder relations) is keyed to its own user rows either way.

### 4.1 The seam

`AuthenticationService` becomes an interface with two implementations:

| | `builtin` | `pratique` | `dev` |
| --- | --- | --- | --- |
| `user()` | `Filament::auth()->user()` (as today) | resolve from verified assertion `sub` | `Filament::auth()->user()` |
| `organisation()` | `Filament::getTenant()` (as today) | `Filament::getTenant()`, asserted to equal `org_slug` | `Filament::getTenant()` |
| `principal()` | global + org roles from DB (as today) | global roles from DB, org roles from claim | same as `builtin` |

Strategy-specific wiring, all of it concentrated in `FilamentServiceProvider`:

| Wiring point | `builtin` | `pratique` | `dev` |
| --- | --- | --- | --- |
| `->login()` | `Login::class` | omitted (Pratique owns login) | `DevLogin::class` (user picker) |
| `->authMiddleware()` | `Authenticate` + `EnforceOneTimePassword` | `VerifyPratiqueAssertion` | `Authenticate` only |
| `->tenantMenu()` | Filament's own | Pratique widget | Filament's own |
| `->profile()` | `Profile::class` (incl. OTP enrolment) | `Profile` minus the OTP component | `Profile` minus OTP |
| `routes/web.php` | `/login/consume` etc. registered | not registered | not registered |

Note `dev` shares `builtin`'s session-guard resolution and differs only in how a
session is *established* — which is exactly the point: it exercises the seam
without duplicating the logic under test.

Everything else — resources, policies, tenancy, `config/permissions.php` — is
shared and untouched.

**The one thing that must not be strategy-dependent:** failure mode. Under
`pratique`, a missing or invalid assertion must fail closed (403), never fall
back to the builtin login page. A misconfigured `AUTH_DRIVER` that silently
degrades to session auth behind a proxy would be a serious hole, so the driver
resolves once at boot and an unknown value is a hard startup error.

## 5. Work plan

### Phase 0 — decide

1. ~~Resolve §3.1 (mandate-holder links).~~ **Done.** Signed-link flow is being
   dropped (security liability — see §3.1); we rely on Pratique's post-login
   `rd` redirect. The query-string gap that made this lossy is fixed in
   pratique PR #3, which has since merged (§3.1.1).
2. ~~Decide the fate of mandatory TOTP.~~ **Decided: TOTP survives as part of the
   builtin strategy** (§4). Superseded an earlier decision to delete it outright.

   Under `AUTH_DRIVER=pratique`, second-factor policy is Pratique's concern —
   passkeys today, per-org step-up via `required_amr` / `pratique admin
   set-stepup`. Under `AUTH_DRIVER=builtin`, today's mandatory TOTP is unchanged.
   OpenVWR no longer has to pick one and lose the other, which is what made this
   the hard decision in the first place.

   Two consequences, both now smaller than they were:
   - **The user manual states 2FA as a requirement.**
     `docs/handleiding/01_welkom.md:41` says *"De applicatie is beschermd met 2
     Factor Authentication"* and walks users through installing Microsoft/Google/
     FreeOTP Authenticator. Still accurate under `builtin`; needs a
     driver-dependent rewrite for `pratique` deployments. Documentation task.
   - **TOTP enrolments don't transfer** to a Pratique deployment — there's
     nowhere to put them, so users re-bootstrap on first login there. But an
     environment staying on `builtin` keeps its enrolments intact, so this is a
     per-deployment cutover note rather than a one-way loss (§7).
3. ~~Decide global-role handling (§3.2).~~ **Decided: keep app-side** — see §3.2
   for the reasoning.
4. ~~Decide the role vocabulary.~~ **Decided: 1:1 mapping, replacing Pratique's
   defaults.** The `rbac:` block becomes exactly OpenVWR's 7 org roles, using the
   existing enum values verbatim (`app/Enums/Authorization/Role.php`):

   ```yaml
   rbac:
     default_role: privacy-officer   # only reachable via self-serve; we disable that
     roles:
       chief-privacy-officer:      ["*"]
       privacy-officer:            ["members.*"]
       counselor:                  []
       data-protection-official:   []
       input-processor:            []
       input-processor-databreach: []
       mandate-holder:             []
   ```

   `functional-manager` is deliberately absent — it's the global role staying
   app-side (§3.2). Pratique's `owner`/`admin`/`member` are replaced, not
   augmented, so there is one role vocabulary rather than two.

   The permission lists above gate **only Pratique's own admin portal** — who may
   invite members and edit org settings — which is why they don't mirror
   `config/permissions.php`. OpenVWR keeps interpreting role→permission exactly as
   today; the authorization layer genuinely doesn't move. Mapping CPO→`["*"]` and
   PO→`["members.*"]` mirrors the existing `USER_ROLE_ORGANISATION_*_MANAGE`
   permissions, so tenant-admin capability lands where it already sits.
5. ~~Decide the fate of per-org `allowed_ips`.~~ **Decided: keep it in Laravel.**
   It's a live, admin-managed feature (editable per org in `OrganisationResource`,
   gated by `ORGANISATION_UPDATE_IP_WHITELIST`, with a global default from
   `IP_WHITELIST_DEFAULTS`), and Pratique has no per-org IP restriction to move it
   to. Keeping it costs nothing: `IPAllowFilter` is tenant middleware and survives
   as-is.

   **The one thing to get right:** behind the proxy, `$request->ip()` becomes the
   proxy's address unless `TrustProxies` is configured for it. Left unset, the
   allowlist silently either blocks everyone or admits everyone — both bad, and
   neither loud. Wire `TrustProxies` in Phase 2 and cover it with a test that
   asserts a request forwarded from the proxy sees the *client* IP.

   Accept the weakening this implies: the check now runs after Pratique has
   already authenticated the user, so it is defence-in-depth rather than a
   perimeter. If it needs to be a true perimeter, it belongs at the ingress in
   front of Pratique, not in either app.

### Phase 1a — extract the strategy interface + a dev strategy

Do this first and merge it on its own. It touches a lot of wiring but changes
nothing observable in production, so it's cheap to review and cheap to revert.

1. Introduce `AuthenticationStrategy` with `user()` / `organisation()` /
   `principal()`; move today's logic into `BuiltinAuthStrategy` verbatim.
2. Bind the strategy in `AuthServiceProvider` from `config('auth.driver')`
   (`AUTH_DRIVER`, default `builtin`). Unknown value ⇒ hard startup error.
3. `AuthenticationService` delegates to the bound strategy. **The `Authentication`
   facade signature does not change**, so the ~50 consuming files stay untouched.
4. Move the strategy-specific panel wiring (§4.1 table) behind driver checks.
5. **Ship a third `dev` strategy: a user picker.** A select of existing users
   plus a "log in" button, no credentials at all.

**Why the dev strategy earns its place.** An interface with a single
implementation is not an abstraction — it's indirection that happens to compile.
Every leak (a stray `Filament::auth()`, an assumption that login is a Laravel
route, a helper reaching past the facade) stays invisible until the second
implementation arrives. Building `dev` in 1a means the seam is *proven* before
the security-critical Pratique strategy is written against it — and when 1b
starts, the questions are already answered.

It also pays off immediately and permanently:
- local dev and CI stop needing mail + a TOTP app to reach a logged-in page
- switching between the 8 roles becomes a dropdown, which makes the role matrix
  in Phase 4 genuinely testable by hand
- it's the natural harness for the parity tests (same fixture, three drivers,
  identical `principal()`)

**It must be impossible to enable in production.** This is an auth bypass by
design, so treat it as one:
- refuse to boot if `AUTH_DRIVER=dev` and `APP_ENV=production` — a hard
  exception in the provider, not a log line
- keep it out of the production image where practical (`require-dev` autoload
  path, or a provider registered only in non-production)
- add a test asserting the boot guard fires — the guard is the whole safety
  argument, so it needs to be the best-tested line in the phase

Acceptance criteria for 1a:
- the full suite passes unchanged under `builtin` (if a test needed editing, the
  extraction leaked)
- the app is fully usable under `dev`
- a parity test shows `builtin` and `dev` agree on `user()` / `organisation()` /
  `principal()` for the same fixture user

### Phase 1b — build the Pratique strategy ✅ done

The only genuinely new code, and the security boundary — it deserves the most care.

Shipped as `PratiqueAssertion(Verifier|Exception)`, `JwksProvider`,
`PratiqueIdentityResolver`, `PratiqueContext`, `PratiqueAuthenticationStrategy`
and the `VerifyPratiqueAssertion` middleware, plus a `users.pratique_subject`
column. Step 4 below turned out to be unnecessary: the 1a seam already routes the
facade through the bound strategy, so nothing about `AuthenticationService`
changed.

Decisions taken while building, worth carrying into Phase 2:

- **Users are matched on `sub`, never email.** Email is mutable in the proxy, so
  matching on it would split one person across rows after a change of address.
- **Organisations are never auto-created.** A tenant here owns registers,
  numbering sequences and a published website; a typo'd slug in the proxy must not
  quietly produce an empty parallel tenant. Unknown org ⇒ 403.
- **Only `principal_type: user` is accepted.** Service accounts and PATs can hold
  valid assertions for this audience, but every policy here is written around a
  person.
- **Unknown role strings are ignored, not fatal** — the proxy's catalogue is
  edited independently; a new role there must not lock out a tenant until this app
  redeploys.
- **`PratiqueContext` is request-scoped, not a singleton** — the direct lesson
  from the cross-tenant leak found in the 1a review.
- Missing `issuer`/`audience`/`jwks_url` is a hard failure, never a default.

1. Add a JWT lib (`web-token/jwt-framework` or `firebase/php-jwt` + JWKS caching).
2. `app/Http/Middleware/VerifyPratiqueAssertion.php`:
   - read `Authorization: Bearer`; **fail closed** if absent (503/403, never "log in")
   - verify ES256 against cached JWKS from `/.well-known/pratique/jwks.json`
   - verify `iss`, strict `aud` (must equal our configured audience), `exp`/`nbf`.
     Getting strict-`aud` wrong is the documented #1 cause of 401 loops — test it
     explicitly.
   - **clock skew:** Pratique's own Go verifier sets zero leeway, but it runs
     beside the proxy; Laravel is a separate host. Allow a small leeway
     (30–60s) on `exp`/`nbf` — negligible against a 9-minute TTL, and it avoids
     hard 403s on otherwise-valid sessions from minor drift. NTP on the app host
     stays a documented deployment requirement regardless.
   - cache the JWKS with a TTL + rotation-tolerant refresh (Pratique rotates keys
     on a schedule — a hard-pinned key will break in production)
3. `app/Services/PratiqueIdentity.php` — resolve claims → `User` + `Organisation`,
   JIT-creating/updating rows by `sub` (add `users.pratique_subject`, unique) and
   syncing org membership + org roles from the claim.
4. Rewrite `AuthenticationService` to read from the verified assertion instead of
   `Filament::auth()` / `Filament::getTenant()`. **Keep the facade's signature
   identical** — this is what keeps the ~50 consuming files untouched.
5. Enforce `route('tenant') === claims.org_slug` (§3.2).

Ship this behind a feature flag with the old auth still present, so both paths can
be exercised.

### Phase 2 — proxy + provisioning

1. `pratique.yaml`: `upstream.target` → the Laravel container, `upstream.audience`
   → e.g. `app://openvwr`, `rbac.roles` → the 8 OpenVWR roles, `signup.self_serve_orgs: false`
   (tenancy here is operator-controlled, not SaaS signup).
2. Enable mTLS proxy→upstream, and make Laravel reject any request that didn't come
   through the proxy (else the assertion is bypassable by hitting :9000 directly).
   This is not optional — it's the fail-closed half of the model.
3. Write a one-shot migration script: for each `organisation` → `pratique admin
   create-org`; for each `organisation_user` → `pratique admin add-member --roles`.
   There is no bulk-import command, so this is a script over the CLI, one
   invocation per row. Caveats that shape the script:
   - the `admin` commands are **create-only and not idempotent** (`create-org`
     fails on duplicate slug), so wrap each in a probe — `admin get-org` exits
     **4** on not-found, which is the sanctioned pattern in
     `docs/10-operations.md §11.1`.
   - **no credentials migrate.** Pratique has no password concept at all; today's
     OpenVWR users have no password either (it's magic-link + TOTP), so nothing is
     lost — but every user re-bootstraps via email code on first login, and their
     **TOTP enrolment is gone**. If 2FA is a compliance requirement, passkeys are
     the replacement and that's a user-communication task, not just a code task.
4. Verify against a copy of production data before touching anything real.

### Phase 2a — webhook receiver (optional, strictly additive)

**The governing rule: a missed webhook must never become a security issue.**

Everything a webhook would tell us is already reconciled on the next request by
`PratiqueIdentityResolver` — the user row, the membership, and the roles held in
the active organisation, added *and withdrawn*. That reconciliation runs before
any policy is consulted, so authorization is correct on every request whether or
not a webhook ever arrived.

Webhooks therefore only ever make something happen **sooner**. Nothing may
depend on one arriving. Concretely, that means:

- **A webhook may never be the only path to a security-relevant state change.**
  If the receiver is down for a day, the system is still correct — just less
  prompt.
- **The receiver is idempotent and order-independent.** Delivery is at-least-once
  and retried (`webhooks.max_attempts`), so a duplicate or out-of-order event has
  to be a no-op, not a correction.
- **A webhook may narrow access, never widen it.** Dropping a role early is safe
  if the event is spurious — the next request restores it from the assertion.
  *Granting* one early on a forged or replayed event would be an escalation with
  no counterweight, so the receiver simply does not grant.
- **Never trust the payload's contents.** Events carry IDs only (see below); the
  receiver re-reads authoritative state rather than believing the body.

Two things genuinely worth having, because request-time reconciliation
structurally cannot do them — both are *liveness*, not *authorization*:

| Event | Why it can't wait for the next request |
| --- | --- |
| `session.revoked` | A forced logout cannot tear down an already-open WebSocket/SSE connection: no request arrives to re-check. Payload is user-scoped (`user_id` + `reason`, no `org_id`) — key the teardown on `user_id`. Relevant if Laravel Reverb/Echo is ever used; today OpenVWR has no long-lived connections, so this is a *future* need, not a current one. |
| `membership.deleted` / `membership.updated` | A withdrawn role persists in OpenVWR's tables until that user's next request. Authorization is unaffected, but the stale row is visible to anything reading the database directly — a scheduled export, a report, an admin listing. |

What the events do **not** give us, verified in the Pratique source rather than
its docs:

- **There are no `user.*` events at all.** The catalogue is `organization.*`,
  `membership.*`, `invitation.*`, `service_account.*` and `session.revoked`
  (`internal/core/core.go:399-421`). A receiver cannot learn that a person was
  created or that their email changed.
- **Payloads carry identifiers only** — `membership.created` is
  `{membership_id, user_id}`, with no email, name or slug. Anything useful means
  calling back into `/api/v1/members`. That alone disqualifies webhooks as a
  provisioning path.
- **SCIM provisioning emits nothing.** `internal/app/scim.go` contains zero emit
  calls, so on an enterprise deployment where the IdP pushes the roster, no
  lifecycle webhook fires at all. A design that leaned on webhooks would silently
  do nothing on exactly the deployments with the most users.

Given all three, **provisioning stays where it is** — in the request path — and
this phase is optional. Skipping it costs nothing today, since OpenVWR holds no
long-lived connections. Revisit when Reverb lands, or if stale role rows in
reports become a real complaint.

If it is built: verify the signature (Pratique signs with the same ES256 key,
so `JwksProvider` is reusable), and note the endpoint needs an
`upstream.public_paths` entry (§3.1) since an inbound webhook carries no session.
Being a public path, it must authenticate the *signature* itself — that is the
one place where "the app does its own authorization" is unavoidable.

### Phase 3 — confine the builtin strategy (was: rip out the old auth)

Under the two-strategy design nothing is deleted. Instead the builtin auth code
is **confined behind the strategy boundary** so it is inert when
`AUTH_DRIVER=pratique`, and its files move under a namespace that makes the
grouping obvious (e.g. `app/Auth/Builtin/`).

Files that become builtin-only — magic-link layer:
- `app/Http/Controllers/Authentication/PasswordlessLoginController.php` (+ exception)
- `app/Services/UserLoginToken/`, `app/Mail/Authentication/`,
  `app/Models/UserLoginToken.php`, `app/Collections/UserLoginTokenCollection.php`,
  `database/factories/UserLoginTokenFactory.php`, `user_login_tokens` table
- `app/Filament/Pages/Login.php`; `->login()` registered only under `builtin`
- `/login/consume` route + `resources/views/auth/consume.blade.php`
- `app/Console/Commands/DevLoginLink.php`, `UserDeleteExpiredLoginTokens.php`

Files that become builtin-only — OTP layer:
- `app/Services/OtpService.php`, `app/Facades/Otp.php`,
  `app/Services/OneTimePassword/`, `app/ValueObjects/OneTimePassword/`
- `app/Http/Middleware/EnforceOneTimePassword.php`,
  `app/Filament/Pages/OneTimePasswordValidation.php`,
  `app/Livewire/User/Profile/OneTimePassword.php`,
  `app/Filament/Actions/OneTimePassword/*`
- `app/Console/Commands/UserDisableOtp.php`,
  `/{tenant}/two-factor-authentication`, the `auth.one_time_password` config
- ⚠️ the `fake` OTP driver (`ONE_TIME_PASSWORD_DRIVER=fake`) disables
  verification entirely. It must be impossible to enable outside local/CI —
  under `AUTH_DRIVER=pratique` it is unreachable, but add a guard that refuses
  to boot with `fake` when `APP_ENV=production`.

**Genuinely deleted — the signed-link flow.** This is the one thing that goes
away under *both* strategies, because it's a security liability in its own right
(§3.1), not because Pratique replaces it:
- `app/Http/Controllers/Authentication/SnapshotSignLoginController.php`,
  `resources/views/auth/snapshot_sign.blade.php`, the `/snapshot/sign/*` routes,
  the `SnapshotSignLoginLink` mailable, and the four `SNAPSHOT_SIGN_LOGIN_*`
  cases in `app/Enums/RouteName.php`.
- The two mailables that build those links get repointed at the plain approval
  URL: `app/Mail/SnapshotApproval/BatchSignRequest.php:32` and
  `SingleSignRequest.php:31` — `URL::signedRoute(...)` becomes an ordinary
  `Resource::getUrl()`. Under `builtin` the user hits the normal login page;
  under `pratique`, Pratique's login + `rd` redirect (§3.1.1).

**Deleted — genuinely dead code**, unrelated to either strategy:
- `config/auth.php` `passwords` block (no `password_reset_tokens` table, no
  reset route, no controller)
- `MustVerifyEmail` on the `User` model — `email_verified_at` was dropped in
  2024, so the interface is currently a lie
- the unused `verified` / `password.confirm` aliases, `config/hashing.php`

**Schema stays put.** `users.otp_secret` / `otp_confirmed_at` /
`otp_recovery_codes` and `user_login_tokens` are *retained*, not dropped —
they're live data for the builtin strategy. This removes the whole
"drop columns in a later release so rollback doesn't lose data" dance from the
old plan, and with it the `sql-generate` + hosting-changelog work
(`DEPLOY_PROCEDURE.md`) that came with it.

Keep (unchanged under both strategies): `users`, `organisations`, roles tables,
all 14 policies, `config/permissions.php`, `AuthorizationService`. Authorization
is unaffected — only authentication varies.

Under `pratique`, `->tenantMenu()` is replaced by Pratique's widget, which needs
a CSP allowance — see `config/csp.php` / `app/Http/CspPolicy.php`.

### Phase 4 — tests & cutover

27 of the 398 test files are auth-related, and they split cleanly:

**Keep and keep passing (8, unchanged):** `DevLoginLinkTest`,
`UserDeleteExpiredLoginTokensTest`, `UserDisableOtpTest`,
`Filament/Pages/LoginTest`, `Livewire/User/Profile/OneTimePasswordTest`,
`PasswordLessLoginLinkTest`, `OtpServiceTest`, `UserLoginServiceTest`. These
cover the builtin strategy, which still ships — under the old
delete-everything plan they'd have been thrown away. They should run with
`AUTH_DRIVER=builtin` pinned explicitly rather than relying on the default.

**Delete (1):** `SnapshotSignLoginControllerTest` — the only flow removed
outright (§3.1).

**Must keep passing — the authz contract, strategy-independent (11):**
`AuthenticationServiceTest`, `AuthorizationServiceTest`, `AuthenticationFacadeTest`,
`AuthorizationFacadeTest`, `BasePolicyTest`, `TenantScopeTest`,
`DashboardTenantIsolationTest`, `RedirectToTenantControllerTest`,
`UserOrganisationRoleTest`, plus helpers `PermissionTestHelper` and
`SessionTestHelper`.

That third list is the real regression suite — if it stays green under *both*
drivers, the semantics survived.

**The test-helper problem largely dissolves.** Under the old plan
`SessionTestHelper` had to be rewritten to mint fake assertions, and every one of
the ~380 feature tests depended on it — the single largest risk in the whole
migration. Now the default driver stays `builtin`, so that helper and every test
using it keep working untouched. What's needed instead is an *additional*
helper that mints a signed assertion, used only by the Pratique-strategy tests.
Additive, not a rewrite.

New tests to write:
- Strategy parity: a matrix asserting `user()` / `organisation()` / `principal()`
  agree across all three drivers for the same fixture data. This is what actually
  guarantees the seam holds. The `builtin`↔`dev` half of it already exists from
  Phase 1a, so adding `pratique` is an extra column rather than new scaffolding.
- Negative assertion tests: no assertion, expired, wrong `aud`, wrong signing
  key, tenant-slug mismatch, direct-to-upstream bypass.
- **Fail-closed test:** with `AUTH_DRIVER=pratique` and no assertion, the app
  must 403 — never render or redirect to the builtin login page (§4).
- Boot guard: unknown `AUTH_DRIVER` is a startup error; `fake` OTP driver refuses
  to boot in production.

CI should run the suite under both drivers. That roughly doubles the auth-test
matrix but is the only way the builtin path stays honest once attention moves to
Pratique.

Cutover: staging with production-shaped data → verify each of the 8 roles →
flip `AUTH_DRIVER` per environment. Rollback is a config flip, not a redeploy.

## 6. Pre-existing bug found while surveying

Unrelated to this migration, and now **more** worth fixing rather than less: under
the two-strategy design this code ships indefinitely as the builtin path, so it
can no longer be waved off as "about to be deleted" —
`app/Http/Controllers/Authentication/PasswordlessLoginController.php:68`:

```php
$user->userLoginTokens()->truncate();
```

`truncate()` on a `HasMany` builder ignores the relation constraint and **truncates
the whole `user_login_tokens` table**, invalidating every other user's in-flight
login link. It should be `->delete()`. Low impact (users just re-request a link)
but it's a live correctness bug affecting all users on every login. Note also
there is no `PasswordlessLoginControllerTest` — the primary login-consumption path
is only covered indirectly.

## 7. Loose ends worth flagging now

- **Per-org IP allowlist** (`IPAllowFilter`, `organisations.allowed_ips`): this is
  arguably a proxy-layer concern now, but Pratique has no per-org IP restriction.
  Keeping it in Laravel is fine and is the low-effort answer — but note it then
  applies *after* Pratique has already authenticated the user, which is a weaker
  position than today. Worth a conscious decision.
- **Audit logging**: `AuthenticationSuccessEvent` / `AuthenticationFailedEvent` will
  stop firing — login now happens in Pratique. Pratique has its own audit log
  (`pratique audit list`). If OpenVWR's audit trail is a compliance artifact (likely,
  given this is a GDPR processing register), split-brain audit is a real problem and
  needs an explicit answer.
- **No PHP middleware exists in Pratique.** `examples/` has Go only; `docs/04-architecture.md:145`
  names Laravel as supported *by spec*, via a stock JOSE library. We're writing the
  first PHP verifier — budget for it, and consider contributing it back to
  `pratique/examples/`.
- **`email_verified_at`**: the assertion carries `email_verified`; `MustVerifyEmail`
  on the User model becomes redundant.
- **SMTP becomes load-bearing on cutover day** (`pratique` driver only; under
  `builtin` nothing changes). Every migrated user's first login
  is an emailed 8-digit code, with no "old credential still works" grace period.
  Verify Pratique's SMTP path end-to-end *before* cutover, not during.
- **`/health` and `/up`**: `FilamentServiceProvider` registers these via
  `->routes()`, so they sit behind the proxy. List them in
  `upstream.public_paths` (§3.1) and existing monitoring keeps working unchanged
  — they serve no user-specific data, which is exactly the condition that makes a
  public path safe. Pratique's own `/healthz` + `/readyz` cover the proxy itself.
- **SQL generator**: `app/Console/Commands/SqlGenerate.php` /
  `app/Services/SqlExport/` emit the versioned schema files. Dropping OTP columns
  and `user_login_tokens` must go through that pipeline, per `DEPLOY_PROCEDURE.md`.
- **Session-dependent Filament features** (flash messages, `databaseNotifications`,
  unsaved-changes alerts) still need a Laravel session — the app keeps `StartSession`,
  it just no longer authenticates from it.

## 8. Rough effort

| Phase | Effort |
| --- | --- |
| 0 — decisions | ✅ **complete** — all five resolved |
| 1a — extract strategy interface + `dev` picker | small-medium; wide but behaviour-preserving, independently mergeable, and immediately useful for local dev |
| 1b — Pratique strategy + verifier | ✅ **done** (#159) — the security boundary, and the largest single chunk |
| 2 — proxy config, mTLS, migration script | medium |
| 2a — webhook receiver | **optional, deferrable**; buys promptness only, never correctness |
| 3 — confine builtin behind the boundary | small (mostly moves + driver checks) |
| 4 — tests + cutover | medium; **down from medium-large** — no test-helper rewrite |

The cost is concentrated in the verifier (1b). The two-strategy design makes 3
and 4 substantially cheaper than the delete-everything plan: no schema drops, no
`sql-generate`/hosting-changelog cycle, no rewrite of the helper that ~380
feature tests depend on. It buys that by carrying two auth paths — a real
ongoing cost, paid mostly in CI matrix time and the discipline to keep the
builtin path tested once attention moves on.

**Phase 1 is unblocked.** Nothing in it waits on an outstanding decision. The only
external dependency, pratique PR #3, has since merged (§3.1.1).

Documentation work now in scope, easy to forget because it isn't code:
`docs/handleiding/01_welkom.md` (the 2FA/authenticator section and the login
walkthrough) and `docs/roles_and_permissions.md` (which currently documents
`config/permissions.php` as the whole story — it stays true, but the *assignment*
of org roles moves to Pratique's portal).
