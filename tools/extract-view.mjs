#!/usr/bin/env node
/**
 * extract-view.mjs — Split PHP page into controller + includes/templates/<name>-view.php
 *
 * Usage:
 *   node tools/extract-view.mjs <file.php> [more.php ...]
 *   node tools/extract-view.mjs --all-views
 *   node tools/extract-view.mjs --dry-run --all-views
 *
 * Guardrails: no PHP→JS, inline <script> stays in view, no new .js files.
 */
import { readFileSync, writeFileSync, mkdirSync, existsSync } from "node:fs";
import { join, dirname, basename, extname } from "node:path";
import { spawnSync } from "node:child_process";

const ROOT = process.cwd();
const dryRun = process.argv.includes("--dry-run");
const allViews = process.argv.includes("--all-views");

/** PHP builtins / pure presentation helpers safe to leave in view */
const KEEP_IN_VIEW = new Set([
  // language
  "if","elseif","else","endif","endwhile","endfor","endforeach","endswitch","while","for","foreach","switch","case","default","break","continue","return","echo","print","require","require_once","include","include_once","function","class","try","catch","finally","throw","use","namespace","const","global","static","new","instanceof","and","or","xor","as","declare","exit","die","match","fn","yield","array","int","float","string","bool","void","mixed","self","parent","this","true","false","null","TRUE","FALSE","NULL",
  // pure / presentation
  "htmlspecialchars","htmlentities","json_encode","date","strtotime","sprintf","number_format","nl2br","basename","dirname","str_replace","preg_replace","preg_match","ucfirst","ucwords","strtolower","strtoupper","trim","strlen","substr","explode","implode","array_keys","array_values","array_filter","array_map","array_slice","array_merge","array_reverse","array_unique","array_key_exists","array_search","count","in_array","isset","empty","defined","function_exists","is_array","is_string","is_numeric","is_null","is_bool","is_int","is_float","intval","floatval","strval","boolval","abs","min","max","round","floor","ceil","range","sort","asort","ksort","end","reset","next","current","key","compact","parse_str","rawurlencode","rawurldecode","urlencode","urldecode","base64_encode","base64_decode","md5","sha1","password_hash","password_verify","random_bytes","random_int","uniqid","microtime","time","mb_strlen","mb_substr","mb_strtolower","mb_strtoupper","pathinfo","file_exists","is_file","is_dir","realpath","filter_var","str_contains","str_starts_with","str_ends_with","str_repeat","str_pad","addslashes","stripslashes","header","http_response_code","setcookie","session_status","ini_get","getenv","phpversion","trigger_error","error_reporting","define","defined","number_format","money_format","chunk_split","wordwrap","iconv","list","each","extract","call_user_func","call_user_func_array","func_get_args","func_num_args","debug_backtrace","version_compare","php_sapi_name","sys_get_temp_dir","get_class","get_object_vars","property_exists","method_exists","class_exists","interface_exists","trait_exists","enum_exists","gettype","settype","serialize","unserialize","var_export","print_r","var_dump","debug_zval_dump","error_get_last","error_clear_last","set_error_handler","set_exception_handler","restore_error_handler","register_shutdown_function","ob_start","ob_get_clean","ob_get_contents","ob_end_clean","ob_end_flush","ob_flush","ob_clean","ob_level","ob_get_level","ob_get_length","ob_implicit_flush","ob_list_handlers","output_add_rewrite_var","output_reset_rewrite_vars","headers_sent","headers_list","headers_remove","http_response_code","setrawcookie","session_name","session_id","session_regenerate_id","session_write_close","session_destroy","session_unset","session_encode","session_decode","session_status","session_cache_limiter","session_cache_expire","session_save_path","session_set_save_handler","session_start","session_abort","session_reset","getallheaders","get_headers","stream_context_create","file_get_contents","file_put_contents","fopen","fclose","fread","fwrite","fgets","fseek","ftell","rewind","feof","ftruncate","fstat","stat","lstat","filesize","filemtime","filectime","fileatime","fileinode","fileowner","filegroup","fileperms","chmod","chown","chgrp","copy","rename","unlink","mkdir","rmdir","scandir","glob","opendir","readdir","closedir","pathinfo","dirname","basename","realpath","tempnam","tmpfile","sys_get_temp_dir","is_uploaded_file","move_uploaded_file","pathinfo","parse_url","parse_str","parse_ini_string","parse_ini_file","http_build_query","rawurlencode","rawurldecode","urlencode","urldecode","geturl","base64_encode","base64_decode","bin2hex","hex2bin","chr","ord","pack","unpack","crc32","crypt","hash","hash_hmac","hash_equals","random_bytes","random_int","openssl_random_pseudo_bytes","uuid_create","uniqid","metaphone","soundex","levenshtein","similar_text","soundex","count_chars","strtr","str_ireplace","strchr","strstr","strrchr","strpos","stripos","strrpos","strripos","strrpos","substr_count","substr_replace","str_pad","str_repeat","str_split","strrev","strcasecmp","strcmp","strncmp","strncasecmp","strnatcmp","strnatcasecmp","str_word_count","strpbrk","strspn","strcspn","stristr","strrpos","strtok","strstr","localeconv","setlocale","number_format","money_format","strftime","gmstrftime","idate","gmdate","date","getdate","localtime","gettimeofday","microtime","microtime_float","strtotime","strptime","date_default_timezone_set","date_default_timezone_get","date_sunrise","sunset","timezone_open","timezone_name_from_abbr","timezone_version_get","timezone_transitions_get","timezone_location_get","timezone_abbreviations_list","checkdate","cal_to_jd","cal_from_jd","cal_info","cal_days_in_month","easter_date","easter_days","unixtojd","jddayofweek","jdmonthname","jewishtojd","hebrewtojd","gregoriantojd","jdtogregorian","jdtounix","unixtojd","FrenchToJD","JewishToJD","JulianToJD","Kalendaryo","cal_to_jd","gregoriantojd","jdtogregorian","jdtofrench","jdtounix","unixtojd","JewishToJD","jewishtojd","jdtojewish","JulianToJD","jdtounix","juliantojd","unixtojd","cal_days_in_month","cal_info","cal_to_jd","cal_from_jd","jddayofweek","jdmonthname","easter_date","easter_days","unixtojd","gregoriantojd","jdtogregorian","jdtounix","jdtocal","jdtofrench","jdtojewish","jdtojulian","jdtounix","unixtojd","FrenchToJD","GregorianToJD","JewishToJD","JulianToJD","Kalendaryo",
  // view helpers that only echo HTML
  "render_page_head","render_flash","render_job_card","render_empty_state","render_pagination","render_modal","render_badge","render_stat","render_avatar","render_icon","render_breadcrumbs","render_rating","render_status_pill","render_time_ago","render_relative_date","render_money","render_skill_tags","render_filters","render_toolbar",
  // csrf / tokens commonly pre-rendered
  "generate_csrf_token","verify_csrf_token","csrf_field",
  // JS false-positive guards (never hoist from JS)
  "getDate","resetSearch","selectPersona","escapeHtml","checkOtherInstitute","handleFileSelected","showStepError","hideStepError","goToStep","prevStep","updateWizardUI","togglePasswordVisibility","verifyStudentIdAvailability",
]);

const PHP_ONLY_PREFIX = /^(get_|set_|create_|update_|delete_|load_|save_|validate_|hydrate_|ensure_|can_|require_|has_|is_|mark_|notify_|send_|consume_|reset_|switch_|wipe_|approve_|reject_|dismiss_|quick_|verify_|register_|login_|format_|time_ago|calculate_|persist_|check_|filter_|search_|find_|list_|fetch_|build_|make_|init_|boot_|apply_|process_|handle_|dispatch_|render_all|get_db)/;

function phpLint(abs) {
  const r = spawnSync("php", ["-l", abs], { encoding: "utf8" });
  return { ok: r.status === 0, out: (r.stdout || r.stderr || "").trim() };
}

function depthOf(fileRel) {
  const dir = dirname(fileRel).replace(/\\/g, "/");
  return dir === "." || dir === "" ? 0 : dir.split("/").length;
}

/** Extract only PHP code regions from a template (between <?php/<?= and ?>) */
function phpSegments(text) {
  const segs = [];
  const re = /<\?(?:php|=)?([\s\S]*?)\?>/g;
  let m;
  while ((m = re.exec(text))) segs.push(m[1]);
  // also leading open with no close
  if (text.startsWith("<?php")) {
    const rest = text.slice(5);
    if (!rest.includes("?>")) segs.push(rest);
  }
  return segs.join("\n");
}

function findZeroArgServiceCalls(phpCode) {
  const re = /\b([A-Za-z_][A-Za-z0-9_]*)\s*\(\s*\)/g;
  const found = new Map();
  let m;
  while ((m = re.exec(phpCode))) {
    const name = m[1];
    if (KEEP_IN_VIEW.has(name)) continue;
    if (!PHP_ONLY_PREFIX.test(name) && !name.includes("_")) continue;
    // skip if defined as function in this file later
    found.set(name, (found.get(name) || 0) + 1);
  }
  return found;
}

function detectSplit(lines) {
  let headerIdx = -1;
  for (let i = 0; i < lines.length; i++) {
    if (/require(?:_once)?\s+.*header\.php/.test(lines[i])) {
      headerIdx = i;
      break;
    }
  }
  if (headerIdx >= 0) {
    for (let i = headerIdx + 1; i < lines.length; i++) {
      if (lines[i].trim() === "?>") return { headerIdx, phpEndIdx: i };
      if (/<!DOCTYPE|<html[\s>]/i.test(lines[i])) return { headerIdx, phpEndIdx: i - 1 };
    }
  }
  // Partial (navbar/footer): first ?> after top PHP block
  if (headerIdx < 0) {
    for (let i = 0; i < lines.length; i++) {
      if (lines[i].trim() === "?>") return { headerIdx: -1, phpEndIdx: i, partial: true };
    }
  }
  for (let i = 0; i < lines.length; i++) {
    if (/<!DOCTYPE|<html[\s>]/i.test(lines[i])) {
      for (let j = i - 1; j >= 0; j--) {
        if (lines[j].trim() === "?>") return { headerIdx: -1, phpEndIdx: j };
      }
      return { headerIdx: -1, phpEndIdx: i - 1 };
    }
  }
  return null;
}

function extractView(fileRel) {
  const abs = join(ROOT, fileRel);
  if (!existsSync(abs)) return { file: fileRel, ok: false, error: "missing" };

  const raw = readFileSync(abs, "utf8");
  const hasCRLF = raw.includes("\r\n");
  const nl = hasCRLF ? "\r\n" : "\n";
  const lines = raw.split(/\r?\n/);

  const split = detectSplit(lines);
  if (!split) return { file: fileRel, ok: false, error: "no_split_point" };

  const { headerIdx, phpEndIdx, partial } = split;
  const isPartial = partial || headerIdx < 0;

  // Controller = PHP up to (not including) ?>
  let controllerLines = lines.slice(0, phpEndIdx); // exclusive of ?>
  if (headerIdx >= 0) {
    controllerLines = controllerLines.filter((l) => !/require(?:_once)?\s+.*header\.php/.test(l));
  }
  while (controllerLines.length && controllerLines[controllerLines.length - 1].trim() === "") {
    controllerLines.pop();
  }

  // View region
  let viewLines = lines.slice(phpEndIdx + 1);
  while (viewLines.length && viewLines[0].trim() === "") viewLines.shift();

  // Unique view name from path: employer/create-job.php → employer-create-job-view.php
  // root register.php → register-view.php
  const pathNoExt = fileRel.replace(/\.php$/i, "").replace(/\\/g, "/");
  const base =
    pathNoExt.includes("/")
      ? pathNoExt.replace(/\//g, "-")
      : pathNoExt;
  const viewRel = `includes/templates/${base}-view.php`;
  const depth = depthOf(fileRel);
  const up = depth === 0 ? "" : "../".repeat(depth);

  // Fix __DIR__ paths in view → ../ from includes/templates/
  // Original patterns:
  //   __DIR__ . '/includes/X'        (root controller)
  //   __DIR__ . '/../includes/X'     (one-level controller)
  //   __DIR__ . '/../../includes/X'  (two-level)
  // Target from includes/templates/: __DIR__ . '/../X'
  for (let i = 0; i < viewLines.length; i++) {
    viewLines[i] = viewLines[i]
      .replaceAll(/__DIR__\s*\.\s*'\/(?:\.\.\/)+includes\//g, "__DIR__ . '/../")
      .replaceAll(/__DIR__\s*\.\s*'\/includes\//g, "__DIR__ . '/../");
  }

  // Hoist zero-arg PHP service calls from PHP segments only
  const definedInFile = new Set(
    [...raw.matchAll(/function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/g)].map((m) => m[1]),
  );
  const viewPhp = phpSegments(viewLines.join("\n"));
  const calls = findZeroArgServiceCalls(viewPhp);
  const preload = [];
  for (const fn of calls.keys()) {
    if (definedInFile.has(fn)) continue;
    if (KEEP_IN_VIEW.has(fn)) continue;
    // Only hoist obvious data getters / form helpers
    if (!PHP_ONLY_PREFIX.test(fn) && fn !== "generate_csrf_token") continue;
    const varName = `$view_${fn}`;
    // Replace only inside PHP segments — approach: replace all occurrences of fn() in view lines
    // but NOT in JS (JS wouldn't typically call get_* with that exact form in attributes as bare fn())
    for (let i = 0; i < viewLines.length; i++) {
      if (viewLines[i].includes(`${fn}()`)) {
        // Don't replace if it's clearly in a JS comment/function def line with `function fn`
        if (/function\s+/.test(viewLines[i]) && viewLines[i].includes(`function ${fn}`)) continue;
        viewLines[i] = viewLines[i].replaceAll(`${fn}()`, varName);
      }
    }
    preload.push(`${varName} = ${fn}();`);
  }

  // Hoist form repopulation: $_POST → $form (controller sets $form = $_POST)
  let usesForm = false;
  for (let i = 0; i < viewLines.length; i++) {
    if (/\$_POST\b/.test(viewLines[i])) {
      viewLines[i] = viewLines[i].replace(/\$_POST\b/g, "$form");
      usesForm = true;
    }
    if (/\$_GET\b/.test(viewLines[i])) {
      viewLines[i] = viewLines[i].replace(/\$_GET\b/g, "$query");
      usesForm = true;
    }
    if (/\$_FILES\b/.test(viewLines[i])) {
      viewLines[i] = viewLines[i].replace(/\$_FILES\b/g, "$files");
      usesForm = true;
    }
    // Read-only flash toast in views: $_SESSION['flash'] → $view_flash
    if (/\$_SESSION\['flash'\]/.test(viewLines[i])) {
      viewLines[i] = viewLines[i].replace(/\$_SESSION\['flash'\]/g, "$view_flash");
      usesForm = true;
    }
  }
  if (usesForm) {
    preload.unshift(
      "$form = $_POST;",
      "$query = $_GET;",
      "$files = $_FILES;",
      "$view_flash = $_SESSION['flash'] ?? null;",
    );
  }

  // Precompute $_SERVER share URL patterns for update-detail style links
  if (viewLines.some((l) => /\$_SERVER\['HTTP_HOST'\]|\$_SERVER\["HTTP_HOST"\]|\$_SERVER\['REQUEST_URI'\]|\$_SERVER\["REQUEST_URI"\]/.test(l))) {
    preload.push(
      `$share_url = 'http://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');`,
    );
    for (let i = 0; i < viewLines.length; i++) {
      viewLines[i] = viewLines[i]
        .replace(/'http:\/\/' \. \$_SERVER\['HTTP_HOST'\] \. \$_SERVER\['REQUEST_URI'\]/g, "$share_url")
        .replace(/\$_SERVER\['HTTP_HOST'\] \. \$_SERVER\['REQUEST_URI'\]/g, "$share_url")
        .replace(/\$_SERVER\['HTTP_HOST'\]/g, "parse_url($share_url, PHP_URL_HOST)")
        .replace(/\$_SERVER\['REQUEST_URI'\]/g, "parse_url($share_url, PHP_URL_PATH)");
    }
  }

  // Build view
const viewHeader = isPartial
    ? [
        "<?php",
        "/**",
        ` * View template for ${fileRel} (partial)`,
        " * Pure HTML/PHP echo. No controller redirects / session mutations here.",
        " */",
        "?>",
        "",
      ]
    : [
        "<?php",
        "/**",
        ` * View template for ${fileRel}`,
        " * Pure HTML/PHP echo. Controller sets variables before require.",
        " * Inline <script> blocks intentionally stay here (guardrail).",
        " */",
        "require_once __DIR__ . '/../header.php';",
        "?>",
        "",
      ];

  // For partials, the original top PHP block stays in controller; view starts after ?>
  // For pages, same.

  const viewContent = [...viewHeader, ...viewLines].join(nl) + nl;

  // Controller ending
  const ctrlRequire = isPartial
    ? null
    : `require __DIR__ . '/${up}includes/templates/${base}-view.php';`;

  const ctrlEnd = [];
  if (preload.length) {
    ctrlEnd.push("", "// Preload view data — no service/DB calls in the template");
    ctrlEnd.push(...preload, "");
  }
  if (ctrlRequire) {
    ctrlEnd.push("// Last line: view template", ctrlRequire, "");
  }

  let ctrlContent = [...controllerLines, ...ctrlEnd].join(nl) + nl;
  if (!ctrlContent.startsWith("<?php")) ctrlContent = "<?php\n" + ctrlContent;

  // Residual checks (view) — form/query/files/flash replaced with preloaded vars
  const residual = [];
  if (/\$_SESSION\b/.test(viewContent)) residual.push("$_SESSION");
  if (/\$_POST\b/.test(viewContent)) residual.push("$_POST");
  if (/\$_GET\b/.test(viewContent)) residual.push("$_GET");
  if (/\$_FILES\b/.test(viewContent)) residual.push("$_FILES");
  if (/\$_SERVER\b/.test(viewContent)) residual.push("$_SERVER");
  if (/header\s*\(\s*['"]Location/.test(viewContent)) residual.push("header_redirect");

  // leftover hoisted calls — word-boundary to avoid render_flash() matching flash()
  for (const fn of preload) {
    const m = fn.match(/\$view_(\w+)\s*=/);
    if (m && new RegExp(`\\b${m[1]}\\s*\\(`).test(viewContent)) residual.push(`leftover:${m[1]}()`);
  }

  if (dryRun) {
    return {
      file: fileRel,
      view: isPartial ? "(partial)" : viewRel,
      ok: residual.length === 0,
      dry: true,
      residual,
      preload,
      isPartial,
      ctrl_bytes: Buffer.byteLength(ctrlContent),
      view_bytes: Buffer.byteLength(viewContent),
    };
  }

  if (!isPartial) {
    const viewAbs = join(ROOT, viewRel);
    mkdirSync(dirname(viewAbs), { recursive: true });
    writeFileSync(viewAbs, viewContent, "utf8");
    writeFileSync(abs, ctrlContent, "utf8");
    const lintC = phpLint(abs);
    const lintV = phpLint(viewAbs);
    return {
      file: fileRel,
      view: viewRel,
      ok: lintC.ok && lintV.ok && residual.length === 0,
      residual,
      preload,
      lint_ctrl: lintC,
      lint_view: lintV,
      ctrl_bytes: Buffer.byteLength(ctrlContent),
      view_bytes: Buffer.byteLength(viewContent),
      ctrl_lines: ctrlContent.split(/\n/).length,
      view_lines: viewContent.split(/\n/).length,
    };
  }

  // Partial: top PHP stays as thin controller requiring view
  const viewAbs = join(ROOT, viewRel);
  mkdirSync(dirname(viewAbs), { recursive: true });
  writeFileSync(viewAbs, viewContent, "utf8");
  const isInIncludes = fileRel.replace(/\\/g, "/").startsWith("includes/");
  const partialRequire = isInIncludes
    ? `require __DIR__ . '/templates/${base}-view.php';`
    : `require __DIR__ . '/${up}includes/templates/${base}-view.php';`;
  const thinCtrl = [...controllerLines, "", partialRequire, ""].join(nl) + nl;
  writeFileSync(abs, thinCtrl.startsWith("<?php") ? thinCtrl : "<?php\n" + thinCtrl, "utf8");
  const lintC = phpLint(abs);
  const lintV = phpLint(viewAbs);
  return {
    file: fileRel,
    view: viewRel,
    ok: lintC.ok && lintV.ok && residual.length === 0,
    residual,
    preload,
    isPartial: true,
    lint_ctrl: lintC,
    lint_view: lintV,
  };
}

function pendingViewFiles() {
  const q = JSON.parse(readFileSync(join(ROOT, "triage-queue.json"), "utf8"));
  return q.files
    .filter(
      (f) =>
        (f.status === "pending" || f.status === "in_progress") &&
        f.action === "extract_view_template",
    )
    .map((f) => f.path.replace(/\\/g, "/"));
}

async function main() {
  let files = [];
  if (allViews) files = pendingViewFiles();
  else files = process.argv.slice(2).filter((a) => !a.startsWith("--") && a.endsWith(".php"));

  if (!files.length) {
    console.error("Usage: node tools/extract-view.mjs <files...> | --all-views [--dry-run]");
    process.exit(2);
  }

  const results = [];
  for (const f of files) {
    const r = extractView(f);
    results.push(r);
    const tag = r.ok ? "OK" : r.dry ? "DRY" : "FAIL";
    console.log(
      `${tag} ${f}` +
        (r.view && !r.isPartial ? ` → ${r.view}` : "") +
        (r.isPartial ? " (partial)" : "") +
        (r.preload?.length ? ` preload=${r.preload.length}` : "") +
        (r.residual?.length ? ` residual=${r.residual.join(",")}` : "") +
        (r.error ? ` ${r.error}` : "") +
        (r.lint_ctrl && !r.lint_ctrl.ok ? ` LINT_C:${r.lint_ctrl.out}` : "") +
        (r.lint_view && !r.lint_view.ok ? ` LINT_V:${r.lint_view.out}` : ""),
    );
  }

  const ok = results.filter((r) => r.ok).length;
  console.log(`\n${ok}/${results.length} OK`);
  writeFileSync(join(ROOT, "scratch", "extract-view-report.json"), JSON.stringify(results, null, 2), "utf8");
  process.exit(ok === results.length ? 0 : 1);
}

main().catch((e) => {
  console.error(e);
  process.exit(2);
});
