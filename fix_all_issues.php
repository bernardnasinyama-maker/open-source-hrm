<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// ============================================================
// 1. FIX PAYROLL - uncomment create/edit pages
// ============================================================
$f = 'app/Filament/Resources/Payrolls/PayrollResource.php';
$c = file_get_contents($f);
$c = str_replace(
    "return [
            'index' => ListPayrolls::route('/'),
            // 'create' => Pages\CreatePayroll::route('/create'),
            // 'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];",
    "return [
            'index'  => ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'edit'   => Pages\EditPayroll::route('/{record}/edit'),
            'view'   => Pages\ViewPayroll::route('/{record}'),
        ];",
    $c
);
// Add missing imports
if (strpos($c, 'CreatePayroll') === false) {
    $c = str_replace(
        'use App\Filament\Resources\Payrolls\Pages\ListPayrolls;',
        'use App\Filament\Resources\Payrolls\Pages\ListPayrolls;
use App\Filament\Resources\Payrolls\Pages\CreatePayroll;
use App\Filament\Resources\Payrolls\Pages\EditPayroll;
use App\Filament\Resources\Payrolls\Pages\ViewPayroll;',
        $c
    );
    $c = str_replace(
        "return [
            'index'  => ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'edit'   => Pages\EditPayroll::route('/{record}/edit'),
            'view'   => Pages\ViewPayroll::route('/{record}'),
        ];",
        "return [
            'index'  => ListPayrolls::route('/'),
            'create' => CreatePayroll::route('/create'),
            'edit'   => EditPayroll::route('/{record}/edit'),
            'view'   => ViewPayroll::route('/{record}'),
        ];",
        $c
    );
}
file_put_contents($f, $c);
echo "Payroll create/edit pages enabled\n";

// ============================================================
// 2. FIX PAYSLIP - add route and fix controller
// ============================================================
$routes = file_get_contents('routes/web.php');
if (strpos($routes, 'payslip') === false) {
    $routes .= "
Route::middleware(['auth:web'])->group(function () {
    Route::get('/payslip/{id}', [App\Http\Controllers\ReportsController::class, 'payslip'])->name('payslip.employee');
    Route::get('/reports/employees/csv', [App\Http\Controllers\ReportsController::class, 'employeesCsv'])->name('reports.employees.csv');
    Route::get('/reports/payroll/csv', [App\Http\Controllers\ReportsController::class, 'payrollCsv'])->name('reports.payroll.csv');
});
";
    file_put_contents('routes/web.php', $routes);
    echo "Payslip routes added\n";
} else {
    echo "Payslip routes exist\n";
}

// Fix ReportsController
if (!is_dir('app/Http/Controllers')) mkdir('app/Http/Controllers', 0755, true);
file_put_contents('app/Http/Controllers/ReportsController.php', '<?php
namespace App\Http\Controllers;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Attendance;

class ReportsController extends Controller
{
    public function payslip($id)
    {
        if (!auth()->user()?->hasAnyRole(["super_admin","admin"])) abort(403);
        $employee = Employee::with(["department","position"])->findOrFail($id);
        $payroll  = Payroll::where("employee_id", $id)->latest("pay_date")->first();
        if (!$payroll) return back()->with("error", "No payroll found for this employee.");

        $gross           = $payroll->gross_pay ?? 0;
        $deductions      = is_array($payroll->deductions) ? $payroll->deductions : json_decode($payroll->deductions ?? "[]", true);
        $allowances      = is_array($payroll->allowances) ? $payroll->allowances : json_decode($payroll->allowances ?? "[]", true);
        $bonuses         = is_array($payroll->bonuses)    ? $payroll->bonuses    : json_decode($payroll->bonuses    ?? "[]", true);
        $nssfEmployee    = $payroll->nssf_employee_amount ?? round($gross * 0.05);
        $nssfEmployer    = $payroll->nssf_employer_amount ?? round($gross * 0.10);
        $paye            = $payroll->paye_amount          ?? 0;
        $lst             = $payroll->lst_amount           ?? 0;
        $totalDeductions = $nssfEmployee + $paye + $lst;
        $totalAllowances = collect($allowances)->sum() + collect($bonuses)->sum();
        $netPay          = $payroll->net_pay              ?? ($gross - $totalDeductions + $totalAllowances);

        return view("payslip", compact(
            "employee","payroll","gross","nssfEmployee","nssfEmployer",
            "paye","lst","totalDeductions","totalAllowances","netPay",
            "deductions","allowances","bonuses"
        ));
    }

    public function employeesCsv()
    {
        if (!auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant"])) abort(403);
        $employees = Employee::with(["department"])->whereNotIn("employee_code",["SYS-001"])->get();
        $csv = "Code,First Name,Last Name,Email,Department,Type,Gender,Hire Date,Status\n";
        foreach ($employees as $e) {
            $csv .= implode(",", [$e->employee_code,$e->first_name,$e->last_name,$e->email,
                $e->department?->name,"N/A",$e->employment_type,$e->gender,$e->hire_date,
                $e->is_active?"Active":"Inactive"]) . "\n";
        }
        return response($csv, 200, [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=employees_".date("Y-m-d").".csv",
        ]);
    }

    public function payrollCsv()
    {
        if (!auth()->user()?->hasAnyRole(["super_admin","admin"])) abort(403);
        $payrolls = Payroll::with("employee")->orderByDesc("pay_date")->get();
        $csv = "Employee,Period,Gross Pay,Net Pay,Status,Pay Date\n";
        foreach ($payrolls as $p) {
            $csv .= implode(",", [
                $p->employee?->first_name." ".$p->employee?->last_name,
                $p->period,$p->gross_pay,$p->net_pay,$p->status,$p->pay_date
            ]) . "\n";
        }
        return response($csv, 200, [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=payroll_".date("Y-m-d").".csv",
        ]);
    }
}');
echo "ReportsController fixed\n";

// ============================================================
// 3. REMOVE DUPLICATE ATTENDANCE FROM HR PANEL
// ============================================================
// Keep QuickAttendance page but remove the copied AttendanceResource from HR
$toRemove = 'app/Filament/Employee/Resources/Attendances';
if (is_dir($toRemove)) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($toRemove, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $f) {
        $f->isDir() ? rmdir($f) : unlink($f);
    }
    rmdir($toRemove);
    echo "Duplicate AttendanceResource removed from HR panel\n";
}

// ============================================================
// 4. ADD LANGUAGE TOGGLE TO HR PANEL
// ============================================================
$hrProvider = file_get_contents('app/Providers/Filament/EmployeePanelProvider.php');
if (strpos($hrProvider, 'SetLanguage') === false) {
    $hrProvider = str_replace(
        'use App\Http\Middleware\SetLanguage;',
        '',
        $hrProvider
    );
    $hrProvider = str_replace(
        "use Illuminate\Cookie\Middleware\EncryptCookies;",
        "use Illuminate\Cookie\Middleware\EncryptCookies;
use App\Http\Middleware\SetLanguage;",
        $hrProvider
    );
    $hrProvider = str_replace(
        'SubstituteBindings::class,
                DisableBladeIconComponents::class,',
        'SubstituteBindings::class,
                DisableBladeIconComponents::class,
                SetLanguage::class,',
        $hrProvider
    );
    file_put_contents('app/Providers/Filament/EmployeePanelProvider.php', $hrProvider);
    echo "Language toggle added to HR panel\n";
} else {
    echo "Language toggle already in HR panel\n";
}

// ============================================================
// 5. WHITELABEL CONFIG FILE
// ============================================================
file_put_contents('config/app_branding.php', '<?php
return [
    "name"          => env("APP_BRAND_NAME",    "CRBC Uganda HRM"),
    "short_name"    => env("APP_BRAND_SHORT",   "CRBC HRM"),
    "company"       => env("APP_COMPANY_NAME",  "CRBC Uganda Ltd"),
    "project"       => env("APP_PROJECT_NAME",  "Kayunga-Bbaale-Galiraya Road (87KM)"),
    "currency"      => env("APP_CURRENCY",      "UGX"),
    "currency_symbol" => env("APP_CURRENCY_SYM","UGX"),
    "country"       => env("APP_COUNTRY",       "Uganda"),
    "timezone"      => env("APP_TIMEZONE",      "Africa/Kampala"),
    "logo_path"     => env("APP_LOGO_PATH",     null),
    "primary_color" => env("APP_PRIMARY_COLOR", "#6366f1"),
    "admin_email"   => env("APP_ADMIN_EMAIL",   "admin@company.com"),
    "support_email" => env("APP_SUPPORT_EMAIL", "support@company.com"),
    "paye_enabled"  => env("APP_PAYE_ENABLED",  true),
    "nssf_enabled"  => env("APP_NSSF_ENABLED",  true),
    "working_hours" => env("APP_WORKING_HOURS", "08:00"),
    "late_threshold"=> env("APP_LATE_THRESHOLD","08:30"),
];
');
echo "Whitelabel config created\n";

// Add branding vars to .env if missing
$env = file_get_contents('.env');
if (strpos($env, 'APP_BRAND_NAME') === false) {
    $env .= '

# ── App Branding (Whitelabel) ─────────────────────────────
APP_BRAND_NAME="CRBC Uganda HRM"
APP_BRAND_SHORT="CRBC HRM"
APP_COMPANY_NAME="CRBC Uganda Ltd"
APP_PROJECT_NAME="Kayunga-Bbaale-Galiraya Road (87KM)"
APP_CURRENCY=UGX
APP_CURRENCY_SYM=UGX
APP_COUNTRY=Uganda
APP_ADMIN_EMAIL=einsteinbernard3000@gmail.com
APP_WORKING_HOURS=08:00
APP_LATE_THRESHOLD=08:30
';
    file_put_contents('.env', $env);
    echo ".env branding vars added\n";
}

// ============================================================
// 6. DEMO DATA SEEDER
// ============================================================
file_put_contents('database/seeders/DemoDataSeeder.php', '<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Employee,Attendance,Leave,SiteExpense,Correspondence,Task,Payroll};
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::whereNotIn("employee_code",["SYS-001"])->get();

        // Attendance last 30 days
        foreach (range(30, 1) as $daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);
            if ($date->isWeekend()) continue;
            foreach ($employees->random(min(12, $employees->count())) as $emp) {
                $late = rand(0,5) === 0;
                Attendance::firstOrCreate(
                    ["employee_id"=>$emp->id,"date"=>$date->toDateString()],
                    ["clock_in"=>$late?"08:".rand(35,59):"07:".rand(30,59),
                     "clock_out"=>"17:".rand(0,30),"is_late"=>$late,"status"=>"present"]
                );
            }
        }
        echo "✅ Attendance seeded\n";

        // Payrolls
        foreach ($employees->take(8) as $emp) {
            $gross = rand(800000, 3500000);
            Payroll::firstOrCreate(
                ["employee_id"=>$emp->id,"period"=>now()->format("Y-m")],
                ["gross_pay"=>$gross,"net_pay"=>round($gross*0.78),
                 "pay_date"=>now()->startOfMonth()->addDays(25),
                 "status"=>"completed","paye_amount"=>round($gross*0.12),
                 "nssf_employee_amount"=>round($gross*0.05),
                 "nssf_employer_amount"=>round($gross*0.10),
                 "lst_amount"=>25000]
            );
        }
        echo "✅ Payrolls seeded\n";

        echo "Demo data complete!\n";
    }
}
');
echo "DemoDataSeeder created\n";

// ============================================================
// 7. FRESH INSTALL SEEDER (wipes client data, keeps structure)
// ============================================================
file_put_contents('database/seeders/FreshInstallSeeder.php', '<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FreshInstallSeeder extends Seeder
{
    public function run(): void
    {
        // Wipe all operational data
        $tables = [
            "attendances","leaves","payrolls","site_expenses",
            "correspondences","correspondence_followups","tasks",
            "employee_documents","disciplinary_records","notifications",
            "activity_log","messages"
        ];
        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
                echo "Cleared: {$table}\n";
            }
        }

        // Keep only system admin, wipe other employees
        DB::table("model_has_roles")
            ->whereIn("model_id",
                DB::table("employees")
                ->whereNotIn("employee_code",["SYS-001"])
                ->pluck("id"))
            ->delete();
        DB::table("employees")
            ->whereNotIn("employee_code",["SYS-001"])
            ->delete();

        echo "Fresh install complete - ready for new client!\n";
        echo "Run: php artisan db:seed --class=RolePermissionSeeder\n";
    }
}
');
echo "FreshInstallSeeder created\n";

echo "\n=== ALL DONE ===\n";
echo "Run: php artisan optimize:clear && php artisan serve --host=0.0.0.0 --port=8000\n";
