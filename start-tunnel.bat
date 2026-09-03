@echo off
title Campus Job Posting System - Public Tunnel
echo ========================================================
echo   CAMPUS JOB POSTING SYSTEM - PUBLIC INTERNET TUNNEL
echo ========================================================
echo.
echo Local URL:
echo   http://localhost/Final-Campus-Job-Posting-System/
echo.
echo Connecting tunnel to Cloudflare Edge...
echo Look for the 'https://*.trycloudflare.com' link below.
echo.
echo ========================================================
"C:\Program Files (x86)\cloudflared\cloudflared.exe" tunnel --url http://localhost:80
pause
