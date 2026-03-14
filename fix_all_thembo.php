<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// ============================================================
// 1. FIX FORM INPUT TEXT COLOR - dark text on white fields
// ============================================================
$globalCss = <<<'CSS'

/* ── Fix ALL form inputs across entire system ───────────── */
input[type="text"],
input[type="email"],
input[type="password"],
input[type="number"],
input[type="date"],
input[type="tel"],
input[type="url"],
input[type="search"],
textarea,
select,
.fi-input,
.fi-select-input,
.fi-textarea {
    color: #0f172a !important;
    background-color: #ffffff !important;
}
input::placeholder,
textarea::placeholder {
    color: #94a3b8 !important;
}
/* Dark mode inputs */
.dark input[type="text"],
.dark input[type="email"],
.dark input[type="password"],
.dark input[type="number"],
.dark textarea,
.dark select,
.dark .fi-input {
    color: #f1f5f9 !important;
    background-color: #1e293b !important;
}
CSS;

$existing = file_get_contents('public/css/login-custom.css');
file_put_contents('public/css/login-custom.css', $existing . $globalCss);
echo "Form input colors fixed\n";

// ============================================================
// 2. HIDE SYSTEM ADMIN FROM EMPLOYEE LISTS
// ============================================================
// Fix EmployeeResource to exclude SYS-001
$empResource = file_get_contents('app/Filament/Resources/Employees/EmployeeResource.php');
if (strpos($empResource, 'SYS-001') === false) {
    $empResource = str_replace(
        'public static function getEloquentQuery(): Builder',
        'public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNotIn("employee_code", ["SYS-001"]);
    }

    public static function getEloquentQuery_ORIG(): Builder',
        $empResource
    );
    file_put_contents('app/Filament/Resources/Employees/EmployeeResource.php', $empResource);
    echo "SYS-001 hidden from admin employee list\n";
} else {
    echo "SYS-001 already hidden in admin\n";
}

// Fix HR Employee Resource too
$hrEmpResource = 'app/Filament/Employee/Resources/Employees/EmployeeResource.php';
if (file_exists($hrEmpResource)) {
    $c = file_get_contents($hrEmpResource);
    if (strpos($c, 'SYS-001') === false) {
        $c = str_replace(
            'public static function getEloquentQuery(): Builder',
            'public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNotIn("employee_code", ["SYS-001"]);
    }

    public static function getEloquentQuery_ORIG(): Builder',
            $c
        );
        file_put_contents($hrEmpResource, $c);
        echo "SYS-001 hidden from HR employee list\n";
    }
}

// ============================================================
// 3. QUICK ATTENDANCE - bulk tick system
// ============================================================
if (!is_dir('app/Filament/Pages')) mkdir('app/Filament/Pages', 0755, true);

file_put_contents('app/Filament/Pages/QuickAttendance.php', '<?php
namespace App\Filament\Pages;

use App\Models\Employee;
use App\Models\Attendance;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class QuickAttendance extends Page
{
    protected static string $view = "filament.pages.quick-attendance";
    protected static ?string $navigationIcon = "heroicon-o-clock";
    protected static ?string $navigationLabel = "Quick Attendance";
    protected static ?string $navigationGroup = "HR Management";
    protected static ?int $navigationSort = 2;

    public array $attendance = [];
    public string $date;
    public string $defaultTimeIn  = "08:00";
    public string $defaultTimeOut = "17:00";

    public function mount(): void
    {
        $this->date = today()->toDateString();
        $employees  = Employee::where("is_active", true)
            ->whereNotIn("employee_code", ["SYS-001"])
            ->orderBy("first_name")->get();

        foreach ($employees as $emp) {
            $existing = Attendance::where("employee_id", $emp->id)
                ->whereDate("date", $this->date)->first();
            $this->attendance[$emp->id] = [
                "present"  => $existing ? true : false,
                "time_in"  => $existing?->clock_in  ?? $this->defaultTimeIn,
                "time_out" => $existing?->clock_out ?? $this->defaultTimeOut,
                "late"     => $existing?->is_late   ?? false,
                "name"     => $emp->first_name . " " . $emp->last_name,
                "code"     => $emp->employee_code,
                "dept"     => $emp->department?->name ?? "N/A",
            ];
        }
    }

    public function saveAttendance(): void
    {
        $saved = 0;
        foreach ($this->attendance as $empId => $data) {
            if (!$data["present"]) continue;

            $timeIn  = $data["time_in"]  ?? "08:00";
            $isLate  = $timeIn > "08:30";

            Attendance::updateOrCreate(
                ["employee_id" => $empId, "date" => $this->date],
                [
                    "clock_in"  => $timeIn,
                    "clock_out" => $data["time_out"] ?? "17:00",
                    "is_late"   => $isLate,
                    "remarks"   => $isLate ? "Late arrival" : null,
                    "status"    => "present",
                ]
            );
            $saved++;
        }

        Notification::make()
            ->title("Attendance saved for {$saved} employees")
            ->success()->send();

        $this->mount();
    }

    public function markAllPresent(): void
    {
        foreach ($this->attendance as $id => $data) {
            $this->attendance[$id]["present"] = true;
        }
    }

    public function markAllAbsent(): void
    {
        foreach ($this->attendance as $id => $data) {
            $this->attendance[$id]["present"] = false;
        }
    }

    protected function getActions(): array
    {
        return [
            Action::make("save")
                ->label("Save Attendance")
                ->icon("heroicon-o-check-circle")
                ->color("success")
                ->action("saveAttendance"),
            Action::make("all_present")
                ->label("All Present")
                ->icon("heroicon-o-user-group")
                ->color("info")
                ->action("markAllPresent"),
            Action::make("all_absent")
                ->label("Clear All")
                ->icon("heroicon-o-x-circle")
                ->color("danger")
                ->action("markAllAbsent"),
        ];
    }
}');
echo "QuickAttendance page created\n";

// ============================================================
// 4. QUICK ATTENDANCE BLADE VIEW
// ============================================================
if (!is_dir('resources/views/filament/pages')) {
    mkdir('resources/views/filament/pages', 0755, true);
}

file_put_contents('resources/views/filament/pages/quick-attendance.blade.php', '
<x-filament-panels::page>
<style>
.att-row { display:grid; grid-template-columns:32px 1fr 120px 100px 100px 80px; gap:10px; align-items:center; padding:10px 14px; border-radius:8px; margin-bottom:6px; }
.att-row:hover { background:rgba(255,255,255,.04); }
.att-header { background:rgba(139,92,246,.15); font-size:11px; font-weight:700; color:#a78bfa; text-transform:uppercase; letter-spacing:.06em; }
.att-name { font-size:13px; font-weight:600; color:#f1f5f9; }
.att-dept { font-size:11px; color:rgba(255,255,255,.35); }
.att-code { font-size:10px; color:rgba(139,92,246,.7); font-family:monospace; }
.time-input { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.1); border-radius:6px; padding:4px 8px; color:#f1f5f9; font-size:12px; width:90px; }
.late-badge { background:rgba(245,158,11,.2); color:#fbbf24; padding:2px 8px; border-radius:20px; font-size:10px; font-weight:700; }
.present-check { width:18px; height:18px; accent-color:#8b5cf6; cursor:pointer; }
.stats-bar { display:flex; gap:16px; padding:12px 16px; background:rgba(139,92,246,.1); border-radius:10px; margin-bottom:16px; border:1px solid rgba(139,92,246,.2); }
</style>

{{-- Date selector & stats --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <div style="display:flex;align-items:center;gap:12px">
        <div style="color:#a78bfa;font-size:13px;font-weight:600">Date:</div>
        <input type="date" wire:model.live="date" style="background:rgba(255,255,255,.08);border:1px solid rgba(139,92,246,.3);border-radius:8px;padding:6px 12px;color:#f1f5f9;font-size:13px">
    </div>
    <div style="display:flex;gap:8px">
        <span style="background:rgba(16,185,129,.15);color:#34d399;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600">
            ✅ {{ collect($attendance)->where("present", true)->count() }} Present
        </span>
        <span style="background:rgba(239,68,68,.15);color:#f87171;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600">
            ❌ {{ collect($attendance)->where("present", false)->count() }} Absent
        </span>
        <span style="background:rgba(245,158,11,.15);color:#fbbf24;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600">
            ⚠️ {{ collect($attendance)->where("present", true)->filter(fn($a) => ($a["time_in"] ?? "08:00") > "08:30")->count() }} Late
        </span>
    </div>
</div>

{{-- Table header --}}
<div class="att-row att-header">
    <div>✓</div>
    <div>Employee</div>
    <div>Time In</div>
    <div>Time Out</div>
    <div>Status</div>
    <div>Late?</div>
</div>

{{-- Employee rows --}}
@foreach($attendance as $empId => $data)
@php $isLate = ($data["time_in"] ?? "08:00") > "08:30" && $data["present"]; @endphp
<div class="att-row" style="{{ $data["present"] ? "background:rgba(16,185,129,.05);border:1px solid rgba(16,185,129,.1)" : "border:1px solid rgba(255,255,255,.04)" }}">
    {{-- Checkbox --}}
    <input type="checkbox" class="present-check"
        wire:model.live="attendance.{{ $empId }}.present">

    {{-- Name --}}
    <div>
        <div class="att-name">{{ $data["name"] }}</div>
        <div style="display:flex;gap:6px;margin-top:2px">
            <span class="att-code">{{ $data["code"] }}</span>
            <span class="att-dept">· {{ $data["dept"] }}</span>
        </div>
    </div>

    {{-- Time In --}}
    <input type="time" class="time-input"
        wire:model.live="attendance.{{ $empId }}.time_in"
        {{ !$data["present"] ? "disabled" : "" }}
        style="{{ !$data["present"] ? "opacity:.3" : ($isLate ? "border-color:#f59e0b;color:#fbbf24" : "border-color:rgba(16,185,129,.4);color:#34d399") }}">

    {{-- Time Out --}}
    <input type="time" class="time-input"
        wire:model.live="attendance.{{ $empId }}.time_out"
        {{ !$data["present"] ? "disabled" : "" }}
        style="{{ !$data["present"] ? "opacity:.3" : "" }}">

    {{-- Status --}}
    <div>
        @if($data["present"])
            <span style="background:rgba(16,185,129,.15);color:#34d399;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">Present</span>
        @else
            <span style="background:rgba(239,68,68,.1);color:#f87171;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">Absent</span>
        @endif
    </div>

    {{-- Late --}}
    <div>
        @if($isLate)
            <span class="late-badge">LATE</span>
        @endif
    </div>
</div>
@endforeach

{{-- Bottom save button --}}
<div style="margin-top:16px;text-align:right">
    <button wire:click="saveAttendance"
        style="background:linear-gradient(135deg,#7c3aed,#4f46e5);color:white;border:none;border-radius:10px;padding:10px 28px;font-size:14px;font-weight:700;cursor:pointer;box-shadow:0 4px 15px rgba(124,58,237,.4)">
        💾 Save Attendance
    </button>
</div>
</x-filament-panels::page>
');
echo "Quick attendance view created\n";

// ============================================================
// 5. ADD EDIT HISTORY TO EXPENSES (audit trail)
// ============================================================
// SiteExpense model - add observer for tracking changes
file_put_contents('app/Observers/SiteExpenseObserver.php', '<?php
namespace App\Observers;
use App\Models\SiteExpense;
use Illuminate\Support\Facades\Auth;

class SiteExpenseObserver
{
    public function updating(SiteExpense $expense): void
    {
        $changes = [];
        foreach ($expense->getDirty() as $field => $newValue) {
            $oldValue = $expense->getOriginal($field);
            if ($oldValue != $newValue) {
                $changes[] = "{$field}: {$oldValue} → {$newValue}";
            }
        }
        if (!empty($changes)) {
            activity("expense_audit")
                ->performedOn($expense)
                ->causedBy(Auth::user())
                ->withProperties(["changes" => $changes, "editor" => Auth::user()?->email])
                ->log("Expense updated: " . implode(", ", $changes));
        }
    }
}');
echo "SiteExpenseObserver created\n";

// Register observer in AppServiceProvider
$provider = file_get_contents('app/Providers/AppServiceProvider.php');
if (strpos($provider, 'SiteExpenseObserver') === false) {
    $provider = str_replace(
        'public function boot(): void
    {',
        'public function boot(): void
    {
        \App\Models\SiteExpense::observe(\App\Observers\SiteExpenseObserver::class);',
        $provider
    );
    file_put_contents('app/Providers/AppServiceProvider.php', $provider);
    echo "Observer registered\n";
}

// ============================================================
// 6. COPY QUICK ATTENDANCE TO HR PANEL TOO
// ============================================================
if (!is_dir('app/Filament/Employee/Pages')) {
    mkdir('app/Filament/Employee/Pages', 0755, true);
}

$qaContent = file_get_contents('app/Filament/Pages/QuickAttendance.php');
$qaContent = str_replace(
    'namespace App\Filament\Pages;',
    'namespace App\Filament\Employee\Pages;',
    $qaContent
);
file_put_contents('app/Filament/Employee/Pages/QuickAttendance.php', $qaContent);
echo "QuickAttendance added to HR panel\n";

echo "\n=== ALL DONE ===\n";
echo "Run: php artisan optimize:clear && php artisan serve --host=0.0.0.0 --port=8000\n";
