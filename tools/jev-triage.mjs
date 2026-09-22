#!/usr/bin/env node
/**
 * jev-triage.mjs — PHP codebase triage engine using TypeSafe System One (Jev).
 *
 * Usage:
 *   node tools/jev-triage.mjs                 Audit all eligible PHP files
 *   node tools/jev-triage.mjs --file <path>   Audit a single file
 *   node tools/jev-triage.mjs --top <n>       Audit the n largest files only
 *   node tools/jev-triage.mjs --rescan        Force re-audit already-queued files
 *   node tools/jev-triage.mjs --dry-run       Print file list, skip API calls
 *
 * Requires: TYPESAFE_API_KEY in %USERPROFILE%/.typesafe.env or project .env
 * Output:   triage-queue.json (gitignored runtime artifact)
 */

import { existsSync, readFileSync, statSync, readdirSync, writeFileSync } from "node:fs";
import { homedir } from "node:os";
import { join, resolve, relative, extname } from "node:path";

function loadEnvFile(path) {
  if (!existsSync(path)) return;
  for (const raw of readFileSync(path, "utf8").split(/\r?\n/)) {
    const line = raw.trim();
    if (!line || line.startsWith("#")) continue;
    const eq  = line.indexOf("=");
    if (eq === -1) continue;
    const key = line.slice(0, eq).trim().replace(/^export\s+/, "");
    const val = line.slice(eq + 1).trim().replace(/^["']|["']$/g, "");
    if (!(key in process.env)) process.env[key] = val;
  }
}

function resolveApiKey() {
  loadEnvFile(join(process.cwd(), ".env"));
  loadEnvFile(join(process.cwd(), ".env.local"));
  loadEnvFile(join(homedir(), ".typesafe.env"));
  const key = process.env.TYPESAFE_API_KEY?.trim();
  if (!key) throw new Error("TYPESAFE_API_KEY not found. Set it in ~/.typesafe.env or run: jev-setup-key");
  return key;
}

const EXCLUDED_DIRS = new Set([
  "node_modules", ".git", ".agents", ".codex", "uploads", "playwright-report",
  "test-results", "scratch", "vendor", "PHPMailer",
]);

const EXCLUDED_FILES = new Set([
  "migrate.php", "test_comprehensive_dual_integration.php", "test_adversarial_audit.php",
]);

function discoverPhpFiles(root) {
  const results = [];
  function walk(dir) {
    for (const entry of readdirSync(dir, { withFileTypes: true })) {
      if (entry.isDirectory()) {
        if (!EXCLUDED_DIRS.has(entry.name)) walk(join(dir, entry.name));
      } else if (extname(entry.name) === ".php" && !EXCLUDED_FILES.has(entry.name)) {
        const full = join(dir, entry.name);
        results.push({ full, rel: relative(root, full), size: statSync(full).size });
      }
    }
  }
  walk(root);
  return results.sort((a, b) => b.size - a.size);
}

const ENDPOINT  = "https://api.typesafe.ai/v1/systemone";
const MODEL     = "jev-latest";
const MAX_CHARS = 50000;
const RETRYABLE = /429|529|ECONNRESET|fetch failed/i;

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
  const ctrl  = new AbortController();
  const timer = setTimeout(() => ctrl.abort(), 30000);
  try {
    const res = await fetch(ENDPOINT, {
      method:  "POST",
      headers: { Authorization: `Bearer ${apiKey}`, "Content-Type": "application/json" },
      body:    JSON.stringify(payload),
      signal:  ctrl.signal,
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

async function callJev(code, apiKey, questions) {
  const state   = { code: code.length > MAX_CHARS ? `${code.slice(0, MAX_CHARS)}\n\n…[truncated]` : code };
  const payload = { model: MODEL, state, questions };
  return withRetry(() => fetchJev(payload, apiKey));
}

function extractNoul(ans) {
  if (!ans || typeof ans !== "object") return null;
  if (typeof ans.noul === "number")   return ans.noul;
  if (typeof ans.value === "boolean") {
    const c = typeof ans.confidence === "number" ? ans.confidence : 1;
    return ans.value ? c : 1 - c;
  }
  return typeof ans.probability === "number" ? ans.probability : null;
}

function extractChoice(ans) {
  return ans?.value ?? ans?.choice ?? ans?.answer ?? null;
}

function extractScore(ans) {
  const raw = ans?.value ?? ans?.score ?? null;
  return raw != null ? Math.round(raw * 10) / 10 : null;
}

function parseAnswers(data) {
  const a = data?.answers ?? {};
  return {
    has_dead_code:     extractNoul(a.has_dead_or_redundant_code),
    violates_srp:      extractNoul(a.violates_single_responsibility),
    lacks_type_safety: extractNoul(a.lacks_php_type_safety),
    action:            extractChoice(a.primary_refactor_action),
    severity:          extractScore(a.spaghetti_severity),
    usage:             data?.usage ?? null,
  };
}

const QUEUE_FILE = join(process.cwd(), "triage-queue.json");

function loadQueue() {
  return existsSync(QUEUE_FILE)
    ? JSON.parse(readFileSync(QUEUE_FILE, "utf8"))
    : { generated_at: null, files: [] };
}

function saveQueue(queue) {
  writeFileSync(QUEUE_FILE, JSON.stringify(queue, null, 2), "utf8");
}

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const r2    = (n)  => (n != null ? Math.round(n * 100) / 100 : null);

function severityLabel(n) {
  if (n == null) return "?";
  if (n >= 4)   return `🔴 ${n}/5`;
  if (n >= 3)   return `🟡 ${n}/5`;
  return              `🟢 ${n}/5`;
}

function noulLabel(val) {
  return val == null ? "?" : val >= 0.6 ? "YES" : "NO";
}

async function main() {
  const argv       = process.argv.slice(2);
  const DRY_RUN    = argv.includes("--dry-run");
  const RESCAN     = argv.includes("--rescan");
  const fileFlag   = argv.indexOf("--file");
  const topFlag    = argv.indexOf("--top");
  const targetFile = fileFlag !== -1 ? argv[fileFlag + 1] : null;
  const topN       = topFlag  !== -1 ? parseInt(argv[topFlag + 1], 10) : null;

  const root    = process.cwd();
  const schema  = JSON.parse(readFileSync(join(root, "tools", "jev-schema.json"), "utf8"));
  const apiKey  = DRY_RUN ? "dry-run-skip" : resolveApiKey();
  const queue   = loadQueue();

  let candidates;
  if (targetFile) {
    const resolved = resolve(root, targetFile);
    if (!existsSync(resolved)) throw new Error(`File not found: ${targetFile}`);
    candidates = [{ full: resolved, rel: relative(root, resolved), size: statSync(resolved).size }];
  } else {
    candidates = discoverPhpFiles(root);
    if (topN) candidates = candidates.slice(0, topN);
  }

  if (!RESCAN && !targetFile) {
    const queued = new Set(queue.files.map((f) => f.path));
    const before = candidates.length;
    candidates   = candidates.filter((c) => !queued.has(c.rel));
    if (before !== candidates.length) {
      console.log(`[jev-triage] Skipping ${before - candidates.length} already-audited files. Use --rescan to re-evaluate.`);
    }
  }

  if (candidates.length === 0) {
    console.log("[jev-triage] Nothing to audit. Queue is current.");
    printQueue(queue);
    return;
  }

  console.log(`\n[jev-triage] Auditing ${candidates.length} PHP file(s)…\n`);

  if (DRY_RUN) {
    candidates.forEach((c, i) =>
      console.log(`  ${String(i + 1).padStart(3)}. ${c.rel.padEnd(55)} ${(c.size / 1024).toFixed(1)} KB`)
    );
    console.log("\n[jev-triage] --dry-run: skipping API calls.");
    return;
  }

  let totalIn = 0, totalOut = 0;

  for (let i = 0; i < candidates.length; i++) {
    const { full, rel, size } = candidates[i];
    process.stdout.write(`[${i + 1}/${candidates.length}] ${rel} (${(size / 1024).toFixed(1)} KB) … `);

    let parsed;
    try {
      const raw = await callJev(readFileSync(full, "utf8"), apiKey, schema.questions);
      parsed    = parseAnswers(raw);
      if (parsed.usage) { totalIn += parsed.usage.input_tokens ?? 0; totalOut += parsed.usage.output_tokens ?? 0; }
    } catch (e) {
      console.log(`ERROR: ${e.message}`);
      continue;
    }

    const entry = {
      path: rel, size_kb: Math.round(size / 1024 * 10) / 10, status: "pending",
      severity: parsed.severity, action: parsed.action,
      has_dead_code: r2(parsed.has_dead_code), violates_srp: r2(parsed.violates_srp),
      lacks_type_safety: r2(parsed.lacks_type_safety), audited_at: new Date().toISOString(),
    };

    const idx = queue.files.findIndex((f) => f.path === rel);
    if (idx !== -1) queue.files[idx] = { ...queue.files[idx], ...entry };
    else            queue.files.push(entry);

    queue.generated_at = new Date().toISOString();
    saveQueue(queue);
    console.log(`${severityLabel(parsed.severity)} → ${parsed.action ?? "?"}`);
    await sleep(200);
  }

  queue.files.sort((a, b) => {
    const done = (s) => s === "verified" || s === "skipped";
    if (done(a.status) !== done(b.status)) return done(a.status) ? 1 : -1;
    return (b.severity ?? 0) - (a.severity ?? 0);
  });
  saveQueue(queue);

  console.log(`\n[jev-triage] Done. Tokens: ${totalIn} in / ${totalOut} out.`);
  printQueue(queue);
}

function printQueue(queue) {
  const pending = queue.files.filter((f) => f.status !== "verified" && f.status !== "skipped");
  const done    = queue.files.filter((f) => f.status === "verified" || f.status === "skipped");

  console.log(`\nTRIAGE QUEUE — ${pending.length} pending / ${done.length} done`);
  console.log("-".repeat(88));
  console.log(" #   Sev  Action                   SRP  Dead  Types    KB    File");
  console.log("-".repeat(88));

  pending.forEach((f, i) => console.log(
    ` ${String(i + 1).padStart(2)}  ${String(f.severity ?? "?").padEnd(4)} ${(f.action ?? "?").padEnd(24)} ` +
    `${noulLabel(f.violates_srp).padEnd(4)} ${noulLabel(f.has_dead_code).padEnd(5)} ` +
    `${noulLabel(f.lacks_type_safety).padEnd(6)} ${String(f.size_kb ?? "?").padStart(6)}  ${f.path}`
  ));

  if (done.length) console.log(`\nDONE: ${done.map((f) => f.path).join(", ")}`);
  console.log("-".repeat(88) + "\n");
}

main().catch((e) => { console.error(`[jev-triage] ${e.message}`); process.exit(1); });
