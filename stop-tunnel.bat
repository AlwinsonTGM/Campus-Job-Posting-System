@echo off
echo Stopping Campus Job Posting System and Cloudflare Tunnel...
taskkill /F /IM php.exe >nul 2>&1
taskkill /F /IM cloudflared.exe >nul 2>&1
echo Done! All processes stopped.
pause
