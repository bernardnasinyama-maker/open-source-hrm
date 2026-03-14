@echo off
title CRBC Uganda HRM - Local Server
color 0A
echo.
echo  ============================================
echo   CRBC Uganda HRM System
echo   Kayunga-Bbaale-Galiraya Road (87KM)
echo  ============================================
echo.
cd /d F:\open-source-hrm
echo Starting server...
echo.
echo  Local access:   http://localhost:8000/admin
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr "IPv4"') do (
    set IP=%%a
    goto :found
)
:found
set IP=%IP: =%
echo  Network access: http://%IP%:8000/admin
echo.
echo  Share the Network access URL with your team!
echo  Press Ctrl+C to stop
echo.
php artisan serve --host=0.0.0.0 --port=8000
pause
