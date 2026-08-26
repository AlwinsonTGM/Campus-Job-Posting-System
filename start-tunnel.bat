@echo off
title Campus Job Posting System - Server & Tunnel
echo ====================================================
echo Starting Campus Job Posting System...
echo ====================================================

set PHP_EXE=C:\xampp\php\php.exe
set CF_EXE=C:\tools\cloudflared.exe

if not exist "%PHP_EXE%" (
    echo [ERROR] PHP executable not found at %PHP_EXE%
    pause
    exit /b 1
)

if not exist "%CF_EXE%" (
    echo [ERROR] Cloudflared executable not found at %CF_EXE%
    pause
    exit /b 1
)

echo Starting PHP built-in server on http://127.0.0.1:8000 ...
start "Campus Job - PHP Server" /B "%PHP_EXE%" -S 127.0.0.1:8000

timeout /t 2 >nul

echo Starting Cloudflare Tunnel...
echo.
echo ====================================================
echo Look for the public https://*.trycloudflare.com URL below:
echo ====================================================
echo.

"%CF_EXE%" tunnel --url http://127.0.0.1:8000
