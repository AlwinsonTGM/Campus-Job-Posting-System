/**
 * Registration wizard driver.
 *
 * register-view.php is a 3-step wizard: fields in inactive panes are hidden,
 * so a plain page.fill() cannot reach them. This drives the real wizard
 * (fill step -> click Next -> ... -> submit) exactly as a user would, so the
 * wizard's own validation and the server-side validation both run.
 *
 * Every write is verified, because a silent fill failure is indistinguishable
 * from a product bug further down the line.
 */
import { sleep } from './harness.mjs';

export const SEL = {
  firstName: '#reg-first-name',
  lastName: '#reg-last-name',
  repName: '#reg-name',
  email: '#reg-email',
  phone: '#reg-phone',
  studentId: '#student_id',
  department: '#department-select',
  otherInstitute: '#other_institute',
  course: '#course-select',
  yearLevel: '#year_level',
  sex: '#reg-sex',
  birthdate: '#reg-birthdate',
  password: '#password',
  confirmPassword: '#confirm_password',
  terms: '#termsCheck',
  btnNext: '.btn-step-next',
  btnSubmit: '#btn-submit-registration',
  stepError: '#step-error-alert',
  stepErrorText: '#step-error-message',
  proof: '#reg-student-proof',
};

const PNG_1x1 = Buffer.from(
  'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg==',
  'base64');

/**
 * Any error the page is showing.
 *
 * #step-error-message holds the CLIENT-side wizard text and ships with a
 * default message even when hidden, so it must never be read as a real error.
 * The server's message is rendered separately in #server-error-alert.
 */
export async function stepError(page) {
  return page.evaluate(() => {
    const bits = [];

    const server = document.getElementById('server-error-alert');
    if (server) bits.push('server: ' + server.innerText.replace(/\s+/g, ' ').trim());

    const box = document.getElementById('step-error-alert');
    if (box && !box.classList.contains('d-none')) {
      bits.push('client: ' + (document.getElementById('step-error-message')?.innerText || '').replace(/\s+/g, ' ').trim());
    }

    return bits.join(' | ');
  }).catch(() => '');
}

/** Fill and CONFIRM the value landed; retries once, returns whether it stuck. */
export async function fillVerified(page, selector, value, attempts = 3) {
  const el = page.locator(selector).first();
  if (!(await el.count())) return false;
  for (let i = 0; i < attempts; i++) {
    await el.fill(value).catch(() => {});
    await sleep(120);
    const got = await el.inputValue().catch(() => null);
    if (got === value) return true;
    // field may have been re-rendered; re-resolve and retry
    await sleep(200);
  }
  return false;
}

/** Choose the first genuine option of a <select> (skips placeholder/empty). */
export async function pickFirstOption(page, selector, { skip = [] } = {}) {
  const el = page.locator(selector).first();
  if (!(await el.count())) return null;
  for (const o of await el.locator('option').all()) {
    const v = (await o.getAttribute('value')) ?? '';
    const t = ((await o.innerText().catch(() => '')) || '').trim();
    if (v.trim() === '') continue;
    if (!t) continue;
    if (/^(select|choose|--|please)/i.test(t)) continue;
    if (skip.includes(v)) continue;
    await el.selectOption(v);
    const got = await el.inputValue().catch(() => null);
    if (got === v) return v;
  }
  return null;
}

/** Attach the mandatory COR / Student ID document and verify it registered. */
export async function attachProof(page) {
  const input = page.locator(SEL.proof).first();
  if (!(await input.count())) return false;
  await input.setInputFiles({ name: 'qa-cor.png', mimeType: 'image/png', buffer: PNG_1x1 }).catch(() => {});
  await sleep(200);
  let n = await page.evaluate((s) => document.querySelector(s)?.files?.length ?? 0, SEL.proof).catch(() => 0);
  if (n === 1) return true;

  // Fallback: build the FileList in-page (setInputFiles can be refused on
  // heavily CSS-hidden inputs).
  await page.evaluate((s) => {
    const inp = document.querySelector(s);
    const bytes = Uint8Array.from(atob('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg=='), (c) => c.charCodeAt(0));
    const dt = new DataTransfer();
    dt.items.add(new File([bytes], 'qa-cor.png', { type: 'image/png' }));
    inp.files = dt.files;
    inp.dispatchEvent(new Event('change', { bubbles: true }));
  }, SEL.proof).catch(() => {});
  await sleep(200);
  n = await page.evaluate((s) => document.querySelector(s)?.files?.length ?? 0, SEL.proof).catch(() => 0);
  return n === 1;
}

/** Wait for the wizard to settle on a given pane. */
async function waitPane(page, n, timeout = 15000) {
  await page.waitForFunction(
    (step) => document.getElementById(`step-pane-${step}`)?.classList.contains('is-visible'),
    n, { timeout },
  ).catch(() => {});
}

/**
 * Click Next on the CURRENTLY VISIBLE pane.
 * Each pane has its own .btn-step-next, and the earlier panes' buttons stay in
 * the DOM but hidden, so an unscoped `.first()` targets an invisible element.
 */
async function clickNext(page) {
  const scoped = page.locator('.reg-step-pane.is-visible .btn-step-next').first();
  if (await scoped.count()) {
    await scoped.click({ timeout: 10000 }).catch(() => {});
  } else {
    const all = page.locator('.btn-step-next');
    for (let i = 0; i < await all.count(); i++) {
      if (await all.nth(i).isVisible().catch(() => false)) {
        await all.nth(i).click({ timeout: 10000 }).catch(() => {});
        break;
      }
    }
  }
  await sleep(400);
}

/**
 * Complete the student registration wizard and submit.
 * Returns diagnostics rather than throwing, so callers can record findings.
 */
export async function completeStudentRegistration(page, { email, stamp, studentId, password = 'QaProbe!2345' } = {}) {
  const sid = studentId || `QA-${stamp}`;
  const out = {
    email, studentId: sid,
    step1Ok: false, step1Msg: '',
    step2Ok: false, step2Msg: '',
    fieldsStuck: {}, proofAttached: false,
    submitted: false, urlAfterSubmit: '',
  };

  await page.goto('/register.php', { waitUntil: 'domcontentloaded' });
  await waitPane(page, 1);

  // ---- Step 1: identity -------------------------------------------------
  out.fieldsStuck.firstName = await fillVerified(page, SEL.firstName, 'QA');
  out.fieldsStuck.lastName = await fillVerified(page, SEL.lastName, 'Probe');
  out.fieldsStuck.email = await fillVerified(page, SEL.email, email);
  out.fieldsStuck.phone = await fillVerified(page, SEL.phone, '09171234567');

  await clickNext(page);
  await waitPane(page, 2);

  out.step1Ok = await page.evaluate(() =>
    document.getElementById('step-pane-2')?.classList.contains('is-visible') ?? false);
  if (!out.step1Ok) out.step1Msg = (await stepError(page)) || 'step 2 never became visible';

  // ---- Step 2: academic profile ----------------------------------------
  if (out.step1Ok) {
    out.fieldsStuck.studentId = await fillVerified(page, SEL.studentId, sid);

    const dept = await pickFirstOption(page, SEL.department, { skip: ['Other Institute / Outsider'] });
    await sleep(700); // course options are loaded per department
    const course = await pickFirstOption(page, SEL.course);
    const year = await pickFirstOption(page, SEL.yearLevel);
    const sex = await pickFirstOption(page, SEL.sex);

    const bd = page.locator(SEL.birthdate).first();
    if (await bd.count()) await bd.fill('2003-05-14').catch(() => {});
    out.fieldsStuck.birthdate = (await bd.inputValue().catch(() => '')) === '2003-05-14';

    out.picked = { dept, course, year, sex };

    await clickNext(page);
    await waitPane(page, 3);

    out.step2Ok = await page.evaluate(() =>
      document.getElementById('step-pane-3')?.classList.contains('is-visible') ?? false);
    if (!out.step2Ok) out.step2Msg = (await stepError(page)) || `step 3 never became visible (dept=${dept})`;
  }

  // ---- Step 3: credentials + submit ------------------------------------
  if (out.step2Ok) {
    out.fieldsStuck.password = await fillVerified(page, SEL.password, password);
    out.fieldsStuck.confirmPassword = await fillVerified(page, SEL.confirmPassword, password);

    out.proofAttached = await attachProof(page);

    const terms = page.locator(SEL.terms).first();
    if (await terms.count()) await terms.check({ force: true }).catch(() => {});
    out.fieldsStuck.terms = await terms.isChecked().catch(() => false);

    // Confirm the whole form is submittable before clicking, so a failure is
    // attributed to the app rather than to a half-filled form.
    out.formValid = await page.evaluate(() =>
      document.getElementById('register-form')?.checkValidity() ?? false).catch(() => false);

    await page.locator(SEL.btnSubmit).first().click({ timeout: 15000 }).catch(() => {});
    await sleep(2500);
    out.submitted = true;
  }

  out.urlAfterSubmit = page.url();
  out.stepErrorAfterSubmit = await stepError(page);
  return out;
}
