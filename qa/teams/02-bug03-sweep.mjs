/**
 * BUG-03 characterization — array query parameters cause uncaught PHP TypeError fatals.
 *
 * The project previously hardened ONLY /api/search-jobs.php for this exact class
 * (see docs/reports/QA_TEST_REPORT.md SEC-08). This sweeps every other route
 * that consumes a scalar $_GET value to establish the real blast radius.
 */
import { Results, httpGet, sleep } from '../lib/harness.mjs';
import * as path from 'node:path';

const ART = path.join(import.meta.dirname, '..', 'artifacts', 'bug03');
const res = new Results('bug03-array-param-fatal');

const FATAL = /<b>Fatal error<\/b>[\s\S]{0,400}/i;
const TYPEERR = /Uncaught TypeError: ([^<\n]{0,220})/;

/** [path, needsAuthRole|null] — anonymous probes hit the guard first, so we note that. */
const TARGETS = [
  ['/student/job-details.php?id[]=1', null],
  ['/employer/edit-job.php?id[]=1', 'employer'],
  ['/view-resume.php?user_id[]=1', 'student'],
  ['/student/apply.php?job_id[]=1', 'student'],
  ['/update-detail.php?id[]=1', null],
  ['/student/my-applications.php?withdraw[]=1', 'student'],
  ['/employer/review-app.php?id[]=1', 'employer'],
  ['/employer/applicants.php?job_id[]=1', 'employer'],
  ['/admin/users.php?ver_status[]=x', 'admin'],
  ['/admin/categories.php?id[]=1', 'admin'],
  ['/api/search-jobs.php?q[]=a', null],
  ['/api/search-jobs.php?job_type[]=a', null],
  ['/student/jobs.php?job_type[]=a', null],
];

console.log('Probing routes with array query parameters (authenticated where required)...\n');

const results = [];
for (const [target, role] of TARGETS) {
  // Use a plain fetch with a session cookie obtained via the demo hook.
  let cookie = '';
  if (role) {
    const login = await fetch(`http://127.0.0.1:8099/login.php?demo=${role}`, { redirect: 'manual' });
    const sc = login.headers.getSetCookie?.() ?? [];
    cookie = sc.map((c) => c.split(';')[0]).join('; ');
    if (!cookie) {
      const raw = login.headers.get('set-cookie') || '';
      cookie = raw.split(';')[0];
    }
  }

  const r = await httpGet(target, cookie ? { headers: { cookie } } : {});
  const fatal = FATAL.test(r.text);
  const m = r.text.match(TYPEERR);
  results.push({ target, role, status: r.status, fatal, message: m ? m[1].trim() : null, bytes: r.text.length });

  const tag = fatal ? 'FATAL' : `HTTP ${r.status}`;
  console.log(`  ${tag.padEnd(8)} ${target}${role ? `  [${role}]` : ''}`);
  if (fatal) console.log(`           ${m ? m[1].trim() : '(unparsed fatal)'}`);
}

const fatals = results.filter((r) => r.fatal);
const clean = results.filter((r) => !r.fatal);

res.check('array params are handled without a fatal on every route', fatals.length === 0,
  `${fatals.length}/${results.length} routes crashed`);

console.log(`\n=== SUMMARY: ${fatals.length} of ${results.length} routes crash on an array param ===`);
for (const f of fatals) console.log(`  - ${f.target}  [${f.role || 'anonymous'}]`);

if (fatals.length) {
  res.finding({
    title: 'Array query parameters cause uncaught PHP TypeError fatals across multiple routes',
    severity: 'high',
    route: fatals.map((f) => f.target.split('?')[0]).join(', '),
    role: 'any (some unauthenticated)',
    steps: [
      'Append [] to any id-like query parameter, e.g. /student/job-details.php?id[]=1',
      'Observe HTTP 200 containing a raw PHP fatal error instead of a clean response.',
      'Repeat on the routes listed below.',
    ],
    expected: 'Invalid input is coerced or rejected with a clean 4xx/redirect.',
    actual: `${fatals.length} routes emit an uncaught TypeError and disclose absolute server paths:\n` +
      fatals.map((f) => `  ${f.target} -> ${f.message}`).join('\n'),
    evidence: [],
  });
}

// Persist the raw sweep for the report
const fs = await import('node:fs');
fs.mkdirSync(ART, { recursive: true });
fs.writeFileSync(path.join(ART, 'array-param-sweep.json'), JSON.stringify(results, null, 2));
res.save(ART);
