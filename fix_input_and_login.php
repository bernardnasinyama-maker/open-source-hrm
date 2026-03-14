<?php

// ============================================================
// 1. FIX INPUT TEXT COLOR - white bg, dark text
// ============================================================
$extra = '
/* ── Input fields - readable ────────────────────────────── */
.fi-simple-main .fi-input,
.fi-simple-main input,
.fi-simple-main input[type=email],
.fi-simple-main input[type=password] {
    color: #1e293b !important;
    background: #ffffff !important;
    border: 2px solid #8b5cf6 !important;
    border-radius: 8px !important;
}
.fi-simple-main input:focus {
    border-color: #6d28d9 !important;
    box-shadow: 0 0 0 3px rgba(139,92,246,0.2) !important;
}
';

$css = file_get_contents('public/css/login-custom.css');
file_put_contents('public/css/login-custom.css', $css . $extra);
echo "Input colors fixed\n";

// ============================================================
// 2. FIX HR PANEL LOGIN - the canAccessPanel issue
// The auth works in tinker but Filament redirects after login
// This is because Filament checks canAccessPanel AFTER auth
// ============================================================
$employee = file_get_contents('app/Models/Employee.php');

// Check if canAccessPanel already exists
if (strpos($employee, 'canAccessPanel') === false) {
    $employee = str_replace(
        'public function getNameAttribute(): string',
        'public function canAccessPanel(\Filament\Panel $panel): bool
    {
        if ($panel->getId() === "admin") {
            return $this->hasAnyRole(["super_admin", "admin"]);
        }
        // HR panel - all employees can access
        return true;
    }

    public function getNameAttribute(): string',
        $employee
    );
    file_put_contents('app/Models/Employee.php', $employee);
    echo "canAccessPanel added\n";
} else {
    // Fix existing one
    $employee = preg_replace(
        '/public function canAccessPanel.*?}\n\n/s',
        'public function canAccessPanel(\Filament\Panel $panel): bool
    {
        if ($panel->getId() === "admin") {
            return $this->hasAnyRole(["super_admin", "admin"]);
        }
        return true;
    }

',
        $employee
    );
    file_put_contents('app/Models/Employee.php', $employee);
    echo "canAccessPanel updated\n";
}

echo "\nDone! Run: php artisan optimize:clear\n";
echo "Then try: http://localhost:8000/hr/login\n";
echo "Email: thembo.amoni@crbc.com\n";
echo "Pass:  thembo123\n";
