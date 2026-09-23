#!/usr/bin/env node
/**
 * jev-verify.mjs — Structured refactor verification via TypeSafe System One (Jev).
 *
 * Combines static contract checks (PHP syntax, function inventory, PHP-only guardrail)
 * with a Jev-API verification schema (tools/jev-verify-schema.json).
 *
 * Usage:
 *   node tools/jev-verify.mjs --file includes/data-helper.php
 *   node tools/jev-verify.mjs --file includes/data-helper.php --against scratch/functions-before.txt
 *   node tools/jev-verify.mjs --chunk includes/data-helper.php includes/services/*.php
 *   node tools/jev-verify.mjs --file <path> --apply          mark verified in triage-queue.json on PASS
 *   node tools/jev-verify.mjs --file <path> --json           raw Jev JSON + report
 *   node tools/jev-verify.mjs --file <path> --dry-run        static checks only, skip API
 *
 * Exit codes: 0 = PASS, 1 = FAIL, 2 = usage/config error
 */

import { existsSync, readFileSync, writeFileSync, readdirSync, statSync } from "node:fs";
import { homedir } from "node:os";
import { join, resolve, relative, extname } from "node:path";
import { spawnSync } from "node:child_process";

const ENDPOINT = "https://api.typesafe.ai/v1/systemone";
const MODEL = "jev-latest";
const MAX_CHARS = 50000;
const RETRYABLE = /429|529|ECONNRESET|fetch failed/i;

// ── env ──────────────────────────────────────────────────────────────────────
function loadEnvFile(path) {
  if (!existsSync(path)) return;
  for (const raw of readFileSync(path, "utf8").split(/\r?\n/)) {
    const line = raw.trim();
    if (!line || line.startsWith("#")) continue;
    const eq = line.indexOf("=");
    if (eq === -1) continue;
    const key = line.slice(0, eq).trim().replace(/^export\s+/, "");
    const val = line.slice(eq + 1).trim().replace(/^["']|["']$/g, "");
    if (!(key in process.env)) process.env[key] = val;
  }
}

function resolveApiKey() {
  loadEnvFile(join(process.cwd(), ".env"));
  loadEnvFile(join(homedir(), ".typesafe.env"));
  const key = process.env.TYPESAFE_API_KEY?.trim();
  if (!key) throw new Error("TYPESAFE_API_KEY not found in ~/.typesafe.env or .env");
  return key;
}

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

async function withRetry(fn, attempts = 4, delay = 800) {
  try {
    return await fn();
  } catch (err) {
    if (attempts <= 1 || !RETRYABLE.test(String(err?.message))) throw err;
    await sleep(delay);
    return withRetry(fn, attempts - 1, delay * 2);
  }
}

async function fetchJev(payload, apiKey) {
  const ctrl = new AbortController();
  const timer = setTimeout(() => ctrl.abort(), 30000);
  try {
    const res = await fetch(ENDPOINT, {
      method: "POST",
      headers: { Authorization: `Bearer ${apiKey}`, "Content-Type": "application/json" },
      body: JSON.stringify(payload),
      signal: ctrl.signal,
    });
    if (res.status === 429 || res.status === 529) throw new Error(`HTTP ${res.status} rate-limited`);
    if (!res.ok) throw new Error(`HTTP ${res.status}: ${(await res.text()).slice(0, 400)}`);
    return await res.json();
  } catch (err) {
    if (err?.name === "AbortError") throw new Error("Request timed out after 30s");
    throw err;
  } finally {
    clearTimeout(timer);
  }
}

// ── answer extractors (same conventions as jev-triage) ───────────────────────
function extractNoul(ans) {
  if (!ans || typeof ans !== "object") return null;
  if (typeof ans.noul === "number") return ans.noul;
  if (typeof ans.value === "boolean") {
    const c = typeof ans.confidence === "number" ? ans.confidence : 1;
    return ans.value ? c : 1 - c;
  }
  return typeof ans.probability === "number" ? ans.probability : null;
}

function extractChoice(ans) {
  return ans?.value ?? ans?.choice ?? ans?.answer ?? null;
}

function parseVerifyAnswers(data) {
  const a = data?.answers ?? {};
  return {
    preserves_php_only: extractNoul(a.preserves_php_only),
    single_domain_coherence: extractNoul(a.single_domain_coherence),
    contract_surface_intact: extractNoul(a.contract_surface_intact),
    bootstrap_safe: extractNoul(a.bootstrap_safe),
    verdict: extractChoice(a.refactor_chunk_verdict),
    residual_debt: extractChoice(a.residual_debt_notes),
    usage: data?.usage ?? null,
  };
}

// ── static checks ───────────────────────────────────────────────────────────
const FN_RE = /^function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/;

function listFunctions(code) {
  const names = [];
  for (const line of code.split(/\r?\n/)) {
    const m = line.match(FN_RE);
    if (m) names.push(m[1]);
  }
  return names;
}

function phpLint(absPath) {
  const r = spawnSync("php", ["-l", absPath], { encoding: "utf8" });
  const ok = r.status === 0;
  return { ok, output: (r.stdout || r.stderr || "").trim() };
}

function expandPaths(patterns) {
  const out = [];
  for (const p of patterns) {
    if (p.includes("*")) {
      const dir = resolve(p.slice(0, p.lastIndexOf("/") + 1) || ".");
      const base = p.slice(p.lastIndexOf("/") + 1);
      if (!existsSync(dir)) continue;
      const re = new RegExp(
        "^" + base.replace(/[.+^${}()|[\]\\]/g, "\\$&").replace(/\*/g, ".*") + "$",
      );
      for (const name of readdirSync(dir)) {
        if (re.test(name)) out.push(join(dir, name));
      }
    } else if (existsSync(p)) {
      out.push(resolve(p));
    }
  }
  return [...new Set(out)];
}

function staticChecks(absFiles, againstListPath) {
  const results = [];

  for (const abs of absFiles) {
    const rel = relative(process.cwd(), abs).replace(/\\/g, "/");
    const code = readFileSync(abs, "utf8");
    const lint = phpLint(abs);
    const fns = listFunctions(code);

    // PHP-only: flag obvious JS port markers in PHP service/bootstrap files
    const jsPortHits = [];
    if (/\bmodule\.exports\b|\brequire\(['"]fs['"]\)|from\s+['"]node:/.test(code)) {
      jsPortHits.push("node_js_module_syntax");
    }

    results.push({
      path: rel,
      size_kb: Math.round((statSync(abs).size / 1024) * 10) / 10,
      php_lint_ok: lint.ok,
      php_lint: lint.output,
      function_count: fns.length,
      functions: fns,
      js_port_markers: jsPortHits,
      code,
    });
  }

  // Aggregate function inventory vs baseline
  const afterSet = new Set(results.flatMap((r) => r.functions));
  let baseline = null;
  let missing = [];
  if (againstListPath && existsSync(againstListPath)) {
    baseline = readFileSync(againstListPath, "utf8").split(/\n/).filter(Boolean);
    missing = baseline.filter((n) => !afterSet.has(n));
  }

  const dupes = [];
  const seen = new Set();
  for (const r of results) {
    for (const fn of r.functions) {
      if (seen.has(fn)) dupes.push(fn);
      seen.add(fn);
    }
  }

  const allLintOk = results.every((r) => r.php_lint_ok);
  const anyJsPort = results.some((r) => r.js_port_markers.length > 0);

  return {
    files: results.map(({ code, ...rest }) => rest),
    baseline_count: baseline ? baseline.length : null,
    after_count: afterSet.size,
    missing_functions: missing,
    duplicate_functions: [...new Set(dupes)],
    php_lint_all_ok: allLintOk,
    js_port_markers_found: anyJsPort,
    static_pass: allLintOk && missing.length === 0 && dupes.length === 0 && !anyJsPort,
  };
}

// ── report ──────────────────────────────────────────────────────────────────
const r2 = (n) => (n != null ? Math.round(n * 100) / 100 : null);

function pct(n) {
  if (n == null) return "?";
  return `${Math.round(n * 100)}%`;
}

function printReport(report) {
  const line = (s = "") => console.log(s);
  line("=".repeat(72));
  line("JEV VERIFY REPORT");
  line(`Mode    : ${report.dry_run ? "static-only (dry-run)" : "static + Jev API"}`);
  line(`Files   : ${report.files.join(", ")}`);
  line("-".repeat(72));

  line("STATIC CHECKS");
  for (const f of report.static.files) {
    line(
      `  ${f.php_lint_ok ? "✓" : "✗"} php -l  ${f.path.padEnd(42)} ${String(f.size_kb).padStart(6)} KB  fns=${f.function_count}`,
    );
    if (!f.php_lint_ok) line(`      ${f.php_lint}`);
    if (f.js_port_markers.length) line(`      JS markers: ${f.js_port_markers.join(", ")}`);
  }
  if (report.static.baseline_count != null) {
    line(
      `  inventory: baseline=${report.static.baseline_count} after=${report.static.after_count} missing=${report.static.missing_functions.length} dupes=${report.static.duplicate_functions.length}`,
    );
    if (report.static.missing_functions.length) {
      line(`  MISSING: ${report.static.missing_functions.join(", ")}`);
    }
  }
  line(`  static_pass: ${report.static.static_pass ? "YES" : "NO"}`);
  line("-".repeat(72));

  if (report.dry_run) {
    line("JEV API: skipped (--dry-run)");
  } else if (report.jev_error) {
    line(`JEV API ERROR: ${report.jev_error}`);
  } else if (report.jev) {
    const j = report.jev;
    line("JEV API SCORES (verification schema)");
    line(`  preserves_php_only       ${pct(j.preserves_php_only)}  ${j.preserves_php_only >= 0.6 ? "PASS" : "FAIL"}`);
    line(`  single_domain_coherence  ${pct(j.single_domain_coherence)}  ${j.single_domain_coherence >= 0.6 ? "PASS" : "FAIL"}`);
    line(`  contract_surface_intact  ${pct(j.contract_surface_intact)}  ${j.contract_surface_intact >= 0.6 ? "PASS" : "FAIL"}`);
    line(`  bootstrap_safe           ${pct(j.bootstrap_safe)}  ${j.bootstrap_safe >= 0.6 ? "PASS" : "FAIL"}`);
    line(`  verdict                  ${j.verdict ?? "?"} (advisory)`);
    line(`  residual_debt            ${j.residual_debt ?? "?"} (advisory)`);
    if (j.usage) {
      line(`  tokens                   ${j.usage.input_tokens ?? 0} in / ${j.usage.output_tokens ?? 0} out`);
    }
  }
  line("-".repeat(72));
  line(`RESULT: ${report.pass ? "PASS" : "FAIL"}${report.applied ? " (queue → verified)" : ""}`);
  line("=".repeat(72));
}

// ── queue apply ─────────────────────────────────────────────────────────────
function applyVerified(primaryRel) {
  const QUEUE_FILE = join(process.cwd(), "triage-queue.json");
  if (!existsSync(QUEUE_FILE)) return false;
  const queue = JSON.parse(readFileSync(QUEUE_FILE, "utf8"));
  const entry = queue.files.find((f) => f.path === primaryRel || f.path.replace(/\\/g, "/") === primaryRel);
  if (!entry) return false;
  entry.status = "verified";
  entry.verified_at = new Date().toISOString();
  entry.verification = { tool: "jev-verify", at: entry.verified_at };
  writeFileSync(QUEUE_FILE, JSON.stringify(queue, null, 2), "utf8");
  return true;
}

// ── main ────────────────────────────────────────────────────────────────────
async function main() {
  const argv = process.argv.slice(2);

  function getFlag(name) {
    const i = argv.indexOf(name);
    return i !== -1 ? argv[i + 1] : null;
  }

  const dryRun = argv.includes("--dry-run");
  const asJson = argv.includes("--json");
  const apply = argv.includes("--apply");
  const against = getFlag("--against");

  // --file primary (for queue --apply), --chunk all files to evaluate.
  // Patterns are merged; --file is always included and treated as primary.
  const patterns = [];
  const fileFlag = getFlag("--file");
  const chunkIdx = argv.indexOf("--chunk");
  if (chunkIdx !== -1) {
    for (let i = chunkIdx + 1; i < argv.length; i++) {
      if (argv[i].startsWith("--")) break;
      patterns.push(argv[i]);
    }
  }
  if (fileFlag) patterns.unshift(fileFlag);
  // Positional paths only (skip known flag values)
  const flagValueIdx = new Set();
  for (let i = 0; i < argv.length; i++) {
    if (["--file", "--against", "--chunk"].includes(argv[i])) flagValueIdx.add(i + 1);
  }
  for (let i = 0; i < argv.length; i++) {
    const a = argv[i];
    if (a.startsWith("--") || flagValueIdx.has(i)) continue;
    if (existsSync(a) && !patterns.includes(a)) patterns.push(a);
  }

  if (patterns.length === 0) {
    console.error("Usage: node tools/jev-verify.mjs --file <path> [--against list.txt] [--apply] [--dry-run] [--json]");
    console.error("       node tools/jev-verify.mjs --chunk <path...> [--dry-run]");
    process.exit(2);
  }

  const absFiles = expandPaths(patterns);
  if (absFiles.length === 0) {
    console.error("[jev-verify] No files matched.");
    process.exit(2);
  }

  // Keep primary file first so Jev state + queue path stay stable
  const primaryAbs = fileFlag ? resolve(process.cwd(), fileFlag) : absFiles[0];
  absFiles.sort((a, b) => (a === primaryAbs ? -1 : b === primaryAbs ? 1 : 0));
  const primaryRel = relative(process.cwd(), primaryAbs).replace(/\\/g, "/");
  const stat = staticChecks(absFiles, against);

  let jev = null;
  let jevError = null;
  let raw = null;

  if (!dryRun) {
    try {
      const apiKey = resolveApiKey();
      const schema = JSON.parse(readFileSync(join(process.cwd(), "tools", "jev-verify-schema.json"), "utf8"));

      // Structured chunk outline + code so Jev can judge domain boundaries,
      // aggregator thinness, and contract surface — not just raw bytes.
      // Annotate with queue action when known (helps Jev pick the right rubric)
      let queueAction = null;
      try {
        const QUEUE_FILE = join(process.cwd(), "triage-queue.json");
        if (existsSync(QUEUE_FILE)) {
          const queue = JSON.parse(readFileSync(QUEUE_FILE, "utf8"));
          const entry = queue.files.find(
            (f) => f.path === primaryRel || f.path.replace(/\\/g, "/") === primaryRel,
          );
          queueAction = entry?.action ?? null;
        }
      } catch {
        /* optional */
      }

      const outline =
        (queueAction ? `ACTION: ${queueAction}\n` : "") +
        absFiles
        .map((f) => {
          const rel = relative(process.cwd(), f).replace(/\\/g, "/");
          const code = readFileSync(f, "utf8");
          const fns = listFunctions(code);
          const role = /-view\.php$/.test(rel)
            ? "view"
            : rel.endsWith("data-helper.php")
              ? "aggregator/bootstrap"
              : rel.includes("common-service")
                ? "domain:common"
                : rel.includes("user-service")
                  ? "domain:user"
                  : rel.includes("job-service")
                    ? "domain:jobs"
                    : rel.includes("system-service")
                      ? "domain:system"
                      : /\bcontroller\b/i.test(rel) || rel === "register.php" || rel === "login.php"
                        ? "controller"
                        : "file";
          return (
            `FILE ${rel} [${role}] bytes=${code.length} functions=${fns.length}\n` +
            (fns.length ? `FUNCTIONS: ${fns.join(", ")}\n` : "FUNCTIONS: (none in PHP top-level)\n")
          );
        })
        .join("\n");

      const codeBodies = absFiles
        .map((f) => `// ===== ${relative(process.cwd(), f).replace(/\\/g, "/")} =====\n${readFileSync(f, "utf8")}`)
        .join("\n\n");

      const combined =
        `CHUNK OUTLINE\n${outline}\n\nCODE\n` +
        (codeBodies.length > MAX_CHARS ? `${codeBodies.slice(0, MAX_CHARS)}\n\n…[truncated]` : codeBodies);

      raw = await withRetry(() =>
        fetchJev(
          { model: MODEL, state: { code: combined }, questions: schema.questions },
          apiKey,
        ),
      );
      jev = parseVerifyAnswers(raw);
    } catch (e) {
      jevError = e.message;
    }
  }

  // PASS rules (deterministic from scored dimensions + static checks):
// - static must pass
// - if API ran: all four noul >= 0.6
// - model verdict/residual_debt are reported as advisory only
// - if dry-run: static pass only (explicit opt-in)
// - if API errored: FAIL (don't silently pass)
  let pass;
  if (!stat.static_pass) pass = false;
  else if (dryRun) pass = true;
  else if (jevError) pass = false;
  else {
    pass =
      (jev.preserves_php_only ?? 0) >= 0.6 &&
      (jev.single_domain_coherence ?? 0) >= 0.6 &&
      (jev.contract_surface_intact ?? 0) >= 0.6 &&
      (jev.bootstrap_safe ?? 0) >= 0.6;
  }

  let applied = false;
  if (pass && apply) applied = applyVerified(primaryRel);

  const report = {
    pass,
    applied,
    dry_run: dryRun,
    primary: primaryRel,
    files: absFiles.map((f) => relative(process.cwd(), f).replace(/\\/g, "/")),
    static: stat,
    jev,
    jev_error: jevError,
    thresholds: { noul_min: 0.6, verdict_advisory: true },
    generated_at: new Date().toISOString(),
  };

  if (asJson) {
    console.log(JSON.stringify({ report, raw }, null, 2));
  } else {
    printReport(report);
  }

  // Always write a machine-readable report for the chunk
  const outPath = join(process.cwd(), "scratch", "verify-report.json");
  try {
    writeFileSync(outPath, JSON.stringify(report, null, 2), "utf8");
  } catch {
    /* scratch may be missing */
  }

  process.exit(pass ? 0 : 1);
}

main().catch((e) => {
  console.error(`[jev-verify] ${e.message}`);
  process.exit(2);
});
