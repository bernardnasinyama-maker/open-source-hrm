<?php

// ============================================================
// 1. FIX ADMIN PANEL - proper login page
// ============================================================
$admin = file_get_contents('app/Providers/Filament/AdminPanelProvider.php');

// Add default() and fix colors properly
$admin = str_replace(
    "->id('admin')",
    "->default()\n            ->id('admin')",
    $admin
);

file_put_contents('app/Providers/Filament/AdminPanelProvider.php', $admin);
echo "Admin panel fixed\n";

// ============================================================
// 2. CUSTOM LOGIN VIEW - beautiful branded page
// ============================================================
if (!is_dir('resources/views/filament/login')) {
    mkdir('resources/views/filament/login', 0755, true);
}

// Add custom CSS to make login beautiful
$loginCss = <<<'CSS'
/* ── Login page override ─────────────────────────────────── */
.fi-simple-layout {
    background: linear-gradient(135deg, #0f0c29, #302b63, #24243e) !important;
    min-height: 100vh !important;
}
.fi-simple-main {
    background: rgba(255,255,255,0.05) !important;
    backdrop-filter: blur(20px) !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    border-radius: 20px !important;
    box-shadow: 0 25px 50px rgba(0,0,0,0.5) !important;
    padding: 2.5rem !important;
}
.fi-logo {
    color: white !important;
    font-size: 1.5rem !important;
    font-weight: 800 !important;
    letter-spacing: -0.02em !important;
}
.fi-simple-header-heading {
    color: rgba(255,255,255,0.9) !important;
    font-size: 1.1rem !important;
}
.fi-simple-header-subheading {
    color: rgba(255,255,255,0.5) !important;
}
.fi-input {
    background: rgba(255,255,255,0.08) !important;
    border-color: rgba(255,255,255,0.15) !important;
    color: white !important;
    border-radius: 10px !important;
}
.fi-input::placeholder { color: rgba(255,255,255,0.3) !important; }
.fi-input:focus {
    border-color: #8b5cf6 !important;
    box-shadow: 0 0 0 3px rgba(139,92,246,0.2) !important;
}
.fi-btn-primary {
    background: linear-gradient(135deg, #7c3aed, #4f46e5) !important;
    border: none !important;
    border-radius: 10px !important;
    font-weight: 600 !important;
    padding: 0.75rem 1.5rem !important;
    width: 100% !important;
    color: white !important;
    box-shadow: 0 4px 15px rgba(124,58,237,0.4) !important;
    transition: all 0.2s ease !important;
}
.fi-btn-primary:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 20px rgba(124,58,237,0.5) !important;
}
.fi-fo-field-wrp-label label {
    color: rgba(255,255,255,0.7) !important;
    font-size: 0.85rem !important;
}
.fi-checkbox-input {
    border-color: rgba(255,255,255,0.3) !important;
}
a { color: #a78bfa !important; }
CSS;

// Write to a custom stylesheet
if (!is_dir('public/css')) mkdir('public/css', 0755, true);
file_put_contents('public/css/login-custom.css', $loginCss);
echo "Login CSS written\n";

// ============================================================
// 3. INJECT CSS INTO BOTH PANELS via renderHook
// ============================================================
$adminProvider = file_get_contents('app/Providers/Filament/AdminPanelProvider.php');
if (strpos($adminProvider, 'login-custom.css') === false) {
    $adminProvider = str_replace(
        "->brandName('CRBC Uganda HRM')",
        "->brandName('CRBC Uganda HRM')
            ->renderHook('panels::head.end', fn() => '<link rel=\"stylesheet\" href=\"/css/login-custom.css\">')",
        $adminProvider
    );
    file_put_contents('app/Providers/Filament/AdminPanelProvider.php', $adminProvider);
    echo "CSS injected into Admin panel\n";
}

$hrProvider = file_get_contents('app/Providers/Filament/EmployeePanelProvider.php');
if (strpos($hrProvider, 'login-custom.css') === false) {
    $hrProvider = str_replace(
        "->brandName('CRBC Uganda · HR Portal')",
        "->brandName('CRBC Uganda · HR Portal')
            ->renderHook('panels::head.end', fn() => '<link rel=\"stylesheet\" href=\"/css/login-custom.css\">')",
        $hrProvider
    );
    file_put_contents('app/Providers/Filament/EmployeePanelProvider.php', $hrProvider);
    echo "CSS injected into HR panel\n";
}

// ============================================================
// 4. FIX NGROK - add bypass header for ngrok warning page
// ============================================================
$middleware = file_get_contents('app/Http/Middleware/SetLanguage.php');
echo "\nNgrok warning fix - add this to your .env:\n";
echo "NGROK_SKIP_BROWSER_WARNING=true\n\n";

// Write to .env
$env = file_get_contents('.env');
if (strpos($env, 'NGROK_SKIP_BROWSER_WARNING') === false) {
    $env .= "\nNGROK_SKIP_BROWSER_WARNING=true\n";
    file_put_contents('.env', $env);
    echo ".env updated\n";
}

// Fix ngrok warning by adding header in index.php
$index = file_get_contents('public/index.php');
if (strpos($index, 'ngrok-skip-browser-warning') === false) {
    $index = str_replace(
        '<?php',
        '<?php
// Fix ngrok browser warning
if (isset($_SERVER["HTTP_X_FORWARDED_HOST"])) {
    header("ngrok-skip-browser-warning: true");
}',
        $index
    );
    file_put_contents('public/index.php', $index);
    echo "Ngrok header fix applied\n";
}

echo "\nAll done!\n";
echo "Run: php artisan optimize:clear && php artisan serve --host=0.0.0.0 --port=8000\n";
