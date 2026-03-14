@echo off
title CRBC Uganda HRM System
color 0A
cd /d F:\open-source-hrm

echo.
echo  ============================================
echo   CRBC Uganda HRM - Starting...
echo  ============================================
echo.

:: Fix session driver for speed
php -r "$e=file_get_contents('.env'); if(strpos($e,'SESSION_DRIVER=file')===false){$e=str_replace('SESSION_DRIVER=database','SESSION_DRIVER=file',$e); file_put_contents('.env',$e);}"

:: Clear cache
php artisan optimize:clear >nul 2>&1
php artisan permission:cache-reset >nul 2>&1

:: Get local IP
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr "IPv4"') do (
    set IP=%%a
    goto :found
)
:found
set IP=%IP: =%

echo  Local:   http://localhost:8000/admin
echo  Network: http://%IP%:8000/admin
echo.
echo  Login: einsteinbernard3000@gmail.com
echo  Pass:  ben123#
echo.

:: Start server
start "CRBC HRM Server" php artisan serve --host=0.0.0.0 --port=8000

:: Wait 2 seconds then open browser
timeout /t 2 /nobreak >nul
start chrome http://localhost:8000/admin

echo  Server running! Press Ctrl+C to stop.
echo.
pause
