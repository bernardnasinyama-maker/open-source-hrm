<?php

// ============================================================
// 1. FIX SESSION IN .ENV
// ============================================================
$env = file_get_contents('.env');
$env = preg_replace('/SESSION_DRIVER=.*/', 'SESSION_DRIVER=file', $env);
if (strpos($env, 'SESSION_SECURE_COOKIE') === false) {
    $env .= "\nSESSION_SECURE_COOKIE=false";
} else {
    $env = preg_replace('/SESSION_SECURE_COOKIE=.*/', 'SESSION_SECURE_COOKIE=false', $env);
}
if (strpos($env, 'SESSION_SAME_SITE') === false) {
    $env .= "\nSESSION_SAME_SITE=lax";
} else {
    $env = preg_replace('/SESSION_SAME_SITE=.*/', 'SESSION_SAME_SITE=lax', $env);
}
file_put_contents('.env', $env);
echo "Session fixed\n";

// ============================================================
// 2. WRITE TRUSTPROXIES MIDDLEWARE
// ============================================================
if (!is_dir('app/Http/Middleware')) {
    mkdir('app/Http/Middleware', 0755, true);
}

file_put_contents('app/Http/Middleware/TrustProxies.php', '<?php
namespace App\Http\Middleware;
use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    protected $proxies = "*";
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}');
echo "TrustProxies written\n";

// ============================================================
// 3. REGISTER IN BOOTSTRAP/APP.PHP
// ============================================================
$bootstrap = file_get_contents('bootstrap/app.php');
if (strpos($bootstrap, 'TrustProxies') === false) {
    $bootstrap = str_replace(
        '->withMiddleware(function (Middleware $middleware) {',
        '->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(\App\Http\Middleware\TrustProxies::class);',
        $bootstrap
    );
    file_put_contents('bootstrap/app.php', $bootstrap);
    echo "Bootstrap updated\n";
} else {
    echo "Bootstrap already has TrustProxies\n";
}

// ============================================================
// 4. CLEAR SESSION FILES
// ============================================================
$sessionPath = 'storage/framework/sessions';
if (is_dir($sessionPath)) {
    $files = glob($sessionPath . '/*');
    foreach ($files as $file) {
        if (is_file($file)) unlink($file);
    }
    echo "Session files cleared\n";
}

echo "\nAll done! Run: php artisan optimize:clear\n";
echo "Then restart server and ngrok\n";
