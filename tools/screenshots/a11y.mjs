/**
 * Accessibility audit of the main pages, using axe-core.
 *
 * Shares the login and navigation of capture.mjs - the browser is already
 * driving these pages for screenshots, so checking them costs little extra.
 *
 * Usage:
 *   CMS_DIR=../../src/cms node a11y.mjs                 # summary per page
 *   CMS_DIR=../../src/cms node a11y.mjs --json out.json # full report
 *   CMS_DIR=../../src/cms node a11y.mjs --impact serious,critical
 *
 * Exits non-zero when violations at or above the --impact threshold are found,
 * so it can gate CI once the current findings are triaged.
 */
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';
import { writeFileSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import { dirname, join, resolve } from 'node:path';

const here = dirname(fileURLToPath(import.meta.url));

const arg = (name, fallback) => {
  const i = process.argv.indexOf(`--${name}`);
  return i > -1 ? process.argv[i + 1] : fallback;
};

const BASE = arg('base', process.env.APP_URL || 'http://127.0.0.1:8000');
const EMAIL = process.env.ADMIN_EMAIL || 'admin@example.com';
const CMS_DIR = resolve(process.env.CMS_DIR || join(here, '../../src/cms'));
const PHP = process.env.PHP_BIN || 'php';
const JSON_OUT = arg('json', null);
const IMPACTS = (arg('impact', 'serious,critical') || '').split(',').filter(Boolean);

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

/** Pages to audit, as paths relative to the tenant root ('' is the login page). */
const PAGES = [
  { name: 'register-list', path: 'avg-responsible-processing-records' },
  { name: 'record-edit', path: null, resolve: recordEditPath },
  { name: 'snapshot-view', path: null, resolve: snapshotPath },
  { name: 'organisation-approvals', path: 'organisation-snapshot-approvals' },
  { name: 'personal-approvals', path: 'personal-snapshot-approvals' },
  { name: 'users', path: 'users' },
  { name: 'data-breaches', path: 'data-breach-records' },
  { name: 'documents', path: 'documents' },
  { name: 'profile', path: 'profile' },
  // Last: auditing this clears the session, and logging back in would trip the
  // OTP rate limit (3 attempts per 60s).
  { name: 'login', path: null, auth: false },
];

function recordEditPath() {
  const id = tinker(`
    echo App\\Models\\Avg\\AvgResponsibleProcessingRecord::query()
      ->whereHas("organisation", fn($q) => $q->where("slug", "nipg"))
      ->orderBy("id")->firstOrFail()->id;
  `);
  return `avg-responsible-processing-records/${id}/edit`;
}

function snapshotPath() {
  const id = tinker('echo App\\Models\\Snapshot::query()->orderBy("id")->firstOrFail()->id;');
  return `snapshots/${id}`;
}

async function login(page) {
  await page.goto(loginUrl(EMAIL), { waitUntil: 'networkidle' });
  await page.getByRole('button', { name: /^Inloggen$/i }).first().click();
  await page.waitForLoadState('networkidle');
  if (page.url().includes('two-factor-authentication')) {
    await page.locator('#code').fill('000000');
    await page.locator('#code').press('Enter');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1500);
  }
  if (page.url().includes('two-factor-authentication')) {
    throw new Error('login did not complete - is ONE_TIME_PASSWORD_DRIVER=fake set?');
  }
}

const browser = await chromium.launch();
const context = await browser.newContext({
  viewport: { width: 1680, height: 1000 },
  locale: 'nl-NL',
  reducedMotion: 'reduce',
});
const page = await context.newPage();

await login(page);
const tenant = new URL(page.url()).pathname.split('/').filter(Boolean)[0];

const report = [];
for (const spec of PAGES) {
  try {
    if (spec.auth === false) {
      await context.clearCookies();
      await page.goto(`${BASE}/`, { waitUntil: 'networkidle' });
    } else {
      const path = spec.resolve ? spec.resolve() : spec.path;
      await page.goto(`${BASE}/${tenant}/${path}`, { waitUntil: 'networkidle' });
    }
    await page.waitForTimeout(1200); // let Livewire settle

    const results = await new AxeBuilder({ page })
      .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
      .analyze();

    const violations = results.violations.map((v) => ({
      id: v.id,
      impact: v.impact,
      help: v.help,
      nodes: v.nodes.length,
      sample: v.nodes[0]?.target?.join(' ') ?? '',
    }));
    report.push({ page: spec.name, url: page.url(), violations });

    const counts = violations.reduce((acc, v) => {
      acc[v.impact] = (acc[v.impact] ?? 0) + 1;
      return acc;
    }, {});
    const summary = Object.entries(counts)
      .map(([k, n]) => `${n} ${k}`)
      .join(', ');
    console.log(`${violations.length ? '!' : '✓'} ${spec.name}: ${summary || 'no violations'}`);
    for (const v of violations.filter((v) => IMPACTS.includes(v.impact))) {
      console.log(`    [${v.impact}] ${v.id} (${v.nodes}x) - ${v.help}`);
    }
  } catch (e) {
    console.error(`✗ ${spec.name}: ${e.message.split('\n')[0]}`);
    report.push({ page: spec.name, error: e.message.split('\n')[0] });
  }

}

await browser.close();

if (JSON_OUT) {
  writeFileSync(JSON_OUT, JSON.stringify(report, null, 2));
  console.log(`\nfull report -> ${JSON_OUT}`);
}

const blocking = report.flatMap((r) =>
  (r.violations ?? []).filter((v) => IMPACTS.includes(v.impact)),
);
const unique = [...new Set(blocking.map((v) => v.id))];
console.log(
  `\n${blocking.length} violations at ${IMPACTS.join('/')} across ${report.length} pages` +
    (unique.length ? ` (${unique.length} distinct rules: ${unique.join(', ')})` : ''),
);
if (blocking.length) process.exitCode = 1;
