@echo off
REM Pre-defense smoke test. Usage: qa\smoke-test.cmd
setlocal
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0smoke-test.ps1"
exit /b %ERRORLEVEL%
