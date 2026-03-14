<?php

// ============================================================
// 1. SETUP CONTROLLER
// ============================================================
if (!is_dir('app/Http/Controllers')) mkdir('app/Http/Controllers', 0755, true);

file_put_contents('app/Http/Controllers/SetupController.php', '<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

class SetupController extends Controller
{
    public function index()
    {
        // If already set up, redirect to admin
        if (Employee::where("employee_code", "SYS-001")->exists()) {
            return redirect("/admin")->with("info", "System already configured.");
        }
        return view("setup.wizard");
    }

    public function store(Request $request)
    {
        $request->validate([
            "company_name"    => "required|string|max:100",
            "project_name"    => "required|string|max:150",
            "contract_number" => "required|string|max:50",
            "country"         => "required|string|max:50",
            "currency"        => "required|string|max:10",
            "admin_name"      => "required|string|max:100",
            "admin_email"     => "required|email",
            "admin_password"  => "required|min:6|confirmed",
            "working_hours"   => "required",
            "late_threshold"  => "required",
        ]);

        // Update .env
        $envUpdates = [
            "APP_BRAND_NAME"     => "\"{$request->company_name} HRM\"",
            "APP_BRAND_SHORT"    => "\"" . substr($request->company_name, 0, 10) . " HRM\"",
            "APP_COMPANY_NAME"   => "\"{$request->company_name}\"",
            "APP_PROJECT_NAME"   => "\"{$request->project_name}\"",
            "APP_CONTRACT_NO"    => "\"{$request->contract_number}\"",
            "APP_COUNTRY"        => "\"{$request->country}\"",
            "APP_CURRENCY"       => $request->currency,
            "APP_CURRENCY_SYM"   => $request->currency,
            "APP_ADMIN_EMAIL"    => $request->admin_email,
            "APP_WORKING_HOURS"  => $request->working_hours,
            "APP_LATE_THRESHOLD" => $request->late_threshold,
        ];

        $env = file_get_contents(base_path(".env"));
        foreach ($envUpdates as $key => $value) {
            if (strpos($env, $key . "=") !== false) {
                $env = preg_replace("/{$key}=.*/", "{$key}={$value}", $env);
            } else {
                $env .= "\n{$key}={$value}";
            }
        }
        file_put_contents(base_path(".env"), $env);

        // Update payslip contract number
        $payslip = file_get_contents(resource_path("views/payslip.blade.php"));
        $payslip = preg_replace(
            "/Contract No: .*/",
            "Contract No: {$request->contract_number}",
            $payslip
        );
        file_put_contents(resource_path("views/payslip.blade.php"), $payslip);

        // Create roles
        foreach (["super_admin","admin","hr_assistant","viewer","employee"] as $role) {
            Role::firstOrCreate(["name" => $role, "guard_name" => "web"]);
        }

        // Create departments
        $depts = array_filter(array_map("trim", explode(",", $request->departments ?? "Administration,HR,Engineering,Finance")));
        foreach ($depts as $dept) {
            \App\Models\Department::firstOrCreate(["name" => $dept]);
        }

        // Create super admin
        $nameParts = explode(" ", $request->admin_name, 2);
        $admin = Employee::create([
            "employee_code"   => "SYS-001",
            "first_name"      => $nameParts[0],
            "last_name"       => $nameParts[1] ?? "Admin",
            "email"           => $request->admin_email,
            "password"        => Hash::make($request->admin_password),
            "employment_type" => "Permanent",
            "department_id"   => \App\Models\Department::first()?->id,
            "gender"          => "Male",
            "hire_date"       => now()->toDateString(),
            "is_active"       => true,
        ]);
        $admin->assignRole("super_admin");

        // Clear cache
        Artisan::call("optimize:clear");

        return redirect("/setup/complete")->with([
            "company"  => $request->company_name,
            "email"    => $request->admin_email,
            "password" => $request->admin_password,
        ]);
    }

    public function complete()
    {
        return view("setup.complete");
    }
}');
echo "SetupController created\n";

// ============================================================
// 2. SETUP ROUTES
// ============================================================
$routes = file_get_contents('routes/web.php');
if (strpos($routes, '/setup') === false) {
    $routes .= '

// Setup Wizard
Route::get("/setup", [App\Http\Controllers\SetupController::class, "index"])->name("setup.index");
Route::post("/setup", [App\Http\Controllers\SetupController::class, "store"])->name("setup.store");
Route::get("/setup/complete", [App\Http\Controllers\SetupController::class, "complete"])->name("setup.complete");
';
    file_put_contents('routes/web.php', $routes);
    echo "Setup routes added\n";
}

// ============================================================
// 3. SETUP WIZARD VIEW
// ============================================================
if (!is_dir('resources/views/setup')) mkdir('resources/views/setup', 0755, true);

file_put_contents('resources/views/setup/wizard.blade.php', '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>System Setup Wizard</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:"Segoe UI",sans-serif;background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.wizard{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(139,92,246,.3);border-radius:20px;width:100%;max-width:680px;overflow:hidden}
.wizard-header{background:linear-gradient(135deg,#7c3aed,#4f46e5);padding:32px;text-align:center}
.wizard-header h1{color:white;font-size:26px;font-weight:800;margin-bottom:6px}
.wizard-header p{color:rgba(255,255,255,.7);font-size:14px}
.wizard-body{padding:32px}
.section{margin-bottom:28px}
.section-title{color:#c4b5fd;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.section-title::after{content:"";flex:1;height:1px;background:rgba(139,92,246,.2)}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-group{margin-bottom:14px}
label{display:block;color:rgba(255,255,255,.7);font-size:12px;font-weight:600;margin-bottom:6px}
input,select,textarea{width:100%;background:rgba(255,255,255,.08);border:1px solid rgba(139,92,246,.3);border-radius:8px;padding:10px 14px;color:#f1f5f9;font-size:13px;outline:none;transition:border-color .2s}
input:focus,select:focus,textarea:focus{border-color:#8b5cf6;box-shadow:0 0 0 3px rgba(139,92,246,.15)}
select option{background:#1e1b4b;color:#f1f5f9}
.hint{font-size:11px;color:rgba(255,255,255,.3);margin-top:4px}
.btn-submit{width:100%;background:linear-gradient(135deg,#7c3aed,#4f46e5);color:white;border:none;border-radius:10px;padding:14px;font-size:15px;font-weight:700;cursor:pointer;margin-top:8px;box-shadow:0 4px 15px rgba(124,58,237,.4);transition:all .2s}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(124,58,237,.5)}
.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#f87171;font-size:13px}
.powered{text-align:center;margin-top:20px;color:rgba(255,255,255,.2);font-size:11px}
</style>
</head>
<body>
<div class="wizard">
    <div class="wizard-header">
        <div style="font-size:40px;margin-bottom:12px">⚙️</div>
        <h1>HRM System Setup</h1>
        <p>Configure your Human Resource Management System</p>
    </div>
    <div class="wizard-body">

        @if($errors->any())
        <div class="error">
            @foreach($errors->all() as $e) • {{ $e }}<br> @endforeach
        </div>
        @endif

        <form method="POST" action="/setup">
            @csrf

            {{-- Company Info --}}
            <div class="section">
                <div class="section-title">🏢 Company Information</div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Company Name *</label>
                        <input type="text" name="company_name" value="{{ old("company_name") }}" placeholder="e.g. CRBC Uganda Ltd" required>
                    </div>
                    <div class="form-group">
                        <label>Country *</label>
                        <select name="country">
                            <option value="Uganda" selected>🇺🇬 Uganda</option>
                            <option value="Kenya">🇰🇪 Kenya</option>
                            <option value="Tanzania">🇹🇿 Tanzania</option>
                            <option value="Rwanda">🇷🇼 Rwanda</option>
                            <option value="Nigeria">🇳🇬 Nigeria</option>
                            <option value="Ghana">🇬🇭 Ghana</option>
                            <option value="South Africa">🇿🇦 South Africa</option>
                            <option value="Other">🌍 Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Project Name *</label>
                    <input type="text" name="project_name" value="{{ old("project_name") }}" placeholder="e.g. Kayunga-Bbaale-Galiraya Road (87KM)" required>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Contract Number *</label>
                        <input type="text" name="contract_number" value="{{ old("contract_number") }}" placeholder="e.g. MOWT/WORKS/2024-25/00115" required>
                    </div>
                    <div class="form-group">
                        <label>Currency *</label>
                        <select name="currency">
                            <option value="UGX" selected>UGX — Uganda Shilling</option>
                            <option value="KES">KES — Kenya Shilling</option>
                            <option value="TZS">TZS — Tanzania Shilling</option>
                            <option value="USD">USD — US Dollar</option>
                            <option value="NGN">NGN — Nigerian Naira</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Departments (comma separated)</label>
                    <input type="text" name="departments" value="{{ old("departments","Administration,Human Resource,Engineering,Finance,Operations") }}" placeholder="Administration, HR, Engineering...">
                    <div class="hint">These departments will be created automatically</div>
                </div>
            </div>

            {{-- Working Hours --}}
            <div class="section">
                <div class="section-title">🕐 Working Hours</div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Official Start Time *</label>
                        <input type="time" name="working_hours" value="{{ old("working_hours","08:00") }}" required>
                    </div>
                    <div class="form-group">
                        <label>Late Arrival Threshold *</label>
                        <input type="time" name="late_threshold" value="{{ old("late_threshold","08:30") }}" required>
                        <div class="hint">Arrivals after this time are marked LATE</div>
                    </div>
                </div>
            </div>

            {{-- Admin Account --}}
            <div class="section">
                <div class="section-title">👤 System Administrator Account</div>
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="admin_name" value="{{ old("admin_name") }}" placeholder="e.g. Bernard Nasinyama" required>
                </div>
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="admin_email" value="{{ old("admin_email") }}" placeholder="admin@company.com" required>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="admin_password" placeholder="Min 6 characters" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password *</label>
                        <input type="password" name="admin_password_confirmation" placeholder="Repeat password" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">🚀 Launch My HRM System</button>
        </form>

        <div class="powered">Powered by SiteHRM — Built for African Construction Companies</div>
    </div>
</div>
</body>
</html>');
echo "Setup wizard view created\n";

// ============================================================
// 4. SETUP COMPLETE VIEW
// ============================================================
file_put_contents('resources/views/setup/complete.blade.php', '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Setup Complete!</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:"Segoe UI",sans-serif;background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.card{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(16,185,129,.3);border-radius:20px;width:100%;max-width:500px;padding:40px;text-align:center}
.icon{font-size:64px;margin-bottom:20px}
h1{color:#34d399;font-size:28px;font-weight:800;margin-bottom:10px}
p{color:rgba(255,255,255,.6);font-size:14px;margin-bottom:24px;line-height:1.6}
.info-box{background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);border-radius:12px;padding:20px;margin-bottom:24px;text-align:left}
.info-row{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.05);font-size:13px}
.info-row:last-child{border:none}
.info-label{color:rgba(255,255,255,.4)}
.info-value{color:#f1f5f9;font-weight:600}
.btn{display:block;background:linear-gradient(135deg,#7c3aed,#4f46e5);color:white;text-decoration:none;border-radius:10px;padding:14px;font-size:15px;font-weight:700;margin-bottom:10px;box-shadow:0 4px 15px rgba(124,58,237,.4)}
.btn-hr{display:block;background:rgba(255,255,255,.08);color:#c4b5fd;text-decoration:none;border-radius:10px;padding:12px;font-size:14px;font-weight:600;border:1px solid rgba(139,92,246,.3)}
</style>
</head>
<body>
<div class="card">
    <div class="icon">🎉</div>
    <h1>Setup Complete!</h1>
    <p>Your HRM system is ready. Save your login credentials below before proceeding.</p>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">System</span>
            <span class="info-value">{{ config("app_branding.name") }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Admin URL</span>
            <span class="info-value">/admin</span>
        </div>
        <div class="info-row">
            <span class="info-label">HR Portal</span>
            <span class="info-value">/hr</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email</span>
            <span class="info-value">{{ session("email") }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Password</span>
            <span class="info-value">{{ session("password") }}</span>
        </div>
    </div>

    <a href="/admin" class="btn">🔐 Go to Admin Panel</a>
    <a href="/hr" class="btn-hr">👥 Go to HR Portal</a>

    <p style="margin-top:20px;font-size:11px;color:rgba(255,255,255,.2)">
        Powered by SiteHRM — Built for African Construction Companies
    </p>
</div>
</body>
</html>');
echo "Setup complete view created\n";

echo "\n=== SETUP WIZARD COMPLETE ===\n";
echo "Access at: http://localhost:8000/setup\n";
echo "Run: php artisan optimize:clear\n";
