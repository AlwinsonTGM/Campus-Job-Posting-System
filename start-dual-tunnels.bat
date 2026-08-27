@echo off
title Campus Job Posting System - Dual Branch Server & Tunnels
echo ================================================================
echo Starting Dual Branch Servers and Cloudflare Tunnels...
echo ================================================================

set PHP_EXE=C:\xampp\php\php.exe
set CF_EXE=C:\tools\cloudflared.exe
set MAIN_DIR=C:\Users\PC\Documents\Campus-Job-Posting-System-main
set FEAT_DIR=C:\Users\PC\Documents\Campus-Job-Posting-System

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

if not exist "%MAIN_DIR%" (
    echo [ERROR] Main branch worktree not found at %MAIN_DIR%
    echo Run: git worktree add ../Campus-Job-Posting-System-main main
    pause
    exit /b 1
)

echo [1/4] Starting Feature Branch PHP Server on 127.0.0.1:8000 ...
start "Campus Job - Feature (8000)" /D "%FEAT_DIR%" /B "%PHP_EXE%" -S 127.0.0.1:8000

echo [2/4] Starting Main Branch PHP Server on 127.0.0.1:8080 ...
start "Campus Job - Main (8080)" /D "%MAIN_DIR%" /B "%PHP_EXE%" -S 127.0.0.1:8080

timeout /t 2 >nul

echo [3/4] Starting Cloudflare Tunnel for Feature Branch (8000)...
start "Cloudflare Tunnel - Feature (8000)" "%CF_EXE%" tunnel --url http://127.0.0.1:8000

echo [4/4] Starting Cloudflare Tunnel for Main Branch (8080)...
start "Cloudflare Tunnel - Main (8080)" "%CF_EXE%" tunnel --url http://127.0.0.1:8080

echo.
echo ================================================================
echo Both servers and tunnels are running!
echo Check the two popup tunnel windows for your public trycloudflare.com URLs.
echo To stop everything, run stop-tunnel.bat.
echo ================================================================
pause
