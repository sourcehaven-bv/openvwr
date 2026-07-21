/**
 * Screenshot capture for the handleiding.
 *
 * Designed to be re-run: every figure is declarative, anchored to CSS
 * selectors rather than pixel coordinates, and writes straight into
 * docs/handleiding/imgs/. Re-running after a UI change regenerates the manual's
 * figures; a moved element throws instead of silently producing a wrong image.
 *
 * The app is passwordless: login is email -> signed magic link. Rather than
 * scrape mail, we mint a signed URL through artisan.
 *
 * Prerequisites: the app running locally (see
 * docs/local_development_without_docker.md), seeded with TestDataSeeder and
 * ScreenshotSeeder.
 *
 * Usage:
 *   node capture.mjs                     # all figures, into docs/handleiding/imgs
 *   node capture.mjs --only login,export # a subset
 *   node capture.mjs --out ./preview     # elsewhere, to compare before replacing
 */
import { chromium } from 'playwright';
import { readFileSync, mkdirSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { createHmac } from 'node:crypto';
import { fileURLToPath } from 'node:url';
import { dirname, join, resolve } from 'node:path';

const here = dirname(fileURLToPath(import.meta.url));
const annotate = readFileSync(join(here, 'annotate.js'), 'utf8');

const arg = (name, fallback) => {
  const i = process.argv.indexOf(`--${name}`);
  return i > -1 ? process.argv[i + 1] : fallback;
};

const BASE = arg('base', process.env.APP_URL || 'http://127.0.0.1:8000');
const ONLY = (arg('only', '') || '').split(',').filter(Boolean);
const EMAIL = process.env.ADMIN_EMAIL || 'admin@example.com';
const CMS_DIR = resolve(process.env.CMS_DIR || join(here, '../../src/cms'));
const PHP = process.env.PHP_BIN || 'php';
const OUT = resolve(arg('out', join(here, '../../docs/handleiding/imgs')));

const tinker = (code) =>
  execFileSync(PHP, ['artisan', 'tinker', '--execute', code], {
    cwd: CMS_DIR,
    encoding: 'utf8',
    stdio: ['ignore', 'pipe', 'ignore'],
  })
    .trim()
    .split('\n')
    .pop()
    .trim();

/** Mint a signed passwordless login URL. */
function loginUrl(email) {
  const url = tinker(`
    $u = App\\Models\\User::where("email", "${email}")->firstOrFail();
    app(App\\Services\\UserLoginToken\\UserLoginService::class)->sendPasswordLessLoginLink($u, "/");
    $t = $u->userLoginTokens()->orderByDesc("expires_at")->firstOrFail();
    echo (new App\\Mail\\Authentication\\PasswordLessLoginLink($t))->link;
  `);
  if (!url.startsWith('http')) throw new Error(`could not mint login URL: ${url}`);
  return url;
}

/** RFC 6238 TOTP - verified against the spec's published test vectors. */
function totp(secret, when = Date.now()) {
  const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
  let bits = '';
  for (const c of secret.toUpperCase().replace(/=+$/, '')) {
    const v = alphabet.indexOf(c);
    if (v >= 0) bits += v.toString(2).padStart(5, '0');
  }
  const bytes = Buffer.from(bits.match(/.{8}/g)?.map((b) => parseInt(b, 2)) ?? []);
  const cb = Buffer.alloc(8);
  cb.writeBigUInt64BE(BigInt(Math.floor(when / 1000 / 30)));
  const digest = createHmac('sha1', bytes).update(cb).digest();
  const offset = digest[digest.length - 1] & 0x0f;
  return ((digest.readUInt32BE(offset) & 0x7fffffff) % 1_000_000).toString().padStart(6, '0');
}

/**
 * Figure definitions.
 *
 *   file     - path under docs/handleiding/imgs, so the mapping to the manual is explicit
 *   clip     - CSS selector to crop to. Element-based rather than a pixel box: a
 *              container survives layout changes, a coordinate rectangle does not.
 *              Omit for a full-viewport shot.
 *   pad      - pixels of breathing room around the clipped element.
 *   fullPage - capture the entire scrollable page instead of clipping.
 *   shoot    - drives the app to the required state and adds annotations.
 *
 * When in doubt, clip wider. A figure with some extra chrome around it still
 * reads fine in the manual; one that has cropped away the table the surrounding
 * paragraph refers to is simply wrong, and the error is easy to miss because the
 * image still looks plausible on its own.
 */
const FIGURES = [
  {
    name: 'login',
    file: '01_welkom/01_login.png',
    auth: false,
    // The card, not the <form>: the form element excludes the heading above it.
    clip: '.fi-simple-main',
    pad: 24,
    async shoot(page) {
      await page.goto(`${BASE}/`, { waitUntil: 'networkidle' });
      await page.waitForSelector('form');
    },
  },
  {
    name: 'registers',
    file: '02_registers/01_avg-responsible-processing-records.png',
    auth: true,
    async shoot(page) {
      await gotoRegister(page);
    },
  },
  {
    name: 'export',
    file: '05_overige_functies/01_avg-responsible-processing-records_export.png',
    auth: true,
    // Matches the original crop: topbar + heading + first rows, no full sidebar.
    clip: '.fi-main',
    async shoot(page) {
      await gotoRegister(page);
      await page.evaluate(() => {
        const btn = [...document.querySelectorAll('button, a')].find((b) =>
          /^\s*Exporteren\s*$/i.test(b.textContent || ''),
        );
        if (!btn) throw new Error('Exporteren button not found');
        window.__annotate.arrow(btn, { side: 'left', length: 150 });
      });
    },
  },
  {
    name: 'export-complete',
    file: '05_overige_functies/02_avg-responsible-processing-records_export_complete.png',
    auth: true,
    // Requires QUEUE_CONNECTION=database and a running `php artisan queue:work`
    // (see the README): on the sync queue the completion notice is a session
    // flash that never reaches the notifications table.
    // The notification card itself; the surrounding panel is full-viewport
    // height and would leave most of the image empty.
    clip: '.fi-no-notification',
    pad: 0,
    async shoot(page) {
      // Start from a clean slate: waitForExport must track *this* run's export
      // rather than a previous row, and stale notifications would otherwise
      // stack up in the panel on every re-run.
      tinker('DB::table("exports")->delete(); DB::table("notifications")->delete();');
      await gotoRegister(page);
      await page.getByRole('button', { name: /Exporteren/i }).first().click();
      // The action opens a modal; the export only starts on the confirm inside
      // it. Scope to the visible Filament modal: several dialogs exist in the
      // DOM at once, so a bare role=dialog lookup is ambiguous.
      await page
        .locator('.fi-modal-footer-actions button')
        .filter({ hasText: /^\s*Exporteren\s*$/ })
        .first()
        .click();
      // Two notifications appear: "Exporteren gestart" immediately, then
      // "Exporteren afgerond" with the download links once the worker is done.
      // The manual shows the second one, so wait for the download action
      // rather than any toast - the start notice also contains "voltooid".
      await waitForExport(page);
      // With a real queue driver this is a database notification, so it needs a
      // page load to render (on the sync queue it would be a session flash and
      // reloading would destroy it - see ExportCompletion.php).
      await page.reload({ waitUntil: 'networkidle' });
      await page.getByRole('button', { name: /Meldingen openen/i }).first().click();
      await page.waitForSelector('text=/downloaden/i', { timeout: 30000 });
      await page.waitForTimeout(800);
    },
  },
  {
    name: 'record-edit',
    file: '02_registers/02_avg-responsible-processing-records_edit.png',
    auth: true,
    // The manual points at the domain navigation on the right and the relation
    // tables below the form ("de tabellen onderaan in het scherm"), and the
    // original figure includes the sidebar for context - so clip to the whole
    // layout, not .fi-main. Not fullPage either: every domain section renders
    // expanded in the DOM, which makes the page ~28000px tall and the figure
    // unreadable. maxHeight keeps the framing close to the original.
    // The whole layout, not .fi-main: the manual points at the sidebar and the
    // domain navigation beside the form. fullPage because the relation tables
    // sit below the fold; maxHeight so a long record cannot stretch the figure
    // to an unreadable sliver. Requires register_layout=steps (pinned by
    // ScreenshotSeeder) - the one_page preference renders a ~28000px page.
    clip: '.fi-layout',
    fullPage: true,
    maxHeight: 1800,
    async shoot(page) {
      await gotoSeededRecord(page);
    },
  },
  {
    name: 'record-version',
    file: '03_goedkeuringsproces/01_avg-responsible-processing-records_edit_versie.png',
    auth: true,
    // Top of the edit page: heading plus the "Versie aanmaken" action. Extra
    // padding leaves room for the arrow above the button.
    clip: '.fi-header',
    pad: 90,
    async shoot(page) {
      await gotoSeededRecord(page);
      await page.evaluate(() => {
        const btn = [...document.querySelectorAll('button, a')].find((b) =>
          /Versie aanmaken/i.test(b.textContent || ''),
        );
        if (!btn) throw new Error('"Versie aanmaken" button not found');
        // From above: pointing from the left would cross the "Dupliceren"
        // button that sits immediately beside it.
        window.__annotate.arrow(btn, { side: 'top', length: 70, gap: 10 });
      });
    },
  },
  {
    name: 'version-select',
    file: '03_goedkeuringsproces/02_avg-responsible-processing-records_edit_versie_select.png',
    auth: true,
    clip: '.fi-ta',
    pad: 12,
    async shoot(page) {
      await gotoSeededRecord(page);
      await openVersionsTab(page);
      await page.evaluate(() => {
        // Point at the in-review version (2), the one awaiting approval.
        const cell = [...document.querySelectorAll('table tbody tr')]
          .find((tr) => /In review/i.test(tr.textContent || ''))
          ?.querySelector('td:first-child');
        if (!cell) throw new Error('in-review version row not found');
        // From the left: the table spans the full width, so a right-side arrow
        // would land past the page edge.
        window.__annotate.arrow(cell, { side: 'left', length: 110 });
      });
    },
  },
  {
    name: 'snapshot-signatures',
    file: '03_goedkeuringsproces/03_snapshots_ondertekeningen.png',
    auth: true,
    clip: '.fi-page',
    pad: 12,
    async shoot(page) {
      await gotoSeededSnapshot(page);
      await page.evaluate(() => {
        const tab = [...document.querySelectorAll('a, button')].find((t) =>
          /Ondertekeningen/i.test(t.textContent || ''),
        );
        if (!tab) throw new Error('Ondertekeningen tab not found');
        window.__annotate.arrow(tab, { side: 'bottom', length: 70, gap: 10 });
      });
    },
  },
  {
    name: 'snapshot-mandateholder',
    file: '03_goedkeuringsproces/04_snapshots_mandaathouder.png',
    auth: true,
    // .fi-page, not .fi-main: the latter is full viewport height and leaves
    // most of the image empty below the short approvals table.
    clip: '.fi-page',
    pad: 12,
    async shoot(page) {
      await gotoSeededSnapshot(page, 'Ondertekeningen');
      await page.evaluate(() => {
        const btn = [...document.querySelectorAll('button, a')].find((b) =>
          /Mandaathouders toevoegen/i.test(b.textContent || ''),
        );
        if (!btn) throw new Error('"Mandaathouders toevoegen" button not found');
        window.__annotate.arrow(btn, { side: 'left', length: 150 });
      });
    },
  },
  {
    name: 'snapshot-notify',
    file: '03_goedkeuringsproces/06_snapshots_mandaathouders_uitnodigen.png',
    auth: true,
    clip: '.fi-main',
    // CANNOT BE REPRODUCED - the feature no longer exists.
    //
    // The original figure shows a "Notificatie versturen" bulk action on the
    // Ondertekeningen tab. app/Livewire/Snapshot/Approvals.php now defines only
    // one bulk action ("delete"); the snapshot_approval.notify translation
    // string is still present but unused. Selecting a row does reveal a bulk
    // action bar, but it only offers Verwijderen.
    //
    // This figure - and the manual text describing it - needs a product
    // decision rather than a capture fix.
    skip: true,
    async shoot(page) {
      await gotoSeededSnapshot(page, 'Ondertekeningen');
      await selectFirstRow(page);
      await page.waitForSelector('text=/Notificatie versturen/i', { timeout: 15000 });
      await page.evaluate(() => {
        const btn = [...document.querySelectorAll('button')].find((b) =>
          /Notificatie versturen/i.test(b.textContent || ''),
        );
        if (!btn) throw new Error('"Notificatie versturen" button not found');
        window.__annotate.arrow(btn, { side: 'left', length: 130 });
      });
    },
  },
  {
    name: 'organisation-snapshots',
    file: '03_goedkeuringsproces/05_organisation-snapshots.png',
    auth: true,
    // The whole layout, not .fi-main: the text points at the overview "in het
    // navigatiemenu links", so the sidebar has to be in frame for that arrow to
    // land on anything.
    clip: '.fi-layout',
    async shoot(page) {
      await page.goto(`${BASE}/${tenantOf(page)}/organisation-snapshot-approvals`, {
        waitUntil: 'networkidle',
      });
      await page.waitForSelector('table');

      // Two arrows, matching the two things the paragraph describes: the
      // overview's place in the sidebar, and the filter control above the table.
      await page.evaluate(() => {
        // Scope to the sidebar: the breadcrumb above the heading has the same
        // text and would otherwise match first.
        const sidebar = document.querySelector('.fi-sidebar, aside');
        const navItem = [...(sidebar?.querySelectorAll('a') ?? [])].find((a) =>
          /Alle Versies/i.test(a.textContent || ''),
        );
        if (!navItem) throw new Error('"Alle Versies" sidebar item not found');
        // From the right: the item sits low in the sidebar, so an arrow above it
        // runs off the top of the canvas.
        // Short: a longer arrow reaches into the table and covers a row.
        window.__annotate.arrow(navItem, { side: 'right', length: 55, gap: 8 });

        // By accessible name rather than a class: Filament's table classes have
        // been renamed across versions, the label has not.
        const filter = [...document.querySelectorAll('button')].find((b) =>
          /filter/i.test(b.getAttribute('aria-label') || b.title || ''),
        );
        if (!filter) throw new Error('table filter trigger not found');
        window.__annotate.arrow(filter, { side: 'top', length: 90, gap: 10 });
      });
    },
  },
  {
    name: 'personal-approvals',
    file: '03_goedkeuringsproces/07_personal-snapshot-approvals_akkoord_geven.png',
    auth: true,
    // ScreenshotSeeder assigns the pending approval to the mandate holder, so
    // only they see the Akkoord / Niet akkoord pair.
    as: 'mandateholder',
    // The "Mijn ondertekeningen" panel at the foot of the version detail page.
    // Not the personal-snapshot-approvals list: the manual describes approving
    // "op de versie detailpagina onderaan", and the list's bulk action offers
    // only Akkoord, while the text explicitly mentions Niet akkoord too.
    // The section containing the approval buttons. Several .fi-section elements
    // exist on the page, so it is resolved by content in shoot() and tagged.
    clip: '[data-shot="approval-section"]',
    pad: 12,
    async shoot(page) {
      await gotoSeededSnapshot(page);
      // The panel sits at the foot of a long page.
      await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
      await page.waitForSelector('text=/Niet akkoord/i', { timeout: 30000 });
      await page.waitForTimeout(400);
      await page.evaluate(() => {
        const btn = [...document.querySelectorAll('button')].find((b) =>
          /^\s*Niet akkoord\s*$/i.test(b.textContent || ''),
        );
        if (!btn) throw new Error('"Niet akkoord" button not found');
        const section = btn.closest('.fi-section');
        if (!section) throw new Error('approval section not found');
        section.setAttribute('data-shot', 'approval-section');
        window.__annotate.arrow(btn, { side: 'right', length: 130 });
      });
    },
  },
  {
    name: 'users',
    file: '04_beheer/01_users_edit.png',
    auth: true,
    clip: '.fi-main',
    async shoot(page) {
      const tenant = tenantOf(page);
      await page.goto(`${BASE}/${tenant}/users`, { waitUntil: 'networkidle' });
      await page.waitForSelector('table');
    },
  },
];

const tenantOf = (page) => {
  const slug = new URL(page.url()).pathname.split('/').filter(Boolean)[0];
  if (!slug) throw new Error(`could not determine tenant slug from ${page.url()}`);
  return slug;
};

/**
 * Poll the exports table until the job finishes. QUEUE_CONNECTION=sync runs it
 * inline, but the request still takes seconds; waiting on the row is more
 * reliable than a fixed sleep.
 */
async function waitForExport(page, timeoutMs = 60000) {
  const deadline = Date.now() + timeoutMs;
  while (Date.now() < deadline) {
    const done = tinker(
      'echo optional(DB::table("exports")->orderByDesc("created_at")->first())->successful_rows ?? 0;',
    );
    if (Number(done) > 0) return;
    await page.waitForTimeout(1500);
  }
  throw new Error('export did not complete within timeout');
}

/**
 * Open the record ScreenshotSeeder gives a version history (established v1 +
 * in-review v2), which the goedkeuringsproces figures depend on. Resolved by
 * name rather than id so it survives a reseed.
 */
async function gotoSeededRecord(page) {
  const id = tinker(`
    echo App\\Models\\Avg\\AvgResponsibleProcessingRecord::query()
      ->whereHas("organisation", fn($q) => $q->where("slug", "nipg"))
      ->where("name", "Afhandelen burgervragen en klachten")
      ->firstOrFail()->id;
  `);
  await page.goto(`${BASE}/${tenantOf(page)}/avg-responsible-processing-records/${id}/edit`, {
    waitUntil: 'networkidle',
  });
  // Wait for the record heading rather than a bare `form`: the page renders
  // several forms and the first can be present before the record has loaded.
  await page.waitForSelector('text=/Afhandelen burgervragen/i', { timeout: 30000 });
}

/**
 * Tick the first table row's selection checkbox, revealing the bulk actions.
 *
 * Filament gives these checkboxes distinct accessible names via an sr-only
 * label ("Item <key> selecteren..." for rows, "Alle items..." for the header),
 * so match on that rather than on DOM position or Alpine's x-on:click binding.
 */
async function selectFirstRow(page) {
  await page
    .getByRole('checkbox', { name: /^Item .*selecteren/i })
    .first()
    .click();
}

/** Switch the record edit page to its "Versies" tab. */
async function openVersionsTab(page) {
  await page.getByRole('tab', { name: /Versies/i }).first().click();
  await page.waitForSelector('table tbody tr');
  await page.waitForTimeout(500);
}

/**
 * Open the in-review snapshot (version 2) that ScreenshotSeeder creates,
 * optionally on one of its tabs.
 */
async function gotoSeededSnapshot(page, tab) {
  const id = tinker(`
    echo App\\Models\\Snapshot::query()
      ->where("name", "Afhandelen burgervragen en klachten")
      ->where("version", 2)
      ->firstOrFail()->id;
  `);
  await page.goto(`${BASE}/${tenantOf(page)}/snapshots/${id}`, { waitUntil: 'networkidle' });
  await page.waitForSelector('text=/Versie bekijken/i', { timeout: 30000 });
  if (tab) {
    await page.getByRole('tab', { name: new RegExp(tab, 'i') }).first().click();
    await page.waitForTimeout(800);
  }
}

async function gotoRegister(page) {
  await page.goto(`${BASE}/${tenantOf(page)}/avg-responsible-processing-records`, {
    waitUntil: 'networkidle',
  });
  await page.waitForSelector('table', { timeout: 15000 });
}

/** Email of the seeded user a figure runs as. */
function emailFor(as) {
  if (as !== 'mandateholder') return EMAIL;
  return tinker(`
    echo App\\Models\\User::query()
      ->where("name", "Marieke de Vries")
      ->firstOrFail()->email;
  `);
}

async function login(page, as) {
  const email = emailFor(as);
  await page.goto(loginUrl(email), { waitUntil: 'networkidle' });
  await page.getByRole('button', { name: /^Inloggen$/i }).first().click();
  await page.waitForLoadState('networkidle');
  if (page.url().includes('/login/consume')) {
    throw new Error(`login did not complete, still at ${page.url()}`);
  }

  // Second factor. With ONE_TIME_PASSWORD_DRIVER=fake any code is accepted;
  // set OTP_FAKE=0 to compute a real TOTP from the seeded secret instead.
  if (page.url().includes('two-factor-authentication')) {
    const code =
      process.env.OTP_FAKE === '0'
        ? totp(tinker(`echo App\\Models\\User::where("email", "${email}")->firstOrFail()->otp_secret;`))
        : '000000';
    await page.locator('#code').fill(code);
    // Livewire form (action=null): clicking the button does not reliably submit.
    await page.locator('#code').press('Enter');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1500);
    if (page.url().includes('two-factor-authentication')) {
      throw new Error(`OTP step did not pass, still at ${page.url()}`);
    }
  }
}

/** Crop to an element's box plus padding, clamped to the page. */
async function clipOf(page, selector, pad = 0, maxHeight = null) {
  const el = page.locator(selector).first();
  if ((await el.count()) === 0) throw new Error(`clip selector not found: ${selector}`);
  const box = await el.boundingBox();
  if (!box) throw new Error(`clip selector has no box: ${selector}`);
  // Clamp to the full scrollable page, not the viewport: an element taller than
  // the viewport (an edit form with its relation tables below it, say) would
  // otherwise be silently cut off at the fold - which is how the manual ended up
  // with a figure whose text described a table that was not in the image.
  const size = await page.evaluate(() => ({
    width: Math.max(document.documentElement.scrollWidth, window.innerWidth),
    height: Math.max(document.documentElement.scrollHeight, window.innerHeight),
  }));
  const x = Math.max(0, box.x - pad);
  const y = Math.max(0, box.y - pad);
  let height = Math.min(box.height + pad * 2, size.height - y);
  if (maxHeight !== null) {
    height = Math.min(height, maxHeight);
  }
  return {
    x,
    y,
    width: Math.min(box.width + pad * 2, size.width - x),
    height,
  };
}

const browser = await chromium.launch();
const context = await browser.newContext({
  viewport: { width: 1680, height: 1000 },
  deviceScaleFactor: 2, // retina, matching the existing screenshots
  locale: 'nl-NL',
  timezoneId: 'Europe/Amsterdam',
  reducedMotion: 'reduce',
});
await context.addInitScript(annotate);

const page = await context.newPage();
// Which seeded user the current session belongs to; figures may need a
// different one (`as: 'mandateholder'`), which forces a re-login.
let authedAs = null;
// Skipped figures are excluded from a full run but still selectable by name.
// Group by user so each session is established once: logging in repeatedly
// trips the OTP rate limit (3 attempts per 60s).
const todo = FIGURES.filter((f) => (ONLY.length ? ONLY.includes(f.name) : !f.skip)).sort(
  (a, b) => (a.as ?? '').localeCompare(b.as ?? ''),
);
const failures = [];

for (const fig of todo) {
  try {
    const wantedUser = fig.as ?? 'admin';
    if (fig.auth && authedAs !== wantedUser) {
      if (authedAs !== null) await context.clearCookies(); // drop the old session
      try {
        await login(page, fig.as);
      } catch (e) {
        // The OTP form allows 3 attempts per 60s; a second login within that
        // window is rejected. Wait the window out and try once more.
        if (!/OTP step did not pass/.test(e.message)) throw e;
        console.log(`  (OTP rate limit hit, waiting 60s before retrying login)`);
        await page.waitForTimeout(61000);
        await login(page, fig.as);
      }
      authedAs = wantedUser;
    }
    await fig.shoot(page);

    const outPath = join(OUT, fig.file);
    mkdirSync(dirname(outPath), { recursive: true });
    await page.screenshot({
      path: outPath,
      ...(fig.fullPage ? { fullPage: true } : {}),
      ...(fig.clip
        ? { clip: await clipOf(page, fig.clip, fig.pad ?? 0, fig.maxHeight ?? null) }
        : {}),
    });
    await page.evaluate(() => window.__annotate?.clear());
    console.log(`✓ ${fig.name} -> ${fig.file}`);
  } catch (e) {
    // Keep going: one broken figure should not block regenerating the rest.
    failures.push({ name: fig.name, error: e.message.split('\n')[0] });
    console.error(`✗ ${fig.name}: ${e.message.split('\n')[0]}`);
    await page.evaluate(() => window.__annotate?.clear()).catch(() => {});
  }
}

await browser.close();

console.log(`\n${todo.length - failures.length}/${todo.length} figures captured into ${OUT}`);
if (failures.length) {
  console.error('failed:', failures.map((f) => f.name).join(', '));
  process.exitCode = 1;
}
