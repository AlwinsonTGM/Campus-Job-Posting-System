@echo off
REM Wrapper so the QA batch can run without touching PowerShell execution policy.
REM Usage:  qa\run-batch.cmd [team-file...]
setlocal
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0run-batch.ps1" %*
exit /b %ERRORLEVEL%
