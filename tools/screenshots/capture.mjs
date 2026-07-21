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
 *   file  - path under docs/handleiding/imgs, so the mapping to the manual is explicit
 *   clip  - CSS selector to crop to. Element-based rather than a pixel box: a
 *           container survives layout changes, a coordinate rectangle does not.
 *           Omit for a full-viewport shot.
 *   pad   - pixels of breathing room around the clipped element.
 *   shoot - drives the app to the required state and adds annotations.
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
    name: 'organisation-snapshots',
    file: '03_goedkeuringsproces/05_organisation-snapshots.png',
    auth: true,
    clip: '.fi-main',
    async shoot(page) {
      await page.goto(`${BASE}/${tenantOf(page)}/organisation-snapshot-approvals`, {
        waitUntil: 'networkidle',
      });
      await page.waitForSelector('table');
    },
  },
  {
    name: 'personal-approvals',
    file: '03_goedkeuringsproces/07_personal-snapshot-approvals_akkoord_geven.png',
    auth: true,
    // The table section only; the page body below it is empty.
    clip: '.fi-ta',
    pad: 12,
    async shoot(page) {
      await page.goto(`${BASE}/${tenantOf(page)}/personal-snapshot-approvals`, {
        waitUntil: 'networkidle',
      });
      await page.waitForSelector('table');
      // Selecting a row reveals the Akkoord / Niet akkoord bulk actions, which
      // are what this figure is about.
      await page.locator('table tbody input[type=checkbox]').first().check();
      await page.waitForTimeout(600);
      await page.evaluate(() => {
        const btn = [...document.querySelectorAll('button')].find((b) =>
          /^\s*Akkoord\s*$/i.test(b.textContent || ''),
        );
        if (!btn) throw new Error('Akkoord button not found');
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

async function gotoRegister(page) {
  await page.goto(`${BASE}/${tenantOf(page)}/avg-responsible-processing-records`, {
    waitUntil: 'networkidle',
  });
  await page.waitForSelector('table', { timeout: 15000 });
}

async function login(page) {
  await page.goto(loginUrl(EMAIL), { waitUntil: 'networkidle' });
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
        ? totp(tinker(`echo App\\Models\\User::where("email", "${EMAIL}")->firstOrFail()->otp_secret;`))
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
async function clipOf(page, selector, pad = 0) {
  const el = page.locator(selector).first();
  if ((await el.count()) === 0) throw new Error(`clip selector not found: ${selector}`);
  const box = await el.boundingBox();
  if (!box) throw new Error(`clip selector has no box: ${selector}`);
  const size = page.viewportSize();
  const x = Math.max(0, box.x - pad);
  const y = Math.max(0, box.y - pad);
  return {
    x,
    y,
    width: Math.min(box.width + pad * 2, size.width - x),
    height: Math.min(box.height + pad * 2, size.height - y),
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
let authed = false;
// Skipped figures are excluded from a full run but still selectable by name.
const todo = FIGURES.filter((f) => (ONLY.length ? ONLY.includes(f.name) : !f.skip));
const failures = [];

for (const fig of todo) {
  try {
    if (fig.auth && !authed) {
      await login(page);
      authed = true;
    }
    await fig.shoot(page);

    const outPath = join(OUT, fig.file);
    mkdirSync(dirname(outPath), { recursive: true });
    await page.screenshot({
      path: outPath,
      ...(fig.clip ? { clip: await clipOf(page, fig.clip, fig.pad ?? 0) } : {}),
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
