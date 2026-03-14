<?php
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
}