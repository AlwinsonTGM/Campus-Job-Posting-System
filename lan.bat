@echo off
title Campus Job Posting System - LAN Router Hosting
setlocal enabledelayedexpansion

echo ============================================================
echo   CAMPUS HIRE - AUTOMATED LOCAL ROUTER (LAN) HOSTING
echo ============================================================
echo.

:: 1. Detect Local IPv4 Address
set "LAN_IP="
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"IPv4 Address"') do (
    set "temp_ip=%%a"
    :: Strip leading whitespace
    for /f "tokens=*" %%b in ("!temp_ip!") do set "temp_ip=%%b"
    if "!LAN_IP!"=="" set "LAN_IP=!temp_ip!"
)

if "%LAN_IP%"=="" (
    echo [!] ERROR: Could not detect a valid IPv4 address.
    echo     Ensure this PC is connected to your Wi-Fi or Ethernet router.
    echo.
    pause
    exit /b 1
)

:: 2. Locate PHP Binary (PATH or standard XAMPP location)
set "PHP_BIN=php"
where php >nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set "PHP_BIN=C:\xampp\php\php.exe"
    ) else (
        echo [!] ERROR: PHP executable not found in system PATH or C:\xampp\php\php.exe
        echo     Please ensure XAMPP or PHP is installed on this PC.
        echo.
        pause
        exit /b 1
    )
)

:: 3. Server Configuration
set "PORT=8000"
set "PROJECT_DIR=%~dp0"

echo [✓] Local Network Details Detected:
echo     - Host PC IP   : %LAN_IP%
echo     - Host Port    : %PORT%
echo     - PHP Executable: %PHP_BIN%
echo.
echo ============================================================
echo  ACCESS URL FOR OTHER DEVICES ON THE SAME ROUTER/WI-FI:
echo.
echo      http://%LAN_IP%:%PORT%
echo.
echo ============================================================
echo  Note: Ensure Windows Firewall allows inbound connections 
echo        on Port %PORT% if other devices fail to connect.
echo  Press Ctrl+C in this terminal window to stop server.
echo ============================================================
echo.

:: 4. Start PHP Built-in Web Server bound to LAN IP
"%PHP_BIN%" -S %LAN_IP%:%PORT% -t "%PROJECT_DIR%."

pause