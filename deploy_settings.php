<?php
// Run: php deploy_settings.php
require __DIR__."/vendor/autoload.php";
$app = require __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 1. Copy SettingsPage.php
copy('SettingsPage.php', 'app/Filament/Pages/SettingsPage.php');
echo "SettingsPage.php deployed\n";

// 2. Create blade view directory and file
if (!is_dir('resources/views/filament/pages')) {
    mkdir('resources/views/filament/pages', 0755, true);
}
copy('settings-blade.html', 'resources/views/filament/pages/settings.blade.php');
echo "settings.blade.php deployed\n";

// 3. Download cloudflared.exe
$cfDest = 'cloudflared.exe';
if (!file_exists($cfDest)) {
    echo "Downloading cloudflared.exe...\n";
    $url = 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe';
    $ctx = stream_context_create(['http'=>['timeout'=>60,'follow_location'=>true]]);
    $data = @file_get_contents($url, false, $ctx);
    if ($data) {
        file_put_contents($cfDest, $data);
        echo "cloudflared.exe downloaded (" . round(filesize($cfDest)/1024/1024,1) . " MB)\n";
    } else {
        echo "Could not download cloudflared - download manually from:\n";
        echo "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe\n";
    }
} else {
    echo "cloudflared.exe already exists\n";
}

// 4. Create startup batch file
file_put_contents('start_hrm.bat',
'@echo off
title CRBC Uganda HRM Server
echo Starting CRBC Uganda HRM...
cd /d F:\open-source-hrm
php artisan serve --host=0.0.0.0 --port=8000
pause
');
echo "start_hrm.bat created\n";

// 5. Create remote access batch file
file_put_contents('start_remote.bat',
'@echo off
title CRBC HRM Remote Access
echo Starting CRBC HRM with internet access...
cd /d F:\open-source-hrm
echo Local:  http://localhost:8000/admin
echo Remote: Starting Cloudflare tunnel...
start cmd /k "php artisan serve --host=0.0.0.0 --port=8000"
timeout /t 3
cloudflared.exe tunnel --url http://localhost:8000
pause
');
echo "start_remote.bat created\n";

echo "\n=== ALL DONE ===\n";
echo "Settings page: /admin/settings (super_admin only)\n";
echo "\nTo serve locally:  double-click start_hrm.bat\n";
echo "To serve remotely: double-click start_remote.bat\n";
echo "  (Cloudflare will give you a public URL like https://xxx.trycloudflare.com)\n";
