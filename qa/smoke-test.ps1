# ==============================================================================
# PRE-DEFENSE SMOKE TEST
#
# Run this before presenting. Confirms in seconds that:
#   1. The demo dataset is intact (30 users / 20 jobs / 22 applications)
#   2. All three roles can log in
#   3. Every key page renders without a PHP error
#   4. The dataset switcher is LOCKED to admins and cannot be GET-triggered
#   5. The live database was not disturbed
#
#   Usage:  qa\smoke-test.cmd
# ==============================================================================
$ErrorActionPreference = 'Continue'

$root  = Split-Path -Parent $PSScriptRoot
$mysql = 'C:\xampp\mysql\bin\mysql.exe'
$php   = 'C:\xampp\php\php.exe'
$base  = 'http://localhost/Final-Campus-Job-Posting-System'
$DB    = 'campus_job_portal'

$pass = 0; $fail = 0
function Ok($m)   { $script:pass++; Write-Host "  [PASS] $m" -ForegroundColor Green }
function Bad($m)  { $script:fail++; Write-Host "  [FAIL] $m" -ForegroundColor Red }
function Info($m) { Write-Host "         $m" -ForegroundColor DarkGray }

function Count($table) {
  $n = & $mysql -u root -N -e "SELECT COUNT(*) FROM ``$DB``.``$table``;" 2>$null
  return [int]$n
}

Write-Host "`n=== 1. DATASET INTACT ($DB) ==="
$expected = @{ users = 30; jobs = 20; applications = 22; categories = 6 }
foreach ($t in $expected.Keys | Sort-Object) {
  $n = Count $t
  if ($n -eq $expected[$t]) { Ok "$t = $n" } else { Bad "$t = $n (expected $($expected[$t]))" }
}

Write-Host "`n=== 2. ROLES CAN LOG IN ==="
foreach ($role in @('student', 'employer', 'admin')) {
  $jar = Join-Path $env:TEMP "smoke_$role.txt"
  & 'C:\Windows\System32\curl.exe' -s -c $jar -o NUL -w "%{http_code}" `
      "$base/login.php?demo=$role" 2>$null | Out-Null
  # follow to the role dashboard and confirm we are authenticated
  $url = switch ($role) {
    'student'  { "$base/student/dashboard.php" }
    'employer' { "$base/employer/dashboard.php" }
    'admin'    { "$base/admin/reports.php" }
  }
  $body = & 'C:\Windows\System32\curl.exe' -s -b $jar "$url" 2>$null | Out-String
  if ($body -match 'Sign In to Campus Hire') { Bad "$role -> bounced back to login" }
  elseif ($body -match 'Fatal error|Uncaught \w+:') { Bad "$role -> PHP error on $url" }
  else { Ok "$role can sign in and open their dashboard" }
  Remove-Item $jar -Force -ErrorAction SilentlyContinue
}

Write-Host "`n=== 3. KEY PAGES RENDER CLEANLY ==="
$pages = @(
  '/index.php', '/login.php', '/register.php', '/faqs.php', '/about-us.php',
  '/privacy.php', '/terms.php', '/student/jobs.php',
  '/student/job-details.php?id=3', '/api/search-jobs.php'
)
foreach ($p in $pages) {
  try {
    $r = Invoke-WebRequest -Uri "$base$p" -UseBasicParsing -TimeoutSec 30 -ErrorAction Stop
    if ($r.Content -match '<b>Fatal error</b>|Uncaught \w+Error|Uncaught \w+TypeError') {
      Bad "$p -> PHP error in body"
    } elseif ($r.StatusCode -eq 200) {
      Ok "$p (HTTP 200, $([math]::Round($r.RawContentLength/1KB)) KB)"
    } else {
      Bad "$p -> HTTP $($r.StatusCode)"
    }
  } catch {
    $code = try { [int]$_.Exception.Response.StatusCode } catch { 'ERR' }
    Bad "$p -> HTTP $code"
  }
}

Write-Host "`n=== 4. DATASET SWITCHER IS LOCKED DOWN ==="
$usersBefore = Count 'users'
foreach ($q in @('action=switch_mode&mode=real', 'mode=real', 'action=wipe_real')) {
  $code = & 'C:\Windows\System32\curl.exe' -s -o NUL -w "%{http_code}" "$base/data-toggle.php?$q" 2>$null
  if ($code -eq '302' -or $code -eq '403' -or $code -eq '405') {
    Ok "GET ?$q -> HTTP $code (refused)"
  } else {
    Bad "GET ?$q -> HTTP $code (NOT refused)"
  }
}
$usersAfter = Count 'users'
if ($usersAfter -eq $usersBefore) { Ok "dataset survived the probes ($usersAfter users)" }
else { Bad "DATASET CHANGED: $usersBefore -> $usersAfter" }

Write-Host "`n=== 5. ENVIRONMENT ==="
$appEnv = (Select-String -Path "$root\.env" -Pattern '^APP_ENV=' -ErrorAction SilentlyContinue)
if ($appEnv) { Info "APP_ENV is set: $($appEnv.Line) (production would disable the switcher)" }
else { Ok "APP_ENV not production -> demo switcher available for your presentation" }

$mailUser = (Select-String -Path "$root\.env" -Pattern '^MAIL_USERNAME=(.+)$' -ErrorAction SilentlyContinue)
if ($mailUser) { Ok "SMTP credentials present (real email will send)" }
else { Info "MAIL_USERNAME is empty -> OTP codes will render on screen instead of emailing" }

$modeFile = "$root\data\system_mode.json"
if (Test-Path $modeFile) {
  $mode = (Get-Content $modeFile -Raw | ConvertFrom-Json).active_mode
  if ($mode -eq 'demo') { Ok "active dataset mode = demo (full demo data)" }
  else { Bad "active dataset mode = $mode (expected demo)" }
}

Write-Host "`n============================================"
if ($fail -eq 0) {
  Write-Host " SMOKE TEST PASSED  ($pass checks)" -ForegroundColor Green
  Write-Host " You are good to present." -ForegroundColor Green
} else {
  Write-Host " SMOKE TEST FAILED  ($pass passed, $fail failed)" -ForegroundColor Red
  Write-Host " Fix the [FAIL] lines above before presenting." -ForegroundColor Red
}
Write-Host "============================================`n"

exit $fail
