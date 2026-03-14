<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// ============================================================
// 1. COPY MISSING RESOURCES TO HR PANEL
// ============================================================
$resources = [
    // From => To
    'app/Filament/Resources/Expenses/SiteExpenseResource.php'           => 'app/Filament/Employee/Resources/Expenses/SiteExpenseResource.php',
    'app/Filament/Resources/Disciplinary/DisciplinaryResource.php'      => 'app/Filament/Employee/Resources/Disciplinary/DisciplinaryResource.php',
    'app/Filament/Resources/Correspondences/CorrespondenceResource.php' => 'app/Filament/Employee/Resources/Correspondences/CorrespondenceResource.php',
    'app/Filament/Resources/Departments/DepartmentResource.php'         => 'app/Filament/Employee/Resources/Departments/DepartmentResource.php',
];

foreach ($resources as $from => $to) {
    $dir = dirname($to);
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    if (file_exists($from)) {
        $content = file_get_contents($from);
        // Change namespace to Employee panel
        $content = str_replace(
            'namespace App\Filament\Resources\\',
            'namespace App\Filament\Employee\Resources\\',
            $content
        );
        file_put_contents($to, $content);
        echo "Copied: " . basename($to) . "\n";
    }
}

// ============================================================
// 2. BEAUTIFUL HR DASHBOARD WITH CHARTS
// ============================================================
if (!is_dir('resources/views/filament/employee/pages')) {
    mkdir('resources/views/filament/employee/pages', 0755, true);
}

$totalEmployees  = App\Models\Employee::whereNotIn('employee_code', ['SYS-001'])->count();
$presentToday    = App\Models\Attendance::whereDate('date', today())->count();
$pendingLeaves   = App\Models\Leave::where('status', 'pending')->count();
$pendingExpenses = App\Models\SiteExpense::where('status', 'pending')->count();
$overdueCorr     = App\Models\Correspondence::whereNotIn('status', ['closed', 'responded'])
    ->whereNotNull('response_due_date')->where('response_due_date', '<', now())->count();

file_put_contents('resources/views/filament/employee/pages/dashboard.blade.php', '
@php
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\SiteExpense;
use App\Models\Correspondence;
use App\Models\Payroll;
use Carbon\Carbon;

$user = auth()->user();
$totalEmployees  = Employee::whereNotIn("employee_code", ["SYS-001"])->count();
$activeEmployees = Employee::where("is_active", true)->whereNotIn("employee_code", ["SYS-001"])->count();
$presentToday    = Attendance::whereDate("date", today())->count();
$pendingLeaves   = Leave::where("status", "pending")->count();
$approvedLeaves  = Leave::where("status", "approved")->whereMonth("start_date", now()->month)->count();
$pendingExpenses = SiteExpense::where("status", "pending")->count();
$totalExpThisMonth = SiteExpense::where("status", "approved")->whereMonth("expense_date", now()->month)->sum("amount");
$overdueCorr     = Correspondence::whereNotIn("status", ["closed","responded"])->whereNotNull("response_due_date")->where("response_due_date","<",now())->count();
$openCorr        = Correspondence::whereNotIn("status", ["closed"])->count();

// Attendance last 7 days for chart
$attendanceData = collect(range(6, 0))->map(function($daysAgo) {
    $date = Carbon::today()->subDays($daysAgo);
    return [
        "day"   => $date->format("D"),
        "count" => Attendance::whereDate("date", $date)->count(),
    ];
});

// Leave by status
$leaveStats = [
    "pending"  => Leave::where("status","pending")->count(),
    "approved" => Leave::where("status","approved")->count(),
    "rejected" => Leave::where("status","rejected")->count(),
];

// Expense by category this month
$expByCategory = SiteExpense::whereMonth("expense_date", now()->month)
    ->get()->groupBy("category")
    ->map(fn($g) => $g->sum("amount"))->sortDesc()->take(5);

// Recent leaves
$recentLeaves = Leave::with("employee")->latest()->take(5)->get();

// Recent expenses
$recentExpenses = SiteExpense::with("employee")->latest()->take(5)->get();

$maxAttendance = $attendanceData->max("count") ?: 1;
@endphp

<x-filament-panels::page>
<style>
.hr-card { background:#1e293b; border:1px solid rgba(255,255,255,.07); border-radius:12px; padding:20px; }
.hr-card-danger { background:rgba(239,68,68,.07); border:1px solid rgba(239,68,68,.2); border-radius:12px; padding:20px; }
.stat-value { font-size:2rem; font-weight:800; line-height:1; }
.stat-label { font-size:11px; color:rgba(255,255,255,.4); text-transform:uppercase; letter-spacing:.08em; margin-top:4px; }
.stat-sub { font-size:12px; color:rgba(255,255,255,.3); margin-top:6px; }
.section-title { font-size:13px; font-weight:700; color:#f1f5f9; margin-bottom:14px; text-transform:uppercase; letter-spacing:.06em; }
.bar { border-radius:4px 4px 0 0; background:linear-gradient(180deg,#8b5cf6,#4f46e5); transition:height .3s; }
.pill { padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
</style>

{{-- Welcome strip --}}
<div style="background:linear-gradient(135deg,#1e1b4b,#312e81);border-radius:14px;padding:20px 24px;margin-bottom:20px;border:1px solid rgba(139,92,246,.3);display:flex;align-items:center;justify-content:space-between">
    <div>
        <div style="color:#a5b4fc;font-size:12px;font-weight:600;margin-bottom:4px;text-transform:uppercase;letter-spacing:.08em">HR Assistant Portal</div>
        <div style="color:white;font-size:20px;font-weight:800">Welcome, {{ $user->first_name }} 👋</div>
        <div style="color:rgba(255,255,255,.4);font-size:12px;margin-top:4px">{{ now()->format("l, d F Y") }} · Kayunga-Bbaale-Galiraya Road</div>
    </div>
    <div style="text-align:right">
        <div style="color:#fbbf24;font-size:22px;font-weight:800">{{ $activeEmployees }}</div>
        <div style="color:rgba(255,255,255,.4);font-size:11px">Active Staff</div>
    </div>
</div>

{{-- Top stat cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:20px">
    <div class="hr-card">
        <div class="stat-value" style="color:#a78bfa">{{ $totalEmployees }}</div>
        <div class="stat-label">Total Staff</div>
        <div class="stat-sub">{{ $activeEmployees }} active</div>
    </div>
    <div class="hr-card" style="border-color:rgba(16,185,129,.2)">
        <div class="stat-value" style="color:#34d399">{{ $presentToday }}</div>
        <div class="stat-label">Present Today</div>
        <div class="stat-sub">{{ today()->format("d M") }}</div>
    </div>
    <div class="hr-card" style="border-color:rgba(245,158,11,.2)">
        <div class="stat-value" style="color:#fbbf24">{{ $pendingLeaves }}</div>
        <div class="stat-label">Pending Leaves</div>
        <div class="stat-sub">{{ $approvedLeaves }} approved this month</div>
    </div>
    <div class="hr-card" style="border-color:rgba(245,158,11,.2)">
        <div class="stat-value" style="color:#fb923c">{{ $pendingExpenses }}</div>
        <div class="stat-label">Pending Expenses</div>
        <div class="stat-sub">UGX {{ number_format($totalExpThisMonth) }} approved</div>
    </div>
    @if($overdueCorr > 0)
    <div class="hr-card-danger">
        <div class="stat-value" style="color:#f87171">{{ $overdueCorr }}</div>
        <div class="stat-label">Overdue Correspondence</div>
        <div class="stat-sub">Needs response now</div>
    </div>
    @endif
    <div class="hr-card" style="border-color:rgba(99,102,241,.2)">
        <div class="stat-value" style="color:#818cf8">{{ $openCorr }}</div>
        <div class="stat-label">Open Correspondence</div>
        <div class="stat-sub">{{ $openCorr }} active items</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
    {{-- Attendance chart --}}
    <div class="hr-card">
        <div class="section-title">📅 Attendance — Last 7 Days</div>
        <div style="display:flex;align-items:flex-end;gap:8px;height:100px;padding-top:10px">
            @foreach($attendanceData as $day)
            @php $pct = $maxAttendance > 0 ? ($day["count"]/$maxAttendance)*100 : 0; @endphp
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px">
                <div style="font-size:10px;color:rgba(255,255,255,.5)">{{ $day["count"] }}</div>
                <div class="bar" style="width:100%;height:{{ max(4, $pct) }}px"></div>
                <div style="font-size:10px;color:rgba(255,255,255,.4)">{{ $day["day"] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Leave breakdown --}}
    <div class="hr-card">
        <div class="section-title">🏖️ Leave Status</div>
        <div style="display:flex;flex-direction:column;gap:10px;margin-top:8px">
            @php
            $total = array_sum($leaveStats) ?: 1;
            $lColors = ["pending"=>"#f59e0b","approved"=>"#10b981","rejected"=>"#ef4444"];
            $lLabels = ["pending"=>"Pending","approved"=>"Approved","rejected"=>"Rejected"];
            @endphp
            @foreach($leaveStats as $status => $count)
            @php $pct = round(($count/$total)*100); @endphp
            <div>
                <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                    <span style="color:rgba(255,255,255,.6);font-size:12px">{{ $lLabels[$status] }}</span>
                    <span style="color:#f1f5f9;font-size:12px;font-weight:600">{{ $count }}</span>
                </div>
                <div style="background:rgba(255,255,255,.06);border-radius:4px;height:6px">
                    <div style="background:{{ $lColors[$status] }};height:6px;border-radius:4px;width:{{ $pct }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
    {{-- Recent leave requests --}}
    <div class="hr-card">
        <div class="section-title">📋 Recent Leave Requests</div>
        @forelse($recentLeaves as $leave)
        @php
        $sc = ["pending"=>"#f59e0b","approved"=>"#10b981","rejected"=>"#ef4444"];
        $c = $sc[$leave->status] ?? "#6b7280";
        @endphp
        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.05)">
            <div>
                <div style="color:#f1f5f9;font-size:12px;font-weight:600">{{ $leave->employee?->first_name }} {{ $leave->employee?->last_name }}</div>
                <div style="color:rgba(255,255,255,.3);font-size:11px">{{ $leave->leave_type ?? "Leave" }} · {{ \Carbon\Carbon::parse($leave->start_date)->format("d M") }}</div>
            </div>
            <span class="pill" style="background:{{ $c }}22;color:{{ $c }}">{{ ucfirst($leave->status) }}</span>
        </div>
        @empty
        <div style="color:rgba(255,255,255,.2);font-size:12px;text-align:center;padding:20px">No leave requests</div>
        @endforelse
        <a href="/hr/leaves" style="color:#a78bfa;font-size:12px;text-decoration:none;display:block;margin-top:10px">View all →</a>
    </div>

    {{-- Recent expenses --}}
    <div class="hr-card">
        <div class="section-title">💰 Recent Expenses</div>
        @forelse($recentExpenses as $exp)
        @php
        $sc = ["pending"=>"#f59e0b","approved"=>"#10b981","rejected"=>"#ef4444"];
        $c = $sc[$exp->status] ?? "#6b7280";
        @endphp
        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.05)">
            <div>
                <div style="color:#f1f5f9;font-size:12px;font-weight:600">{{ \Str::limit($exp->title, 25) }}</div>
                <div style="color:rgba(255,255,255,.3);font-size:11px">UGX {{ number_format($exp->amount) }} · {{ ucfirst($exp->category) }}</div>
            </div>
            <span class="pill" style="background:{{ $c }}22;color:{{ $c }}">{{ ucfirst($exp->status) }}</span>
        </div>
        @empty
        <div style="color:rgba(255,255,255,.2);font-size:12px;text-align:center;padding:20px">No expenses</div>
        @endforelse
        <a href="/hr/expenses" style="color:#a78bfa;font-size:12px;text-decoration:none;display:block;margin-top:10px">View all →</a>
    </div>
</div>

{{-- Expense breakdown by category --}}
@if($expByCategory->count() > 0)
<div class="hr-card">
    <div class="section-title">📊 Expense Breakdown — {{ now()->format("F Y") }}</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px">
    @php $totalExp = $expByCategory->sum() ?: 1; @endphp
    @foreach($expByCategory as $cat => $amt)
    @php $pct = round(($amt/$totalExp)*100); @endphp
    <div style="background:rgba(139,92,246,.08);border-radius:10px;padding:12px;border:1px solid rgba(139,92,246,.15)">
        <div style="color:rgba(255,255,255,.5);font-size:10px;text-transform:capitalize;margin-bottom:6px">{{ str_replace("_"," ",$cat) }}</div>
        <div style="color:#f1f5f9;font-size:14px;font-weight:700">UGX {{ number_format($amt) }}</div>
        <div style="background:rgba(255,255,255,.06);border-radius:4px;height:4px;margin-top:8px">
            <div style="background:linear-gradient(90deg,#8b5cf6,#4f46e5);height:4px;border-radius:4px;width:{{ $pct }}%"></div>
        </div>
        <div style="color:rgba(255,255,255,.3);font-size:10px;margin-top:4px">{{ $pct }}% of total</div>
    </div>
    @endforeach
    </div>
</div>
@endif

</x-filament-panels::page>
');
echo "HR Dashboard built\n";

// ============================================================
// 3. UPDATE EMPLOYEE PANEL PROVIDER - add new resources
// ============================================================
$provider = file_get_contents('app/Providers/Filament/EmployeePanelProvider.php');
if (strpos($provider, 'Expenses') === false) {
    $provider = str_replace(
        "->discoverResources(in: app_path('Filament/Employee/Resources'), for: 'App\\\\Filament\\\\Employee\\\\Resources')",
        "->discoverResources(in: app_path('Filament/Employee/Resources'), for: 'App\\\\Filament\\\\Employee\\\\Resources')",
        $provider
    );
    file_put_contents('app/Providers/Filament/EmployeePanelProvider.php', $provider);
}

// Add navigation groups
$provider = file_get_contents('app/Providers/Filament/EmployeePanelProvider.php');
if (strpos($provider, 'Project') === false) {
    $provider = str_replace(
        "->navigationGroups([
                'HR Management'
            ])",
        "->navigationGroups([
                'HR Management',
                'Project',
                'Finance',
            ])",
        $provider
    );
    file_put_contents('app/Providers/Filament/EmployeePanelProvider.php', $provider);
    echo "Navigation groups updated\n";
}

echo "\nAll done!\n";
echo "Run: php artisan optimize:clear && php artisan serve --host=0.0.0.0 --port=8000\n";
