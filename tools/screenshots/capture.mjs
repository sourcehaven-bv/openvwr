/**
 * Screenshot capture for the handleiding.
 *
 * Designed to be re-run: every figure is declarative, anchored to CSS
 * selectors rather than pixel coordinates, and writes straight into
 * src/cms/public/handleiding/, where the application serves them. Re-running
 * after a UI change regenerates the manual's figures; a moved element throws
 * instead of silently producing a wrong image.
 *
 * The app is passwordless: login is email -> signed magic link. Rather than
 * scrape mail, we mint a signed URL through artisan.
 *
 * Prerequisites: the app running locally, seeded with TestDataSeeder and
 * ScreenshotSeeder. See tools/screenshots/README.md.
 *
 * Usage:
 *   node capture.mjs                     # all figures, into src/cms/public/handleiding
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
const OUT = resolve(arg('out', join(here, '../../src/cms/public/handleiding')));

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
  // artisan builds the link from APP_URL, which need not be the server we are
  // driving - a stale APP_URL would log us in on a different origin and leave
  // this one unauthenticated. The signature covers the query, not the host, so
  // rebasing onto BASE keeps the link valid.
  const minted = new URL(url);
  const base = new URL(BASE);
  minted.protocol = base.protocol;
  minted.host = base.host;
  return minted.toString();
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
 *   file     - path under src/cms/public/handleiding, matching the image url in the manual
 *   clip     - CSS selector to crop to. Element-based rather than a pixel box: a
 *              container survives layout changes, a coordinate rectangle does not.
 *              Omit for a full-viewport shot.
 *   pad      - pixels of breathing room around the clipped element.
 *   fullPage - capture the entire scrollable page instead of clipping.
 *   shoot    - drives the app to the required state and adds annotations.
 *   mask     - [{ selector, text, box }] covered with a labelled placeholder
 *              before the screenshot, for content that must not be published at
 *              all. Sized from the element and verified to cover it. `box:
 *              false` masks a line of text without a frame; see otp-setup.
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
    // Top of the edit page: heading plus the "Start vaststellen" action. Extra
    // padding leaves room for the arrow above the button.
    clip: '.fi-header',
    pad: 90,
    async shoot(page) {
      await gotoSeededRecord(page);
      await page.evaluate(() => {
        const btn = [...document.querySelectorAll('button, a')].find((b) =>
          /Start vaststellen/i.test(b.textContent || ''),
        );
        if (!btn) throw new Error('"Start vaststellen" button not found');
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
    name: 'snapshot-statusflow',
    file: '03_goedkeuringsproces/08_snapshots_statusverloop.png',
    auth: true,
    // The status flow itself, not the whole page: the paragraph around it lists
    // the five statuses, so the figure only has to show where the reader sees
    // them back. Anchored on the heading rather than the first .fi-section:
    // the view page stacks several sections, and their order is not fixed.
    clip: '.fi-section:has(h3:text-is("Statusverloop"))',
    pad: 12,
    async shoot(page) {
      await gotoSeededSnapshot(page);
      await page.waitForSelector('text=/Statusverloop/i', { timeout: 15000 });
    },
  },
  {
    name: 'snapshot-statuschange',
    file: '03_goedkeuringsproces/09_snapshots_status_aanpassen.png',
    auth: true,
    // "Status aanpassen" is a header action of the Statusverloop *section*
    // (see ViewInfoTab::getStatusFlowSection), not of the page - so clip to
    // that section, not .fi-header, which carries "Alle versies" instead.
    clip: '.fi-section:has(h3:text-is("Statusverloop"))',
    // Enough headroom above the section for the arrow, which is drawn outside it.
    pad: 56,
    async shoot(page) {
      await gotoSeededSnapshot(page);
      await page.evaluate(() => {
        const btn = [...document.querySelectorAll('button, a')].find((b) =>
          /Status aanpassen/i.test(b.textContent || ''),
        );
        if (!btn) throw new Error('"Status aanpassen" button not found');
        window.__annotate.arrow(btn, { side: 'top', length: 70, gap: 10 });
      });
    },
  },
  {
    name: 'snapshot-signatures',
    file: '03_goedkeuringsproces/03_snapshots_ondertekeningen.png',
    auth: true,
    // Down to the tabs and the panel under them: the text is about clicking
    // "Ondertekeningen", so the Gegevens panel below only pads the figure out.
    clip: '.fi-page',
    pad: 12,
    maxHeight: 660,
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
    name: 'labels',
    file: '06_labels/01_tags.png',
    auth: true,
    clip: '.fi-main',
    async shoot(page) {
      await page.goto(`${BASE}/${tenantOf(page)}/tags`, { waitUntil: 'networkidle' });
      await page.waitForSelector('table');
    },
  },
  {
    name: 'labels-field',
    file: '06_labels/02_avg-responsible-processing-records_edit_labels.png',
    auth: true,
    // The record form is a wizard; the Labels field lives in its first step.
    // Clip to that step - .fi-section no longer wraps the field - and trim the
    // tall remainder, since the rest of the step is the registers chapter's
    // territory.
    clip: '.fi-fo-wizard-step',
    pad: 12,
    maxHeight: 760,
    async shoot(page) {
      // A record ScreenshotSeeder tags with one label of each kind
      // (afdeling / locatie / domein), which is what the chapter explains.
      await gotoRecordNamed(page, 'Onderzoek vaccinatiegraad');
      await page.evaluate(() => {
        const label = [...document.querySelectorAll('label')].find((l) =>
          /^\s*Labels\s*$/i.test(l.textContent || ''),
        );
        if (!label) throw new Error('Labels field not found');
        // From the right: the field spans the full width of the wizard step, so
        // a left-side arrow starts outside the clip and is cut off.
        window.__annotate.arrow(label, { side: 'right', length: 110 });
      });
    },
  },
  {
    name: 'labels-column',
    file: '06_labels/05_avg-responsible-processing-records_labels_kolom.png',
    auth: true,
    // The table itself: the paragraph is about seeing at a glance where a
    // record belongs, which is the Labels column, not the surrounding chrome.
    clip: '.fi-ta',
    pad: 12,
    async shoot(page) {
      await gotoRegister(page);
      // TagsColumn is toggleable(isToggledHiddenByDefault: true), so the column
      // is off until the reader turns it on - which is what the surrounding
      // paragraph tells them to do. The choice lives in Livewire's component
      // state, not on the user or in local storage, so it has to be clicked.
      await page
        .locator('.fi-dropdown-trigger')
        .filter({ hasText: /Kolommen in-\/uitschakelen/i })
        .first()
        .click();
      // Scope to the *visible* panel: the filter dropdown is also in the DOM
      // and also carries a "Labels" row, so an unscoped lookup finds that one
      // and waits forever for a hidden checkbox. The checkboxes have no
      // accessible name either, hence the row-label detour.
      await page
        .locator('.fi-dropdown-panel:visible label')
        .filter({ hasText: /^\s*Labels\s*$/i })
        .first()
        .locator('input[type=checkbox]')
        .check();
      await page.keyboard.press('Escape');
      // Livewire re-renders the table after the toggle, so wait for the column
      // to actually exist before annotating it.
      await page
        .locator('th')
        .filter({ hasText: /^\s*Labels\s*$/i })
        .first()
        .waitFor({ timeout: 15000 });
      await page.waitForTimeout(400);
      await page.evaluate(() => {
        const header = [...document.querySelectorAll('th')].find((th) =>
          /^\s*Labels\s*$/i.test(th.textContent || ''),
        );
        if (!header) throw new Error('Labels column not found');
        // From below, pointing up: the header sits just under the search bar,
        // so a 'top' arrow is drawn over that bar instead of the column.
        window.__annotate.arrow(header, { side: 'bottom', length: 60, gap: 8 });
      });
    },
  },
  {
    name: 'labels-filter',
    file: '06_labels/03_avg-responsible-processing-records_filter_labels.png',
    auth: true,
    // The filter panel is an overlay that hangs past the bottom of .fi-page and
    // even .fi-layout - it is what defines the page height. No container clip
    // can hold it, so give the viewport room instead and clip to the layout.
    clip: '.fi-layout',
    async shoot(page) {
      await page.setViewportSize({ width: 1680, height: 1250 });
      await gotoRegister(page);
      // The filter panel overlays the table, so the figure deliberately keeps
      // it open: it shows the Labels filter, the selected label and the
      // narrowed result at once.
      await page.locator('.fi-ta-header-toolbar button').filter({ hasText: /Filteren/ }).first().click();
      await page.waitForTimeout(1200);
      // Labels is the first filter in the panel. Its Choices.js select only
      // loads options once you type, so search rather than click an option.
      await page.locator('.fi-dropdown-panel .fi-fo-field-wrp .choices').first().click();
      await page.waitForTimeout(800);
      // "Administratie" sits on several records, so the filtered table still
      // shows a few rows; a label with one match reads as an empty result.
      await page.keyboard.type('Administratie');
      await page.waitForTimeout(2000);
      await page.keyboard.press('Enter');
      await page.waitForTimeout(2500);
      // Collapse the now-empty Choices search dropdown, which would otherwise
      // cover the filters below with "geen resultaten". Blur the input rather
      // than press Escape: Escape closes the whole filter panel, and the panel
      // is what this figure is about.
      await page.evaluate(() => document.activeElement?.blur());
      await page.waitForTimeout(1000);
      const rows = await page.locator('table tbody tr').count();
      if (rows === 0) throw new Error('label filter matched no rows');
      // The feedback singled this button out as hard to find - it is a small
      // funnel icon that all but disappears next to the open panel.
      await page.evaluate(() => {
        const btn = [...document.querySelectorAll('.fi-ta-header-toolbar button')].find((b) =>
          /Filteren/i.test(b.textContent || ''),
        );
        if (!btn) throw new Error('filter button not found');
        window.__annotate.arrow(btn, { side: 'left', length: 90, gap: 10 });
      });
    },
    // Restore the shared viewport: every later figure is framed for 1000px.
    async after(page) {
      await page.setViewportSize({ width: 1680, height: 1000 });
    },
  },
  {
    name: 'labels-system',
    file: '06_labels/04_systems_labels.png',
    auth: true,
    // Labels are not limited to the verwerkingsregisters. This figure shows the
    // same field on Systemen/Applicaties, which is what the chapter claims.
    clip: '.fi-main',
    maxHeight: 620,
    async shoot(page) {
      // Resolved by name rather than taking the first table row: the overview
      // sorts on updated_at, so the seeded system is not reliably on top.
      const id = tinker(`
        echo App\\Models\\System::query()
          ->whereHas("organisation", fn($q) => $q->where("slug", "nipg"))
          ->where("description", "Personeelsinformatiesysteem")
          ->firstOrFail()->id;
      `);
      await page.goto(`${BASE}/${tenantOf(page)}/systems/${id}/edit`, { waitUntil: 'networkidle' });
      // The description lives in an input, so it is not matchable as page text;
      // wait for the Labels field itself, which is what the figure is about.
      await page.waitForSelector('.choices__item', { timeout: 30000 });
      await page.evaluate(() => {
        const label = [...document.querySelectorAll('label')].find((l) =>
          /^\s*Labels\s*$/i.test(l.textContent || ''),
        );
        if (!label) throw new Error('Labels field not found on system');
        // To the right of the label text: the field spans the full width, so a
        // left-side arrow is clipped by the page edge and one from above lands
        // on the field sitting directly overhead.
        window.__annotate.arrow(label, { side: 'right', length: 110 });
      });
    },
  },
  {
    name: 'dpia-prescan',
    file: '03_dpia/01_dpia-prescan-records_edit.png',
    auth: true,
    // The header carries the "DPIA starten" action, and the outcome sits just
    // below it - both belong in one figure: the button only makes sense once
    // you see why it appeared.
    clip: '.fi-main',
    maxHeight: 900,
    async shoot(page) {
      const id = tinker(`
        echo App\\Models\\Dpia\\DpiaPrescanRecord::query()
          ->whereHas("organisation", fn($q) => $q->where("slug", "nipg"))
          ->where("name", "Cameratoezicht toegangsbeveiliging")
          ->firstOrFail()->id;
      `);
      await page.goto(`${BASE}/${tenantOf(page)}/dpia-prescan-records/${id}/edit`, {
        waitUntil: 'networkidle',
      });
      await passOtp(page);
      // Wait for the action itself: the record name lives in a form input, so
      // a text selector never matches it.
      await page
        .locator('button, a')
        .filter({ hasText: /DPIA starten/i })
        .first()
        .waitFor({ timeout: 30000 });
      await page.waitForTimeout(600);
      await page.evaluate(() => {
        const btn = [...document.querySelectorAll('button, a')].find((b) =>
          /DPIA starten/i.test(b.textContent || ''),
        );
        if (!btn) throw new Error('"DPIA starten" button not found - is the outcome REQUIRED?');
        window.__annotate.arrow(btn, { side: 'left', length: 130 });
      });
    },
  },
  {
    name: 'dpia-personal-data',
    file: '03_dpia/02_dpia-records_edit_personal-data.png',
    auth: true,
    // .fi-fo-wizard, not .fi-fo-wizard-step: Filament keeps every step mounted
    // at height 0 and only the active one has content, so a step selector
    // clips to an empty sliver.
    clip: '.fi-fo-wizard',
    pad: 12,
    // High enough that the annotated field is in frame: the arrow is drawn
    // beside it, and a tighter crop cuts the tip off.
    maxHeight: 1750,
    async shoot(page) {
      await gotoSeededDpia(page, '2. Persoonsgegevens');
      // The type is what the paragraph is about: it decides whether an
      // exception ground is required.
      await page.evaluate(() => {
        const label = [...document.querySelectorAll('label, .fi-fo-field-wrp-label')].find((l) =>
          /^\s*Type persoonsgegeven/i.test(l.textContent || ''),
        );
        if (!label) throw new Error('"Type persoonsgegeven" field not found');
        window.__annotate.arrow(label, { side: 'right', length: 110 });
      });
    },
  },
  {
    name: 'dpia-risks',
    file: '03_dpia/03_dpia-records_edit_risks.png',
    auth: true,
    clip: '.fi-fo-wizard',
    pad: 12,
    // High enough that the annotated field is in frame: the arrow is drawn
    // beside it, and a tighter crop cuts the tip off.
    maxHeight: 1750,
    async shoot(page) {
      await gotoSeededDpia(page, "16. Risico's voor betrokkenen");
      // Kans en impact together produce the risk level the text explains.
      await page.evaluate(() => {
        const label = [...document.querySelectorAll('label, .fi-fo-field-wrp-label')].find((l) =>
          /^\s*Kans/i.test(l.textContent || ''),
        );
        if (!label) throw new Error('"Kans" field not found');
        window.__annotate.arrow(label, { side: 'right', length: 110 });
      });
    },
  },
  {
    name: 'website-tree',
    file: '04_beheer/05_public-website-tree.png',
    auth: true,
    // isScopedToTenant is false on the resource, but the route still carries a
    // {tenant} segment - dropping it gives a 404.
    clip: '.fi-main',
    // Just the tree: the page is mostly empty below it. In CSS pixels - the
    // capture runs at deviceScaleFactor 2, so the png is twice this tall.
    maxHeight: 440,
    async shoot(page) {
      await page.goto(`${BASE}/${tenantOf(page)}/public-website-tree`, {
        waitUntil: 'networkidle',
      });
      await passOtp(page);
      await page.waitForSelector('text=/Website organogram/i', { timeout: 30000 });
      await page.waitForTimeout(800);
      await page.evaluate(() => {
        const btn = [...document.querySelectorAll('button, a')].find((b) =>
          /Nieuw item maken/i.test(b.textContent || ''),
        );
        if (!btn) throw new Error('"Nieuw item maken" button not found');
        window.__annotate.arrow(btn, { side: 'left', length: 140 });
      });
    },
  },
  {
    name: 'users-add',
    file: '04_beheer/04_users_toevoegen.png',
    auth: true,
    // The list, not the edit page: this figure belongs to "Gebruikers
    // toevoegen", and the button the text places "rechtsboven de
    // gebruikerstabel" only exists here.
    clip: '.fi-main',
    maxHeight: 700,
    async shoot(page) {
      await page.goto(`${BASE}/${tenantOf(page)}/users`, { waitUntil: 'networkidle' });
      await passOtp(page);
      await page.locator('table:visible').first().waitFor({ timeout: 30000 });
      await page.evaluate(() => {
        const btn = [...document.querySelectorAll('button, a')].find((b) =>
          /Gebruiker (aanmaken|toevoegen)|Nieuwe gebruiker/i.test(b.textContent || ''),
        );
        if (!btn) throw new Error('"Gebruiker aanmaken" button not found');
        // From the left: the button sits at the right edge of the content area,
        // so an arrow on its right would fall outside the clip.
        window.__annotate.arrow(btn, { side: 'left', length: 150 });
      });
    },
  },
  {
    name: 'users',
    file: '04_beheer/01_users_edit.png',
    auth: true,
    // The whole layout, matching the original: the surrounding text places user
    // management "in het navigatiemenu onder Organisaties", so the sidebar is
    // part of what the figure shows. fullPage with a bound so the role toggles
    // below the fold are in frame without stretching to the page's full height.
    clip: '.fi-layout',
    fullPage: true,
    maxHeight: 1500,
    async shoot(page) {
      // The edit page, not the list: the text describes what can be done once a
      // user is opened - "Op deze pagina kunnen rollen worden aangepast" and the
      // red delete button "rechtsbovenin". The list shows neither.
      const id = tinker(`
        echo App\\Models\\User::query()
          ->where("name", "Marieke de Vries")
          ->firstOrFail()->id;
      `);
      await page.goto(`${BASE}/${tenantOf(page)}/users/${id}/edit`, {
        waitUntil: 'networkidle',
      });
      await page.waitForSelector('text=/Organisatie rollen/i', { timeout: 30000 });
      // Filament restores the sidebar's scroll position, which leaves the figure
      // showing the foot of the navigation rather than its start.
      await page.evaluate(() => {
        window.scrollTo(0, 0);
        // Whatever is actually scrollable in the sidebar - the class has moved
        // between Filament versions.
        const aside = document.querySelector('aside, .fi-sidebar');
        for (const el of [aside, ...(aside?.querySelectorAll('*') ?? [])]) {
          if (el && el.scrollHeight > el.clientHeight + 10) el.scrollTop = 0;
        }
      });
      await page.waitForTimeout(300);
    },
  },
  {
    name: 'otp-setup',
    file: '01_welkom/02_profile_one_time_password.png',
    auth: true,
    // The profile page the OTP gate forces an un-enrolled user onto. Clip to the
    // two-factor block rather than .fi-main: the page also carries the
    // personal-info and settings panels, which would push the card the text
    // actually describes off the bottom of the figure. Anchored on the visible
    // heading rather than nth-of-type, so reordering the panels moves the figure
    // with them instead of silently capturing whichever block sits there.
    clip: '.filament-breezy-grid-section:has(.filament-breezy-grid-title:text-is("Tweefactorauthenticatie"))',
    pad: 16,
    // The figure necessarily renders a scannable QR code and the matching key
    // in plain text, and it is written under src/cms/public/, which the app
    // serves without authentication. The captured secret is faker output from
    // a dev database, so nothing real leaks today - but the mechanism
    // publishes whatever the database holds at capture time, so cover it here
    // rather than relying on that staying true.
    mask: [
      { selector: '[data-screenshot-mask="qr"]', text: 'QR-code\nverschijnt hier' },
      // A line of text, not a placeholder for a missing graphic: no frame.
      { selector: '[data-screenshot-mask="secret"]', text: 'Sleutel: XXXX XXXX XXXX XXXX', box: false },
    ],
    async shoot(page) {
      // The seeded user already has a confirmed factor, so the gate would never
      // fire. Roll it back to "enrolled but not confirmed" - the state a real
      // user is in halfway through setup, which is what renders the QR code and
      // the "Bevestigen" button the surrounding text describes.
      const restore = tinker(`
        $u = App\\Models\\User::where("email", "${EMAIL}")->firstOrFail();
        echo $u->otp_confirmed_at?->toIso8601String() ?? "";
      `);
      tinker(`
        $u = App\\Models\\User::where("email", "${EMAIL}")->firstOrFail();
        $u->otp_confirmed_at = null;
        $u->save();
      `);
      try {
        // Any protected URL will do: EnforceOneTimePassword redirects it to the
        // profile page, which is precisely the forced step being documented.
        await page.goto(`${BASE}/${tenantOf(page)}/avg-responsible-processing-records`, {
          waitUntil: 'networkidle',
        });
        await page.waitForSelector('text=/Tweefactorauthenticatie is verplicht/i', {
          timeout: 30000,
        });
        if (!page.url().includes('profile')) {
          throw new Error(`OTP gate did not redirect to the profile page, at ${page.url()}`);
        }
        // The block sits below the personal-info and settings panels, so it
        // starts outside the viewport; without this the clip rectangle falls off
        // the captured image and the screenshot call fails outright.
        await page.locator(this.clip).first().scrollIntoViewIfNeeded();
        await page.waitForTimeout(400);
      } finally {
        // Always restore, even on failure: otherwise every later figure in this
        // run would be bounced to the profile page as well.
        tinker(`
          $u = App\\Models\\User::where("email", "${EMAIL}")->firstOrFail();
          $u->otp_confirmed_at = ${restore ? `"${restore}"` : 'now()'};
          $u->save();
        `);
      }
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

/** Open the edit page of a seeded record, resolved by name rather than id. */
async function gotoRecordNamed(page, name) {
  const id = tinker(`
    echo App\\Models\\Avg\\AvgResponsibleProcessingRecord::query()
      ->whereHas("organisation", fn($q) => $q->where("slug", "nipg"))
      ->where("name", "${name}")
      ->firstOrFail()->id;
  `);
  await page.goto(`${BASE}/${tenantOf(page)}/avg-responsible-processing-records/${id}/edit`, {
    waitUntil: 'networkidle',
  });
  await page.waitForSelector(`text=/${name.split(' ')[0]}/i`, { timeout: 30000 });
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

/**
 * Bring the versions table on a record's edit page into view.
 *
 * ScreenshotSeeder pins register_layout=steps, and in that layout the versions
 * table is not behind a tab: the relation managers render below the wizard,
 * which is what the manual describes ("onderaan de pagina"). Clicking a
 * "Versies" tab worked under the older tabbed layout only.
 *
 * They are also suppressed on the wizard's first step, where an edit page
 * opens by default - the tables are in the DOM but none is visible. Any later
 * step renders them, so move off step one before waiting.
 */
async function openVersionsTab(page) {
  const url = new URL(page.url());
  url.searchParams.set('step', 'opmerkingen');
  await page.goto(url.toString(), { waitUntil: 'networkidle' });
  await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
  // Scope the row wait to the visible table. A bare 'table tbody tr' resolves
  // against the hidden relation managers that also sit in the DOM here, and
  // then waits forever for one of those rows to become visible.
  const table = page.locator('table:visible').first();
  await table.waitFor({ timeout: 30000 });
  await table.locator('tbody tr').first().waitFor({ timeout: 30000 });
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

/**
 * Open the seeded DPIA on one of its wizard steps.
 *
 * ScreenshotSeeder::createDpia creates it; the step slug is Filament's
 * kebab-cased label, so "2. Persoonsgegevens" becomes "persoonsgegevens".
 */
async function gotoSeededDpia(page, step) {
  const id = tinker(`
    echo App\\Models\\Dpia\\DpiaRecord::query()
      ->whereHas("organisation", fn($q) => $q->where("slug", "nipg"))
      ->where("name", "Cameratoezicht toegangsbeveiliging")
      ->firstOrFail()->id;
  `);
  await page.goto(`${BASE}/${tenantOf(page)}/dpia-records/${id}/edit`, {
    waitUntil: 'networkidle',
  });
  await passOtp(page);
  await page.waitForSelector('.fi-fo-wizard', { timeout: 30000 });

  // The wizard is driven by Livewire, not by the url: a ?step= parameter is
  // ignored and the page silently stays on step one - a green run producing
  // the wrong figure. Click the step in the navigation instead, and assert we
  // arrived, so a renamed paragraph fails loudly.
  await page
    .locator('.fi-fo-wizard-header-step')
    .filter({ hasText: step })
    .first()
    .click();
  await page.waitForTimeout(1200);

  // Assert on the step navigation, not on the panel: the panel's text is the
  // field content, which does not repeat the paragraph title.
  const active = page
    .locator('.fi-fo-wizard-header-step[aria-current="step"], .fi-fo-wizard-header-step')
    .filter({ hasText: step });
  if ((await active.count()) === 0) {
    throw new Error(`wizard step "${step}" not found`);
  }
  await page.waitForTimeout(400);
}

async function gotoRegister(page) {
  await page.goto(`${BASE}/${tenantOf(page)}/avg-responsible-processing-records`, {
    waitUntil: 'networkidle',
  });
  // This is typically the first protected page of the run, so the OTP gate
  // fires here rather than at login.
  await passOtp(page);
  await page.locator('table:visible').first().waitFor({ timeout: 15000 });
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

  await passOtp(page, email);
}

/**
 * Clear the second-factor gate if it is showing.
 *
 * EnforceOneTimePassword only fires on the first *protected* page, so this is
 * not necessarily right after login: landing on the tenant root passes, and the
 * redirect appears when a register is opened. Every navigation to a protected
 * page therefore has to be prepared to answer it, or the figure silently
 * captures a login-gated shell instead of the page it names.
 *
 * With ONE_TIME_PASSWORD_DRIVER=fake any code is accepted; set OTP_FAKE=0 to
 * compute a real TOTP from the seeded secret instead.
 */
async function passOtp(page, email = EMAIL) {
  if (!page.url().includes('two-factor-authentication')) return;

  // config/auth.php allows 3 validation attempts per 60s. A full run logs in
  // once per user and re-logs in on every session switch, so a long run trips
  // it. Retry here rather than at the call site: the gate fires on the first
  // protected page, where a rejected code surfaces as a selector timeout in
  // whichever helper navigated - not as an error the caller can recognise.
  for (let attempt = 0; attempt < 2; attempt++) {
    const code =
      process.env.OTP_FAKE === '0'
        ? totp(tinker(`echo App\\Models\\User::where("email", "${email}")->firstOrFail()->otp_secret;`))
        : '000000';
    await page.locator('#code').fill(code);
    // Livewire form (action=null): clicking the button does not reliably submit.
    await page.locator('#code').press('Enter');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1500);

    if (!page.url().includes('two-factor-authentication')) return;

    if (attempt === 0) {
      console.log('  (OTP rate limit hit, waiting 61s)');
      await page.waitForTimeout(61000);
      await page.reload({ waitUntil: 'networkidle' });
      if (!page.url().includes('two-factor-authentication')) return;
    }
  }

  throw new Error(`OTP step did not pass, still at ${page.url()}`);
}

/**
 * Cover elements that must not be published, then confirm they really are
 * covered.
 *
 * The verification is the point. Painting an overlay cannot fail loudly on its
 * own: if a selector goes stale the mask lands somewhere else, the capture
 * reports success, and the figure ships the secret. So after painting, check
 * each target's geometry against the masks actually on the page.
 */
async function applyMasks(page, masks) {
  for (const { selector, text, ...options } of masks) {
    const count = await page.locator(selector).count();
    if (count === 0) throw new Error(`mask selector not found: ${selector}`);
    await page.evaluate(
      ([sel, label, opts]) => window.__annotate.mask(sel, label, opts),
      [selector, text ?? '', options],
    );
    const covered = await page.evaluate((sel) => window.__annotate.masked(sel), selector);
    if (!covered) {
      throw new Error(`mask does not cover ${selector} - refusing to publish the figure`);
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
    // After shoot(), so the page has settled where the masked elements live,
    // and before the screenshot, so the secret is never in the captured image.
    if (fig.mask) await applyMasks(page, fig.mask);

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
    if (fig.after) await fig.after(page);
    console.log(`✓ ${fig.name} -> ${fig.file}`);
  } catch (e) {
    // Keep going: one broken figure should not block regenerating the rest.
    failures.push({ name: fig.name, error: e.message.split('\n')[0] });
    console.error(`✗ ${fig.name}: ${e.message.split("\n")[0]}`);
    // A bare selector timeout says nothing about *why* the page was not what
    // the figure expected - logged out, on the OTP gate, an error page. Record
    // where we actually were, and dump the screen next to the figures.
    try {
      console.error(`   at ${page.url()}`);
      const shot = join(OUT, '_failures', `${fig.name}.png`);
      mkdirSync(dirname(shot), { recursive: true });
      await page.screenshot({ path: shot });
      console.error(`   screen: ${shot}`);
    } catch {
      console.error('   (could not capture failure state)');
    }
    await page.evaluate(() => window.__annotate?.clear()).catch(() => {});
    if (fig.after) await fig.after(page).catch(() => {});
  }
}

await browser.close();

console.log(`\n${todo.length - failures.length}/${todo.length} figures captured into ${OUT}`);
if (failures.length) {
  console.error('failed:', failures.map((f) => f.name).join(', '));
  process.exitCode = 1;
}
