@echo off
REM ============================================================================
REM QA Application Server Launcher
REM Serves the app against the ISOLATED test database with mail disabled.
REM
REM   DB_NAME=campus_job_portal_e2e  -^> live database is never touched
REM   MAIL_USERNAME / MAIL_PASSWORD  -^> blanked so is_smtp_configured() is
REM                                     false, which makes OTP codes render on
REM                                     screen instead of being emailed.
REM
REM IMPORTANT - why the blanking below is NOT sufficient on its own:
REM   includes/ai/env.php `load_env()` runs `putenv()` for every key in .env.
REM   It only skips keys already set via getenv(), and a variable deliberately
REM   set to an EMPTY string still reads back as false. So once .env holds real
REM   credentials this file CANNOT suppress them, and a registration would send
REM   real mail (this actually happened once during development).
REM
REM   The real guarantee now lives in code, not in this script:
REM   includes/services/user-service.php::registration_mail_allowed() refuses
REM   to dispatch any registration mail on a QA server. This script sets
REM   QA_MAIL_DISABLED=1 to flip that gate.
REM ============================================================================

set "DB_HOST=127.0.0.1"
set "DB_PORT=3306"
set "DB_NAME=campus_job_portal_e2e"
set "DB_USER=root"
set "DB_PASS="

REM Belt-and-braces only - see the note above. Harmless when .env is blank.
set "MAIL_USERNAME="
set "MAIL_PASSWORD="

REM The actual gate: registration_mail_allowed() returns false when this is 1.
set "QA_MAIL_DISABLED=1"

set "APP_ENV=local"

REM Keep single-process: login.php?reset=1 copies data/*.json to fixed paths,
REM so concurrent workers would corrupt fixtures mid-test.
set "PHP_CLI_SERVER_WORKERS=1"

REM C:\xampp\tmp is not writable in this environment. That breaks two things:
REM   1. session_start() fails -> login and CSRF break for every test.
REM   2. PHP cannot stage file uploads there, so every attachment upload dies
REM      with "Failed to persist ... to storage" even though uploads/ is fine.
REM Redirect both into the workspace, which is writable.
if not exist "%~dp0sessions" mkdir "%~dp0sessions"
if not exist "%~dp0uploads-tmp" mkdir "%~dp0uploads-tmp"

cd /d "%~dp0.."
"C:\xampp\php\php.exe" -c "C:\xampp\php\php.ini" ^
  -d "session.save_path=%~dp0sessions" ^
  -d "session.gc_probability=0" ^
  -d "upload_tmp_dir=%~dp0uploads-tmp" ^
  -d "sys_temp_dir=%~dp0uploads-tmp" ^
  -S 127.0.0.1:8099 -t "%CD%"
