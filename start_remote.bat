@echo off
title CRBC HRM - Remote Access via Cloudflare
color 0B
echo.
echo  ============================================
echo   CRBC Uganda HRM - REMOTE ACCESS
echo   Powered by Cloudflare Tunnel
echo  ============================================
echo.
cd /d F:\open-source-hrm
echo Starting local server in background...
start "HRM Server" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"
timeout /t 3 /nobreak > nul
echo.
echo Starting Cloudflare tunnel...
echo A public URL will appear below in a moment.
echo Share that URL with ANYONE anywhere in the world!
echo.
echo  Example: https://abc-def-ghi.trycloudflare.com/admin
echo.
cloudflared.exe tunnel --url http://localhost:8000
pause
