@echo off
title CRBC HRM - Remote Access
color 0B
cd /d F:\open-source-hrm

echo.
echo  ============================================
echo   CRBC HRM - INTERNET ACCESS via Cloudflare
echo  ============================================
echo.

:: Start Laravel server in background
start "HRM Server" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"
timeout /t 3 /nobreak >nul

echo  Starting Cloudflare tunnel...
echo  A public URL will appear below - share it ending with /admin
echo.
echo  Example: https://abc-xyz.trycloudflare.com/admin
echo.
echo  Share credentials:
echo  Email: thembo.amoni@crbc.com  Pass: thembo123  (HR)
echo  Email: mr.shi@crbc.com        Pass: shi123@    (Admin)
echo.

cloudflared.exe tunnel --url http://localhost:8000
pause
