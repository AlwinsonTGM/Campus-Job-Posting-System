@echo off
REM ============================================================================
REM QA Application Server Launcher
REM Serves the app against the ISOLATED test database with SMTP disabled.
REM
REM   DB_NAME=campus_job_portal_e2e  -^> live database is never touched
REM   MAIL_USERNAME empty            -^> is_smtp_configured() == false, so OTP
REM                                     codes render on-screen instead of being
REM                                     emailed. No mail can leave the machine.
REM
REM load_env() only applies a .env value when (!getenv(key)), so these
REM process-level values take precedence over .env.
REM ============================================================================

set "DB_HOST=127.0.0.1"
set "DB_PORT=3306"
set "DB_NAME=campus_job_portal_e2e"
set "DB_USER=root"
set "DB_PASS="
set "MAIL_USERNAME="
set "MAIL_PASSWORD="
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
