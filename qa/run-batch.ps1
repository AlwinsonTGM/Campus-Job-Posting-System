# ==============================================================================
# QA batch runner
#
# Runs one or more harness scripts with the browser available and verifies
# database containment afterwards:
#   - campus_job_portal_e2e may change (that is the point)
#   - campus_job_portal must NOT change
#
# Usage:  qa\run-batch.cmd [team-file...]
#         qa\run-batch.cmd                       (runs containment only)
# ==============================================================================
$ErrorActionPreference = 'Continue'

$root   = Split-Path -Parent $PSScriptRoot
$mysql  = 'C:\xampp\mysql\bin\mysql.exe'
$QA_DB  = 'campus_job_portal_e2e'
$LIVE_DB= 'campus_job_portal'

function Count-Rows($db) {
  $tables = & $mysql -u root -N -e "SELECT table_name FROM information_schema.tables WHERE table_schema='$db' AND table_type='BASE TABLE';" 2>$null
  $total = 0
  $per = @{}
  foreach ($t in $tables) {
    if (-not $t) { continue }
    $n = & $mysql -u root -N -e "SELECT COUNT(*) FROM ``$db``.``$t``;" 2>$null
    $per[$t] = [int]$n
    $total += [int]$n
  }
  return @{ Total = $total; Per = $per }
}

Write-Host "=== snapshot BEFORE ==="
$qaBefore   = Count-Rows $QA_DB
$liveBefore = Count-Rows $LIVE_DB
Write-Host "  ${QA_DB}: $($qaBefore.Total) rows"
Write-Host "  ${LIVE_DB}: $($liveBefore.Total) rows"

$targets = if ($args.Count -gt 0) { $args } else { @('qa\teams\00-containment.mjs') }

Set-Location $root
foreach ($t in $targets) {
  Write-Host "`n=== RUN $t ==="
  node $t
  Write-Host "=== $t exit=$LASTEXITCODE ==="
}

Write-Host "`n=== snapshot AFTER ==="
$qaAfter   = Count-Rows $QA_DB
$liveAfter = Count-Rows $LIVE_DB

$qaDelta   = $qaAfter.Total   - $qaBefore.Total
$liveDelta = $liveAfter.Total - $liveBefore.Total

Write-Host "  ${QA_DB}: $($qaBefore.Total) -> $($qaAfter.Total)  (delta $qaDelta)"
Write-Host "  ${LIVE_DB}: $($liveBefore.Total) -> $($liveAfter.Total)  (delta $liveDelta)"

Write-Host ""
if ($liveDelta -eq 0) {
  Write-Host "CONTAINMENT: PASS - live database untouched (delta 0)." -ForegroundColor Green
} else {
  Write-Host "CONTAINMENT: FAIL - live database changed by $liveDelta rows!" -ForegroundColor Red
}
if ($qaDelta -gt 0) {
  Write-Host "ISOLATION:   PASS - test database received the new data (+$qaDelta)." -ForegroundColor Green
} else {
  Write-Host "ISOLATION:   NOTE - test database delta was $qaDelta (expected growth)." -ForegroundColor Yellow
}

# quick look at what the QA run created
Write-Host "`n=== newest QA users ==="
& $mysql -u root --table -e "SELECT id,name,email,role,is_email_verified FROM $QA_DB.users ORDER BY id DESC LIMIT 5;" 2>$null

exit 0
