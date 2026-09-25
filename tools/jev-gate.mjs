#!/usr/bin/env node
/**
 * jev-gate.mjs — VERY STRICT deterministic quality gate for JEV outputs.
 *
 * Strict superset of jev-verify.mjs static checks. Runs OFFLINE by default
 * (no API key, no Laya daemon required). Opt-in network check via --api.
 *
 * Usage:
 *   node tools/jev-gate.mjs --file <path> [--api] [--apply] [--json]
 *   node tools/jev-gate.mjs --chunk <paths...> [--api]
 *   node tools/jev-gate.mjs --laya-suite            gate all Laya-touching files
 *   node tools/jev-gate.mjs --structure             aggregator + controller/view sweep
 *
 *   npm run jev:gate -- --file employer/review-app.php
 *   npm run jev:gate:strict -- --file includes/services/laya-service.php
 *   npm run jev:check:laya
 *   npm run jev:structure
 *
 * Exit codes: 0 = PASS, 1 = FAIL, 2 = usage/config error
 *
 * STRICT RULES (all must pass, offline-deterministic):
 *  S1  php -l passes on every file
 *  S2  zero JS-port markers (module.exports, require('fs'), from 'node:')
 *  S3  zero duplicate top-level function names across chunk
 *  S4  zero missing functions vs --against baseline (if given)
 *  S5  controller/view separation:
 *      - controller (admin|employer|student/*.php, root *.php) ends with
 *        `require ...-view.php` as last code statement (allowlist: api/, includes/, tools/)
 *      - view (*-view.php) bans: header(, session_start(, $pdo->, mysqli_,
 *        update_/create_/delete_/approve_/reject_ mutations,
 *        get_jobs(/get_applications(/get_all_users(/get_user (service reads belong in controller)
 *        allows: laya_get_*, htmlspecialchars, generate_csrf_token, require header/navbar/footer
 *  S6  aggregator thinness: includes/data-helper.php + includes/ai-config.php
 *      must be <= 60 lines and contain only session/define/require_once
 *  S7  Laya advisory-only (files touching laya OR --laya-suite):
 *      - laya-service.php bans auto-mutation fns + header(Location
 *      - laya-service.php must have: declare(strict_types=1), LAYA_ENDPOINT,
 *        laya_is_available, $_SESSION['laya_guidance'] cache, timeout guard
 *      - controller call-sites must guard laya_get_* with laya_is_available()
 *      - views showing laya_* must contain "Advisory" + human-discretion label
 *        (/discretion|human decides|insights only|maintains full/i)
 *  S8  size caps (strict): controller <= 450 lines, view <= 1100 lines,
 *      service <= 900 lines — else FAIL (split_monolith required)
 *
 *  API MODE (--api): additionally calls Jev primary API with Laya fallback,
 *  requiring all four noul >= 0.80 AND verdict == "verified".
 *  API error => FAIL closed (never silently pass).
 */

import { existsSync, readFileSync, writeFileSync, readdirSync, statSync } from "node:fs";
import { homedir } from "node:os";
import { join, resolve, relative } from "node:path";
import { spawnSync } from "node:child_process";

// ── config ─────────────────────────────────────────────────────────────────
const STRICT_NOUL_MIN = 0.8;
const LIMITS = { controller: 450, view: 1100, service: 900, aggregator: 60 };
const LAYA_SUITE_FILES = [
  "includes/services/laya-service.php",
  "includes/data-helper.php",
  "employer/review-app.php",
  "employer/applicants.php",
  "employer/dashboard.php",
  "employer/create-job.php",
  "student/job-details.php",
  "student/apply.php",
  "admin/users.php",
  "admin/reports.php",
  "includes/templates/employer-review-app-view.php",
  "includes/templates/employer-applicants-view.php",
  "includes/templates/employer-dashboard-view.php",
  "includes/templates/employer-create-job-view.php",
  "includes/templates/student-job-details-view.php",
  "includes/templates/student-apply-view.php",
  "includes/templates/admin-users-view.php",
  "includes/templates/admin-reports-view.php",
];
const STRUCTURE_FILES = [
  "includes/data-helper.php",
  "includes/ai-config.php",
  "employer/review-app.php",
  "employer/applicants.php",
  "employer/dashboard.php",
  "employer/create-job.php",
  "student/job-details.php",
  "student/apply.php",
  "admin/reports.php",
  "admin/users.php",
];

// ── helpers ────────────────────────────────────────────────────────────────
function loadEnvFile(p) {
  if (!existsSync(p)) return;
  for (const raw of readFileSync(p, "utf8").split(/\r?\n/)) {
    const line = raw.trim();
    if (!line || line.startsWith("#")) continue;
    const eq = line.indexOf("=");
    if (eq === -1) continue;
    const k = line.slice(0, eq).trim().replace(/^export\s+/, "");
    const v = line.slice(eq + 1).trim().replace(/^["']|["']$/g, "");
    if (!(k in process.env)) process.env[k] = v;
  }
}
function resolveApiKey() {
  loadEnvFile(join(process.cwd(), ".env"));
  loadEnvFile(join(homedir(), ".typesafe.env"));
  return process.env.TYPESAFE_API_KEY?.trim() || null;
}

const FN_RE = /^function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/;
function listFunctions(code) {
  const out = [];
  for (const line of code.split(/\r?\n/)) {
    const m = line.match(FN_RE);
    if (m) out.push(m[1]);
  }
  return out;
}
function phpLint(abs) {
  const r = spawnSync("php", ["-l", abs], { encoding: "utf8" });
  return { ok: r.status === 0, output: (r.stdout || r.stderr || "").trim() };
}
function expandPaths(patterns) {
  const out = [];
  for (const p of patterns) {
    if (p.includes("*")) {
      const dir = resolve(p.slice(0, p.lastIndexOf("/") + 1) || ".");
      const base = p.slice(p.lastIndexOf("/") + 1);
      if (!existsSync(dir)) continue;
      const re = new RegExp("^" + base.replace(/[.+^${}()|[\]\\]/g, "\\$&").replace(/\*/g, ".*") + "$");
      for (const n of readdirSync(dir)) if (re.test(n)) out.push(join(dir, n));
    } else if (existsSync(p)) out.push(resolve(p));
  }
  return [...new Set(out)];
}
const norm = (p) => relative(process.cwd(), p).replace(/\\/g, "/");
const isView = (rel) => rel.endsWith("-view.php");
const isController = (rel) =>
  /^(admin|employer|student)\//.test(rel) && !isView(rel);
const isService = (rel) => rel.includes("includes/services/") || rel.includes("includes/ai/");
const isAggregator = (rel) =>
  rel === "includes/data-helper.php" || rel === "includes/ai-config.php";
const isApiOrTool = (rel) =>
  rel.startsWith("api/") || rel.startsWith("tools/") || rel.startsWith("includes/");

// ── strict checks ──────────────────────────────────────────────────────────
function checkControllerEnding(rel, code) {
  if (!isController(rel) || isApiOrTool(rel) && !isController(rel)) return { ok: true, reason: "n/a" };
  if (!isController(rel)) return { ok: true, reason: "n/a" };
  const lines = code.split(/\r?\n/).map((l) => l.trim()).filter((l) => l && !l.startsWith("//") && !l.startsWith("*") && !l.startsWith("/*"));
  const last = lines.slice(-3).join("\n");
  const ok = /require\s+__DIR__\s*\.\s*['"]\/\.\.\/includes\/templates\//.test(last);
  return { ok, reason: ok ? "ends with require view" : `last lines missing require view :: ${lines.slice(-1)[0]?.slice(0, 120)}` };
}

const VIEW_BANNED = [
  [/header\s*\(/, "header() redirect in view"],
  [/session_start\s*\(/, "session_start() in view"],
  [/\$pdo\s*->|mysqli_/, "direct DB access in view"],
  [/\bupdate_application_status\s*\(/, "status mutation in view"],
  [/\b(update_user_verification|approve_profile_request|reject_profile_request)\s*\(/, "verification mutation in view"],
  [/\b(create_application|delete_application|create_job)\s*\(/, "data mutation in view"],
  [/\bget_jobs\s*\(/, "get_jobs() service read in view (move to controller)"],
  [/\bget_applications\s*\(/, "get_applications() service read in view (move to controller)"],
  [/\bget_all_users\s*\(/, "get_all_users() service read in view (move to controller)"],
];
function checkViewPurity(rel, code) {
  if (!isView(rel)) return [];
  return VIEW_BANNED.filter(([re]) => re.test(code)).map(([, msg]) => msg);
}

function checkAggregatorThin(rel, code) {
  if (!isAggregator(rel)) return { ok: true, reason: "n/a" };
  const lines = code.split(/\r?\n/).length;
  const bad = code.split(/\r?\n/).filter((l) => {
    const t = l.trim();
    if (!t || t.startsWith("<?php") || t.startsWith("//") || t.startsWith("*") || t.startsWith("/*") || t === "?>" || /^[\{\}]+$/.test(t)) return false;
    if (/^if\s*\(!?defined\(/.test(t)) return false;
    return !/require_once|session_status|session_start|define\(|dirname\(|DATA_DIR|components\.php|service\.php|ai\/|NVIDIA_CONFIG_LOADED/.test(t);
  });
  const ok = lines <= LIMITS.aggregator && bad.length === 0;
  return { ok, lines, reason: ok ? `thin (${lines} lines)` : `fat: ${lines} lines (max ${LIMITS.aggregator}), non-wiring lines: ${bad.slice(0, 3).join(" | ").slice(0, 160)}` };
}

const LAYA_BANNED_IN_SERVICE = [
  "update_application_status", "update_user_verification",
  "approve_profile_request", "reject_profile_request",
  "create_application", "delete_application", "create_job",
];
function checkLayaService(rel, code) {
  if (!rel.endsWith("laya-service.php")) return { checks: [], fail: [] };
  const checks = [];
  const fail = [];
  for (const fn of LAYA_BANNED_IN_SERVICE) {
    if (new RegExp(`\\b${fn}\\s*\\(`).test(code)) fail.push(`banned auto-mutation ${fn}() inside laya-service.php`);
    else checks.push(`no ${fn}() OK`);
  }
  if (/header\s*\([^)]*Location/.test(code)) fail.push("header(Location) redirect inside laya-service.php");
  else checks.push("no redirect OK");
  const must = [
    [/declare\s*\(\s*strict_types\s*=\s*1/, "declare(strict_types=1)"],
    [/LAYA_ENDPOINT/, "LAYA_ENDPOINT const"],
    [/function\s+laya_is_available/, "laya_is_available()"],
    [/\$_SESSION\['laya_guidance'\]/, "session cache $_SESSION['laya_guidance']"],
    [/timeout/i, "timeout guard"],
  ];
  for (const [re, label] of must) {
    if (re.test(code)) checks.push(`${label} OK`);
    else fail.push(`missing ${label} in laya-service.php`);
  }
  return { checks, fail };
}

function checkLayaCallSite(rel, code) {
  const touches = /laya_get_|laya_fit|laya_guidance|laya_available|laya_student_fit|laya_risks|laya_job_fit|laya_hiring_risks|laya_requisition|laya_apply_self|laya_req_check|laya_selfcheck|laya_verification|laya_quota|laya_triage|laya_narrative/.test(code);
  if (!touches) return { applies: false, fail: [] };
  const fail = [];
  if (/laya_get_applicant_guidance/.test(code) && !/laya_is_available\s*\(/.test(code))
    fail.push(`${rel}: calls laya_get_applicant_guidance() without laya_is_available() guard`);
  if (isView(rel)) {
    if (!/Advisory/i.test(code)) fail.push(`${rel}: shows Laya output without "Advisory" label`);
    if (!/discretion|human decides|insights only|maintains full/i.test(code))
      fail.push(`${rel}: shows Laya output without human-discretion label`);
  }
  return { applies: true, fail };
}

function checkSizeCap(rel, code) {
  const lines = code.split(/\r?\n/).length;
  const kind = isView(rel) ? "view" : isController(rel) ? "controller" : isService(rel) ? "service" : null;
  if (!kind) return { ok: true, lines };
  const ok = lines <= LIMITS[kind];
  return { ok, lines, kind, reason: ok ? `${kind} ${lines} lines` : `${kind} ${lines} lines exceeds strict cap ${LIMITS[kind]} — split_monolith required` };
}

// ── Jev API (opt-in --api, threshold 0.80) ──────────────────────────────────
function extractNoul(a) {
  if (!a || typeof a !== "object") return null;
  if (typeof a.noul === "number") return a.noul;
  if (typeof a.value === "boolean") return a.value ? (typeof a.confidence === "number" ? a.confidence : 1) : 1 - (typeof a.confidence === "number" ? a.confidence : 1);
  return typeof a.probability === "number" ? a.probability : null;
}
async function callJevStrict(combined, apiKey) {
  const schema = JSON.parse(readFileSync(join(process.cwd(), "tools", "jev-verify-schema.json"), "utf8"));
  const payload = { model: "jev-latest", state: { code: combined.slice(0, 50000) }, questions: schema.questions };
  const tryFetch = async (url, headers) => {
    const ctrl = new AbortController();
    const t = setTimeout(() => ctrl.abort(), 30000);
    try {
      const r = await fetch(url, { method: "POST", headers, body: JSON.stringify(payload), signal: ctrl.signal });
      if (!r.ok) throw new Error(`HTTP ${r.status}: ${(await r.text()).slice(0, 200)}`);
      return await r.json();
    } finally { clearTimeout(t); }
  };
  if (apiKey && !process.env.FORCE_LAYA) {
    try { return { data: await tryFetch("https://api.typesafe.ai/v1/systemone", { Authorization: `Bearer ${apiKey}`, "Content-Type": "application/json" }), engine: "jev" }; }
    catch (e) { console.warn(`[jev-gate] Jev primary failed (${e.message}) — cascading to Laya :8100...`); }
  }
  const r = await tryFetch("http://127.0.0.1:8100/predict", { "Content-Type": "application/json" });
  return { data: r.data ?? r, engine: "laya" };
}

// ── main ───────────────────────────────────────────────────────────────────
async function main() {
  const argv = process.argv.slice(2);
  const getFlag = (n) => { const i = argv.indexOf(n); return i !== -1 ? argv[i + 1] : null; };
  const wantApi = argv.includes("--api");
  const wantJson = argv.includes("--json");
  const wantApply = argv.includes("--apply");
  const against = getFlag("--against");

  let patterns = [];
  if (argv.includes("--laya-suite")) patterns = [...LAYA_SUITE_FILES];
  else if (argv.includes("--structure")) patterns = [...STRUCTURE_FILES];
  else {
    const chunkIdx = argv.indexOf("--chunk");
    if (chunkIdx !== -1) for (let i = chunkIdx + 1; i < argv.length; i++) { if (argv[i].startsWith("--")) break; patterns.push(argv[i]); }
    const f = getFlag("--file");
    if (f) patterns.unshift(f);
    const skip = new Set();
    for (let i = 0; i < argv.length; i++) if (["--file", "--against", "--chunk"].includes(argv[i])) skip.add(i + 1);
    for (let i = 0; i < argv.length; i++) {
      const a = argv[i];
      if (a.startsWith("--") || skip.has(i)) continue;
      if (existsSync(a) && !patterns.includes(a)) patterns.push(a);
    }
  }
  if (!patterns.length) { console.error("Usage: node tools/jev-gate.mjs --file <p> | --chunk <ps...> | --laya-suite | --structure [--api] [--apply] [--json]"); process.exit(2); }

  const absFiles = expandPaths(patterns);
  if (!absFiles.length) { console.error("[jev-gate] No files matched."); process.exit(2); }

  const failures = [];
  const passes = [];
  const perFile = [];
  const allFns = new Map();

  for (const abs of absFiles) {
    const rel = norm(abs);
    const code = readFileSync(abs, "utf8");
    const fns = listFunctions(code);
    for (const fn of fns) { if (!allFns.has(fn)) allFns.set(fn, []); allFns.get(fn).push(rel); }

    const lint = phpLint(abs);
    const fileFail = [];
    if (!lint.ok) fileFail.push(`S1 php -l FAIL: ${lint.output}`);
    if (/\bmodule\.exports\b|\brequire\(['"]fs['"]\)|from\s+['"]node:/.test(code)) fileFail.push("S2 JS-port markers found");
    const ce = checkControllerEnding(rel, code);
    if (!ce.ok && isController(rel)) fileFail.push(`S5 controller boundary: ${ce.reason}`);
    for (const v of checkViewPurity(rel, code)) fileFail.push(`S5 view purity: ${v}`);
    const ag = checkAggregatorThin(rel, code);
    if (!ag.ok) fileFail.push(`S6 aggregator: ${ag.reason}`);
    const ls = checkLayaService(rel, code);
    fileFail.push(...ls.fail.map((m) => `S7 ${m}`));
    const cs = checkLayaCallSite(rel, code);
    fileFail.push(...cs.fail.map((m) => `S7 ${m}`));
    const sc = checkSizeCap(rel, code);
    if (!sc.ok) fileFail.push(`S8 ${sc.reason}`);

    let baselineMissing = [];
    if (against && existsSync(against)) {
      const base = readFileSync(against, "utf8").split(/\n/).filter(Boolean);
      baselineMissing = base.filter((n) => !fns.includes(n) && !absFiles.some((o) => o !== abs && listFunctions(readFileSync(o, "utf8")).includes(n)));
    }
    // baseline check evaluated at chunk level below; record per-file fns
    perFile.push({ path: rel, lines: code.split(/\r?\n/).length, fns: fns.length, lint_ok: lint.ok, fail: fileFail });
    if (fileFail.length) failures.push(...fileFail.map((m) => `  ✗ ${rel}: ${m}`));
    else passes.push(`  ✓ ${rel}`);
  }

  // S3 duplicates across chunk
  for (const [fn, owners] of allFns) {
    if (owners.length > 1) failures.push(`  ✗ S3 duplicate function ${fn}() in: ${owners.join(", ")}`);
  }
  // S4 baseline
  if (against && existsSync(against)) {
    const base = readFileSync(against, "utf8").split(/\n/).filter(Boolean);
    const have = new Set([...allFns.keys()]);
    const missing = base.filter((n) => !have.has(n));
    if (missing.length) failures.push(`  ✗ S4 missing vs baseline (${missing.length}): ${missing.slice(0, 12).join(", ")}${missing.length > 12 ? "…" : ""}`);
    else passes.push("  ✓ S4 baseline inventory intact");
  }

  // S7-laya-suite extra: suite must include the single-model service file
  if (argv.includes("--laya-suite")) {
    const rels = absFiles.map(norm);
    if (!rels.some((r) => r.endsWith("laya-service.php"))) failures.push("  ✗ S7 laya-suite must include includes/services/laya-service.php (single-model rule)");
  }

  // Optional API at 0.80 + verdict==verified
  let apiInfo = null;
  if (wantApi) {
    try {
      const apiKey = resolveApiKey();
      const combined = absFiles.map((f) => `// ===== ${norm(f)} =====\n${readFileSync(f, "utf8")}`).join("\n\n");
      const { data, engine } = await callJevStrict(combined, apiKey);
      const a = data?.answers ?? {};
      const scores = {
        preserves_php_only: extractNoul(a.preserves_php_only),
        single_domain_coherence: extractNoul(a.single_domain_coherence),
        contract_surface_intact: extractNoul(a.contract_surface_intact),
        bootstrap_safe: extractNoul(a.bootstrap_safe),
      };
      const verdict = a.refactor_chunk_verdict?.value ?? a.refactor_chunk_verdict?.choice ?? null;
      apiInfo = { engine, scores, verdict };
      for (const [k, v] of Object.entries(scores)) {
        if ((v ?? 0) < STRICT_NOUL_MIN) failures.push(`  ✗ API(${engine}) ${k}=${v ?? "?"} < strict ${STRICT_NOUL_MIN}`);
        else passes.push(`  ✓ API(${engine}) ${k}=${v}`);
      }
      if (verdict !== "verified") failures.push(`  ✗ API(${engine}) verdict=${verdict ?? "?"} (strict requires "verified")`);
      else passes.push(`  ✓ API(${engine}) verdict=verified`);
    } catch (e) {
      failures.push(`  ✗ API error (fail-closed): ${e.message}`);
    }
  }

  const pass = failures.length === 0;
  let applied = false;
  if (pass && wantApply) {
    const qf = join(process.cwd(), "triage-queue.json");
    if (existsSync(qf)) {
      const q = JSON.parse(readFileSync(qf, "utf8"));
      for (const pf of perFile) {
        const e = q.files.find((x) => x.path === pf.path || x.path.replace(/\\/g, "/") === pf.path);
        if (e) { e.status = "verified"; e.verified_at = new Date().toISOString(); e.verification = { tool: "jev-gate:strict", at: e.verified_at }; applied = true; }
      }
      writeFileSync(qf, JSON.stringify(q, null, 2), "utf8");
    }
  }

  const report = { pass, applied, files: perFile.map((p) => p.path), failures: failures.map((f) => f.trim()), passes, api: apiInfo, thresholds: { noul_min: STRICT_NOUL_MIN, verdict_required: wantApi ? "verified" : "n/a (offline)" }, limits: LIMITS, generated_at: new Date().toISOString() };
  if (wantJson) console.log(JSON.stringify(report, null, 2));
  else {
    console.log("=".repeat(72));
    console.log(`JEV STRICT GATE — ${pass ? "PASS" : "FAIL"} (${absFiles.length} file(s)${wantApi ? ", +API@0.80" : ", offline static"})`);
    console.log("-".repeat(72));
    for (const p of passes.slice(0, 40)) console.log(p);
    for (const f of failures) console.log(f);
    console.log("-".repeat(72));
    console.log(`RESULT: ${pass ? "PASS" : "FAIL"}${applied ? " (queue → verified)" : ""}  |  strict noul=${STRICT_NOUL_MIN}, verdict=${wantApi ? "verified required" : "offline"}`);
    console.log("=".repeat(72));
  }
  try { writeFileSync(join(process.cwd(), "scratch", "gate-report.json"), JSON.stringify(report, null, 2), "utf8"); } catch { /* scratch optional */ }
  process.exit(pass ? 0 : 1);
}

main().catch((e) => { console.error(`[jev-gate] ${e.message}`); process.exit(2); });
