<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;

class ReportsController extends Controller
{
    public function payslip($id)
    {
        if (!auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant"])) abort(403);

        $employee = Employee::with(["department","position"])->findOrFail($id);
        $payroll  = Payroll::where("employee_id", $id)->latest("pay_date")->first();

        // Work with or without payroll data
        $gross      = $payroll?->gross_pay ?? 0;
        $period     = $payroll?->period    ?? now()->format("Y-m");
        $payDate    = $payroll?->pay_date  ?? now()->toDateString();
        $status     = $payroll?->status    ?? "pending";
        $notes      = $payroll?->notes     ?? "";

        // Parse deductions JSON
        $deductionsRaw = $payroll?->deductions ?? [];
        if (is_string($deductionsRaw)) {
            $deductionsRaw = json_decode($deductionsRaw, true) ?? [];
        }

        // Parse allowances JSON
        $allowancesRaw = $payroll?->allowances ?? [];
        if (is_string($allowancesRaw)) {
            $allowancesRaw = json_decode($allowancesRaw, true) ?? [];
        }

        // Parse bonuses JSON
        $bonusesRaw = $payroll?->bonuses ?? [];
        if (is_string($bonusesRaw)) {
            $bonusesRaw = json_decode($bonusesRaw, true) ?? [];
        }

        // Extract statutory deductions from deductions JSON
        $paye         = (float) ($deductionsRaw["PAYE"]          ?? $deductionsRaw["paye"]         ?? 0);
        $nssfEmployee = (float) ($deductionsRaw["NSSF Employee"]  ?? $deductionsRaw["nssf_employee"] ?? round($gross * 0.05));
        $lst          = (float) ($deductionsRaw["LST"]            ?? $deductionsRaw["lst"]           ?? 0);
        $nssfEmployer = round($gross * 0.10);

        // Other deductions (non-statutory)
        $otherDeductions = collect($deductionsRaw)
            ->filter(fn($v, $k) => !in_array($k, ["PAYE","paye","NSSF Employee","nssf_employee","LST","lst"]))
            ->toArray();

        $totalStatutory  = $paye + $nssfEmployee + $lst;
        $totalOther      = collect($otherDeductions)->sum();
        $totalDeductions = $totalStatutory + $totalOther;
        $totalAllowances = collect($allowancesRaw)->sum() + collect($bonusesRaw)->sum();
        $netPay          = $payroll?->net_pay ?? ($gross - $totalDeductions + $totalAllowances);

        return view("payslip", compact(
            "employee", "payroll", "gross", "period", "payDate", "status", "notes",
            "paye", "nssfEmployee", "nssfEmployer", "lst",
            "totalStatutory", "totalDeductions", "totalAllowances", "netPay",
            "allowancesRaw", "bonusesRaw", "otherDeductions"
        ));
    }

    public function employeesCsv()
    {
        if (!auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant"])) abort(403);
        $employees = Employee::with(["department"])
            ->whereNotIn("employee_code", ["SYS-001"])->get();
        $csv = "Code,First Name,Last Name,Email,Department,Type,Gender,Hire Date,Status\n";
        foreach ($employees as $e) {
            $csv .= implode(",", [
                $e->employee_code, $e->first_name, $e->last_name, $e->email,
                $e->department?->name ?? "N/A", $e->employment_type,
                $e->gender, $e->hire_date, $e->is_active ? "Active" : "Inactive"
            ]) . "\n";
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
                $p->period, $p->gross_pay, $p->net_pay, $p->status, $p->pay_date
            ]) . "\n";
        }
        return response($csv, 200, [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=payroll_".date("Y-m-d").".csv",
        ]);
    }

    public function exportEmployeeCsv($id)
    {
        if (!auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant"])) abort(403);

        $employee   = \App\Models\Employee::with(["department","position"])->findOrFail($id);
        $attendances = \App\Models\Attendance::where("employee_id", $id)
            ->orderByDesc("date")->get();
        $leaves     = \App\Models\Leave::where("employee_id", $id)
            ->orderByDesc("start_date")->get();
        $payrolls   = \App\Models\Payroll::where("employee_id", $id)
            ->orderByDesc("pay_date")->get();

        $lines = [];

        // ── Personal Details ─────────────────────────────────
        $lines[] = "EMPLOYEE PROFILE EXPORT";
        $lines[] = "Generated:," . now()->format("d M Y H:i") . " EAT";
        $lines[] = "";
        $lines[] = "PERSONAL DETAILS";
        $lines[] = "Employee Code:," . $employee->employee_code;
        $lines[] = "Full Name:," . $employee->name;
        $lines[] = "Email:," . $employee->email;
        $lines[] = "Phone:," . ($employee->phone ?? "N/A");
        $lines[] = "Gender:," . ($employee->gender ?? "N/A");
        $lines[] = "Department:," . ($employee->department?->name ?? "N/A");
        $lines[] = "Employment Type:," . $employee->employment_type;
        $lines[] = "Hire Date:," . $employee->hire_date;
        $lines[] = "Status:," . ($employee->is_active ? "Active" : "Inactive");
        $lines[] = "NSSF Number:," . ($employee->nssf_number ?? "N/A");
        $lines[] = "Basic Salary:," . ($employee->basic_salary ?? "N/A");
        $lines[] = "";

        // ── Attendance History ───────────────────────────────
        $lines[] = "ATTENDANCE HISTORY (" . $attendances->count() . " records)";
        $lines[] = "Date,Clock In,Clock Out,Status,Late,Remarks";
        foreach ($attendances as $a) {
            $lines[] = implode(",", [
                $a->date,
                $a->clock_in  ?? "N/A",
                $a->clock_out ?? "N/A",
                $a->status    ?? "present",
                $a->is_late   ? "YES" : "No",
                $a->remarks   ?? "",
            ]);
        }
        $lines[] = "";

        // ── Leave History ────────────────────────────────────
        $lines[] = "LEAVE HISTORY (" . $leaves->count() . " records)";
        $lines[] = "Type,Start Date,End Date,Days,Status,Reason";
        foreach ($leaves as $l) {
            $start = \Carbon\Carbon::parse($l->start_date);
            $end   = \Carbon\Carbon::parse($l->end_date);
            $days  = $start->diffInDays($end) + 1;
            $lines[] = implode(",", [
                $l->leave_type  ?? "Leave",
                $l->start_date,
                $l->end_date,
                $days,
                ucfirst($l->status),
                str_replace(",", ";", $l->reason ?? ""),
            ]);
        }
        $lines[] = "";

        // ── Payroll History ──────────────────────────────────
        $lines[] = "PAYROLL HISTORY (" . $payrolls->count() . " records)";
        $lines[] = "Period,Pay Date,Gross Pay,Net Pay,Status";
        foreach ($payrolls as $p) {
            $lines[] = implode(",", [
                $p->period,
                $p->pay_date,
                $p->gross_pay,
                $p->net_pay,
                ucfirst($p->status),
            ]);
        }

        $csv = implode("\n", $lines);
        $filename = "employee_" . $employee->employee_code . "_" . date("Y-m-d") . ".csv";

        return response($csv, 200, [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
        ]);
    }

}