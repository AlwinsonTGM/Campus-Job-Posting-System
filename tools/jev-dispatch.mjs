#!/usr/bin/env node
/**
 * jev-dispatch.mjs — Refactor queue manager & dispatch brief generator.
 *
 * Usage:
 *   node tools/jev-dispatch.mjs                   Show full queue
 *   node tools/jev-dispatch.mjs --next            Brief for top pending file
 *   node tools/jev-dispatch.mjs --brief <path>    Brief for a specific file
 *   node tools/jev-dispatch.mjs --status <path> <status>
 *     status: pending | in_progress | verified | skipped
 */

import { existsSync, readFileSync, writeFileSync } from "node:fs";
import { join, resolve, relative } from "node:path";

const QUEUE_FILE = join(process.cwd(), "triage-queue.json");

function loadQueue() {
  if (!existsSync(QUEUE_FILE)) {
    console.error("[jev-dispatch] triage-queue.json not found. Run: node tools/jev-triage.mjs");
    process.exit(1);
  }
  return JSON.parse(readFileSync(QUEUE_FILE, "utf8"));
}

function saveQueue(queue) {
  writeFileSync(QUEUE_FILE, JSON.stringify(queue, null, 2), "utf8");
}

function noulLabel(val) {
  if (val == null) return "?";
  return val >= 0.6 ? "YES" : "NO";
}

const PHP_GUARDRAILS = [
  "STRICT PHP PRESERVATION GUARDRAILS",
  "1. NEVER convert, port, or rewrite PHP into JavaScript or TypeScript.",
  "2. All server-side logic MUST remain in .php files.",
  "3. DB queries, session handling, auth, and email STAY in PHP.",
  "4. DO NOT create new .js files unless for a standalone client module (canvas/robot).",
  "5. Inline <script> tags in PHP view templates STAY inline.",
  "6. Preserve ALL existing function signatures and return contracts.",
  "7. Verify after refactoring: php -l <file>",
].join("\n");

const ACTION_INSTRUCTIONS = {
  split_monolith: (f) => [
    `TASK: Split the monolith (${f.size_kb} KB, multiple responsibilities).`,
    "",
    "- Keep the original file as a thin aggregator/bootstrap (~50 lines).",
    "- Extract domain functions into includes/services/<domain>-service.php.",
    "- Extract HTML markup into includes/templates/<name>-view.php.",
    "- All existing function names and parameter shapes are preserved.",
    "- Add PHP 8.2 typed parameters and return types to extracted functions.",
    "- No new .js files.",
  ].join("\n"),

  extract_view_template: (f) => {
    const viewTarget = f.path
      .replace(/^(admin|employer|student)\//, "$1/templates/")
      .replace(/^(?!(admin|employer|student))/, "includes/templates/")
      .replace(".php", "-view.php");
    return [
      `TASK: Separate controller from view.`,
      "",
      `Controller: ${f.path}`,
      `View target: ${viewTarget}`,
      "",
      "- Controller: session/auth checks, DB queries, data preparation only.",
      "- Last line of controller: require __DIR__ . '/../includes/templates/<name>-view.php';",
      "- View template: pure HTML/PHP echo — no DB calls, no header() redirects.",
      "- Pass data via variables set before the require (no $db in templates).",
      "- Inline <script> blocks stay in the view template, not extracted to .js.",
    ].join("\n");
  },

  extract_services: (f) => [
    "TASK: Extract service functions into domain service files.",
    "",
    "  includes/services/common-service.php  — shared utils, hydration, uploads",
    "  includes/services/user-service.php   — auth, session, CSRF, password",
    "  includes/services/job-service.php    — job CRUD, categories, applications",
    "  includes/services/system-service.php — metrics, notifications, devblogs",
    "",
    "- Keep only coordination code in the source file.",
    "- Add PHP 8.2 strict types to extracted functions.",
    "- Preserve 100% of existing function names and parameter shapes.",
  ].join("\n"),

  simplify_logic: () => [
    "TASK: Simplify overly complex logic.",
    "",
    "- Break functions longer than 50 lines into focused helpers.",
    "- Replace deeply nested if/else with early returns (guard clauses).",
    "- Add PHP 8.2 parameter and return types to simplified functions.",
    "- Do NOT change external behavior or return values.",
  ].join("\n"),

  remove_dead_code: () => [
    "TASK: Remove dead and redundant code.",
    "",
    "- Delete unused function definitions (verify by grep before removing).",
    "- Remove commented-out code blocks that are not documentation.",
    "- Remove var_dump(), print_r(), error_log() debug calls.",
    "- Delete variables assigned but never read.",
  ].join("\n"),

  add_type_declarations: () => [
    "TASK: Add PHP 8.2 type safety.",
    "",
    "- Add declare(strict_types=1); at the top if missing.",
    "- Add typed parameters and return types to all functions.",
    "- Replace == with === for string/int/bool comparisons.",
    "- Use null coalescing (??) instead of isset() ternaries where applicable.",
    "- Do NOT change any function signatures externally.",
  ].join("\n"),

  none: () => "TASK: Minor polish only. Do not restructure or move any code.",
};

function generateBrief(f) {
  const actionFn = ACTION_INSTRUCTIONS[f.action] ?? ACTION_INSTRUCTIONS.none;
  const issues = [
    f.violates_srp      >= 0.6 ? `  Single Responsibility Violated  (${Math.round(f.violates_srp * 100)}% confidence)` : null,
    f.has_dead_code     >= 0.6 ? `  Dead or Redundant Code Present  (${Math.round(f.has_dead_code * 100)}% confidence)` : null,
    f.lacks_type_safety >= 0.6 ? `  Missing PHP 8.2 Type Declarations (${Math.round(f.lacks_type_safety * 100)}% confidence)` : null,
  ].filter(Boolean).join("\n") || "  No critical issues flagged";

  return [
    "=".repeat(72),
    `JEV DISPATCH BRIEF`,
    `File    : ${f.path}`,
    `Size    : ${f.size_kb} KB`,
    `Action  : ${f.action ?? "none"}`,
    `Severity: ${f.severity ?? "?"}/5`,
    "-".repeat(72),
    PHP_GUARDRAILS,
    "-".repeat(72),
    "DETECTED ISSUES:",
    issues,
    "-".repeat(72),
    actionFn(f),
    "-".repeat(72),
    "VERIFICATION:",
    `  php -l ${f.path}`,
    `  jev-gate --file ${f.path}`,
    `  node tools/jev-dispatch.mjs --status ${f.path} verified`,
    "=".repeat(72),
  ].join("\n");
}

function printQueue(queue) {
  const pending  = queue.files.filter((f) => f.status === "pending");
  const progress = queue.files.filter((f) => f.status === "in_progress");
  const verified = queue.files.filter((f) => f.status === "verified");
  const skipped  = queue.files.filter((f) => f.status === "skipped");

  console.log(`\nJEV REFACTOR QUEUE — ${pending.length} pending | ${progress.length} active | ${verified.length} verified | ${skipped.length} skipped`);
  if (queue.generated_at) console.log(`Generated: ${new Date(queue.generated_at).toLocaleString()}`);
  console.log("-".repeat(88));
  console.log("  #   Sev  Action                   SRP  Dead  Types    KB    File");
  console.log("-".repeat(88));

  [...progress, ...pending].forEach((f, i) => {
    const mark = f.status === "in_progress" ? "▶ " : "  ";
    console.log(
      `${mark}${String(i + 1).padStart(2)}  ` +
      `${String(f.severity ?? "?").padEnd(4)} ` +
      `${(f.action ?? "?").padEnd(24)} ` +
      `${noulLabel(f.violates_srp).padEnd(4)} ` +
      `${noulLabel(f.has_dead_code).padEnd(5)} ` +
      `${noulLabel(f.lacks_type_safety).padEnd(6)} ` +
      `${String(f.size_kb ?? "?").padStart(6)}  ` +
      f.path
    );
  });

  if (verified.length || skipped.length) {
    console.log("\nDONE:", [...verified, ...skipped].map((f) => `[${f.status}] ${f.path}`).join(", "));
  }

  console.log("-".repeat(88));
  console.log("  --next              print brief for top item");
  console.log("  --brief <path>      print brief for specific file");
  console.log("  --status <path> <s> update status (pending|in_progress|verified|skipped)");
  console.log("  jev-triage --file <path>  re-audit one file");
  console.log("-".repeat(88) + "\n");
}

const argv = process.argv.slice(2);

function getFlag(name) {
  const i = argv.indexOf(name);
  return i !== -1 ? argv[i + 1] : null;
}

const queue = loadQueue();

if (argv.includes("--next")) {
  const top = queue.files.find((f) => f.status === "pending");
  if (!top) { console.log("[jev-dispatch] No pending items in queue."); process.exit(0); }
  top.status = "in_progress";
  saveQueue(queue);
  console.log(generateBrief(top));
  process.exit(0);
}

if (argv.includes("--brief")) {
  const pathArg = getFlag("--brief");
  if (!pathArg) { console.error("[jev-dispatch] --brief requires a file path."); process.exit(1); }
  const rel   = relative(process.cwd(), resolve(process.cwd(), pathArg)).replace(/\\/g, "/");
  const entry = queue.files.find((f) => f.path === rel || f.path === pathArg);
  if (!entry) { console.error(`[jev-dispatch] '${pathArg}' not in triage queue.`); process.exit(1); }
  console.log(generateBrief(entry));
  process.exit(0);
}

if (argv.includes("--status")) {
  const pathArg   = getFlag("--status");
  const statusIdx = argv.indexOf("--status");
  const newStatus = argv[statusIdx + 2];
  const valid     = new Set(["pending", "in_progress", "verified", "skipped"]);

  if (!pathArg || !newStatus || !valid.has(newStatus)) {
    console.error("[jev-dispatch] Usage: --status <path> <pending|in_progress|verified|skipped>");
    process.exit(1);
  }

  const rel   = relative(process.cwd(), resolve(process.cwd(), pathArg)).replace(/\\/g, "/");
  const entry = queue.files.find((f) => f.path === rel || f.path === pathArg);
  if (!entry) { console.error(`[jev-dispatch] '${pathArg}' not in queue.`); process.exit(1); }

  entry.status = newStatus;
  if (newStatus === "verified") entry.verified_at = new Date().toISOString();
  saveQueue(queue);
  console.log(`[jev-dispatch] ${entry.path} → ${newStatus}`);
  printQueue(queue);
  process.exit(0);
}

printQueue(queue);
