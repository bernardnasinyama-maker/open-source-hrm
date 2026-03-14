<?php
require __DIR__."/vendor/autoload.php";
$app = require __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// ============================================================
// 1. FIX ROUTES - Add missing payslip + reports routes
// ============================================================
$routesFile = 'routes/web.php';
$routes = file_get_contents($routesFile);

if (strpos($routes, 'reports.employees.csv') === false) {
    $newRoutes = '
// HRM Reports & Payslip Routes
Route::middleware(["auth:web"])->group(function () {
    Route::get("/reports/employees/csv",  [App\Http\Controllers\ReportsController::class, "employeesCsv"])->name("reports.employees.csv");
    Route::get("/reports/payroll/csv",    [App\Http\Controllers\ReportsController::class, "payrollCsv"])->name("reports.payroll.csv");
    Route::get("/reports/attendance/csv", [App\Http\Controllers\ReportsController::class, "attendanceCsv"])->name("reports.attendance.csv");
    Route::get("/payslip/{id}",           [App\Http\Controllers\ReportsController::class, "payslip"])->name("payslip.employee");
});
';
    file_put_contents($routesFile, $routes . $newRoutes);
    echo "Routes added\n";
} else {
    echo "Routes already exist\n";
}

// ============================================================
// 2. FIX/CREATE REPORTS CONTROLLER
// ============================================================
if (!is_dir('app/Http/Controllers')) mkdir('app/Http/Controllers', 0755, true);

file_put_contents('app/Http/Controllers/ReportsController.php', '<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function employeesCsv()
    {
        if (!auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant"])) abort(403);
        $employees = Employee::with(["department","position"])
            ->whereNotIn("employee_code",["SYS-001","CRBC-VIEW"])
            ->get();
        $csv = "Employee Code,First Name,Last Name,Email,Department,Employment Type,Gender,Hire Date,Status\n";
        foreach ($employees as $e) {
            $csv .= implode(",", [
                $e->employee_code, $e->first_name, $e->last_name,
                $e->email, $e->department?->name ?? "N/A",
                $e->employment_type, $e->gender,
                $e->hire_date, $e->is_active ? "Active" : "Inactive"
            ]) . "\n";
        }
        return response($csv, 200, [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=employees_" . date("Y-m-d") . ".csv",
        ]);
    }

    public function payrollCsv()
    {
        if (!auth()->user()?->hasAnyRole(["super_admin","admin"])) abort(403);
        $payrolls = Payroll::with("employee")->orderByDesc("pay_date")->get();
        $csv = "Employee,Period,Gross Pay,Deductions,Allowances,Net Pay,Status,Pay Date\n";
        foreach ($payrolls as $p) {
            $csv .= implode(",", [
                $p->employee?->first_name . " " . $p->employee?->last_name,
                $p->period, $p->gross_pay, $p->deductions,
                $p->allowances, $p->net_pay, $p->status, $p->pay_date
            ]) . "\n";
        }
        return response($csv, 200, [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=payroll_" . date("Y-m-d") . ".csv",
        ]);
    }

    public function attendanceCsv()
    {
        if (!auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant"])) abort(403);
        $records = Attendance::with("employee")
            ->whereMonth("date", now()->month)
            ->orderByDesc("date")->get();
        $csv = "Employee,Date,Clock In,Clock Out,Remarks\n";
        foreach ($records as $a) {
            $csv .= implode(",", [
                $a->employee?->first_name . " " . $a->employee?->last_name,
                $a->date, $a->clock_in ?? "N/A",
                $a->clock_out ?? "N/A", $a->remarks ?? ""
            ]) . "\n";
        }
        return response($csv, 200, [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=attendance_" . date("Y-m-d") . ".csv",
        ]);
    }

    public function payslip($id)
    {
        if (!auth()->user()?->hasAnyRole(["super_admin","admin"])) abort(403);
        $employee = Employee::with(["department","position"])->findOrFail($id);
        $payroll  = Payroll::where("employee_id", $id)->latest("pay_date")->first();
        if (!$payroll) {
            return back()->with("error", "No payroll found for this employee.");
        }
        // Calculate deductions
        $gross = $payroll->gross_pay;
        $nssfEmployee = round($gross * 0.05);
        $nssfEmployer = round($gross * 0.10);
        $taxable = $gross - $nssfEmployee;
        // PAYE Uganda 2024/2025
        $paye = 0;
        if ($taxable > 10000000)      { $paye = 1433596 + ($taxable - 10000000) * 0.40; }
        elseif ($taxable > 4920000)   { $paye = 383596  + ($taxable - 4920000)  * 0.30; }
        elseif ($taxable > 2820000)   { $paye = 173596  + ($taxable - 2820000)  * 0.20; }
        elseif ($taxable > 335000)    { $paye = round(($taxable - 335000) * 0.10); }
        // LST
        $lst = $gross >= 1000000 ? 100000 : ($gross >= 360000 ? 25000 : ($gross >= 100000 ? 5000 : 0));
        $totalDeductions = $nssfEmployee + $paye + $lst;
        $netPay = $gross - $totalDeductions + ($payroll->allowances ?? 0) + ($payroll->bonuses ?? 0);
        return view("payslip", compact("employee","payroll","gross","nssfEmployee","nssfEmployer","paye","lst","totalDeductions","netPay"));
    }
}');
echo "ReportsController created\n";

// ============================================================
// 3. CREATE START SCRIPTS
// ============================================================

// Local server bat
file_put_contents('start_hrm.bat',
'@echo off
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
for /f "tokens=2 delims=:" %%a in (\'ipconfig ^| findstr "IPv4"\') do (
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
');
echo "start_hrm.bat created\n";

// Remote access bat
file_put_contents('start_remote.bat',
'@echo off
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
');
echo "start_remote.bat created\n";

// ============================================================
// 4. EMAIL SETUP INSTRUCTIONS
// ============================================================
echo "\n=== EMAIL SETUP (2 minutes) ===\n";
echo "1. Go to: https://myaccount.google.com/apppasswords\n";
echo "2. Sign in with: einsteinbernard3000@gmail.com\n";
echo "3. Select app: Mail | Device: Windows Computer\n";
echo "4. Copy the 16-character password shown\n";
echo "5. Run this command (replace XXXX with your password):\n\n";
echo 'php -r "
$e = file_get_contents(\'.env\');
$e = preg_replace(\'/MAIL_MAILER=.*/\',       \'MAIL_MAILER=smtp\', $e);
$e = preg_replace(\'/MAIL_HOST=.*/\',         \'MAIL_HOST=smtp.gmail.com\', $e);
$e = preg_replace(\'/MAIL_PORT=.*/\',         \'MAIL_PORT=587\', $e);
$e = preg_replace(\'/MAIL_USERNAME=.*/\',     \'MAIL_USERNAME=einsteinbernard3000@gmail.com\', $e);
$e = preg_replace(\'/MAIL_PASSWORD=.*/\',     \'MAIL_PASSWORD=XXXX-XXXX-XXXX-XXXX\', $e);
$e = preg_replace(\'/MAIL_ENCRYPTION=.*/\',   \'MAIL_ENCRYPTION=tls\', $e);
$e = preg_replace(\'/MAIL_FROM_ADDRESS=.*/\', \'MAIL_FROM_ADDRESS=einsteinbernard3000@gmail.com\', $e);
$e = preg_replace(\'/MAIL_FROM_NAME=.*/\',    \'MAIL_FROM_NAME=\"CRBC Uganda HRM\"\', $e);
file_put_contents(\'.env\', $e);
echo \'Email configured!\';
"' . "\n";

echo "\n=== ALL FIXES DONE ===\n";
echo "Run: php artisan optimize:clear && php artisan serve --host=0.0.0.0 --port=8000\n";
