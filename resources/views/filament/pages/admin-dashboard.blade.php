@php
    use App\Models\Employee;
    use App\Models\Department;
    use App\Models\Attendance;
    use App\Models\Leave;
    use Carbon\Carbon;

    $totalEmployees    = Employee::withoutRole(["super_admin","viewer"])->where("employee_code","!=","SYS-001")->where("employee_code","!=","CRBC-VIEW")->count();
    $activeEmployees   = Employee::where('is_active', true)->count();
    $inactiveEmployees = Employee::where('is_active', false)->count();
    $totalDepartments  = Department::count();
    $hrAdmins          = Employee::role('admin')->count();
    $presentToday      = Attendance::whereDate('created_at', Carbon::today())->count();
    $pendingLeaves     = Leave::where('status', 'pending')->count();
    $approvedLeaves    = Leave::where('status', 'approved')->count();
    $newHires          = Employee::whereMonth('hire_date', Carbon::now()->month)->count();
    $onLeaveToday      = Leave::where('status','approved')->whereDate('start_date','<=',Carbon::today())->whereDate('end_date','>=',Carbon::today())->count();
    $absent            = max(0, $activeEmployees - $presentToday - $onLeaveToday);
    $attendancePct     = $activeEmployees > 0 ? round(($presentToday / $activeEmployees) * 100) : 0;
    $leavePct          = $activeEmployees > 0 ? round(($onLeaveToday / $activeEmployees) * 100) : 0;
    $absentPct         = $activeEmployees > 0 ? round(($absent / $activeEmployees) * 100) : 0;
    $daysLeft          = Carbon::now()->daysInMonth - Carbon::now()->day;
    $departments       = Department::withCount('employees')->orderByDesc('employees_count')->take(6)->get();
    $maxDeptCount      = $departments->max('employees_count') ?: 1;
    $docsCount         = class_exists('\App\Models\EmployeeDocument') ? \App\Models\EmployeeDocument::count() : 0;
    $disciplinaryOpen  = class_exists('\App\Models\DisciplinaryRecord') ? \App\Models\DisciplinaryRecord::where('status','open')->count() : 0;

    // Charts data
    // Hiring last 6 months
    $hiringLabels = [];
    $hiringData   = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = Carbon::now()->subMonths($i);
        $hiringLabels[] = $month->format('M');
        $hiringData[]   = Employee::whereYear('hire_date', $month->year)->whereMonth('hire_date', $month->month)->count();
    }

    // Gender distribution
    $maleCount   = Employee::where('gender', 'male')->count();
    $femaleCount = Employee::where('gender', 'female')->count();
    $otherCount  = Employee::whereNotIn('gender', ['male','female'])->orWhereNull('gender')->count();

    // Disciplinary by type
    $discTypes = [];
    $discCounts = [];
    if (class_exists('\App\Models\DisciplinaryRecord')) {
        $discData = \App\Models\DisciplinaryRecord::selectRaw('type, count(*) as total')->groupBy('type')->get();
        foreach ($discData as $d) {
            $discTypes[]  = ucwords(str_replace('_', ' ', $d->type));
            $discCounts[] = $d->total;
        }
    }

    // Payroll status
    $payrollPaid    = \App\Models\Payroll::where('status', 'paid')->whereMonth('pay_date', Carbon::now()->month)->count();
    $payrollPending = \App\Models\Payroll::where('status', 'pending')->whereMonth('pay_date', Carbon::now()->month)->count();
    $payrollDraft   = \App\Models\Payroll::where('status', 'draft')->whereMonth('pay_date', Carbon::now()->month)->count();

    // Department data for chart
    $deptNames  = $departments->pluck('name')->toArray();
    $deptCounts = $departments->pluck('employees_count')->toArray();

    $lang = session('locale', 'en');
    $isZh = $lang === 'zh_CN';
    $switchLang = $isZh ? 'en' : 'zh_CN';

    $tx = [
        'en' => [
            'project'        => 'DESIGN & BUILD OF KAYUNGA-BBAALE-GALIRAYA ROAD (87KM) INCLUDING LANDING SITE',
            'system'         => 'CRBC UGANDA HR MANAGEMENT SYSTEM',
            'designer'       => 'Designed & Developed by Eng. Bernard Nasinyama',
            'title'          => 'Admin Command Center',
            'subtitle'       => 'Full system oversight',
            'switch'         => '中文',
            'total_emp'      => 'Total Employees',
            'present'        => 'Present Today',
            'pending_lv'     => 'Pending Leaves',
            'depts'          => 'Departments',
            'docs'           => 'Documents',
            'disc'           => 'Disciplinary',
            'payroll'        => 'Payroll',
            'days_left'      => 'days left',
            'active'         => 'Active',
            'uploaded'       => 'uploaded',
            'open'           => 'open cases',
            'approved'       => 'approved',
            'att_rate'       => 'Attendance Rate',
            'quick'          => 'Quick Actions',
            'add_emp'        => 'Add Employee',
            'employees'      => 'Employees',
            'attendance'     => 'Attendance',
            'leaves'         => 'Leaves',
            'documents'      => 'Documents',
            'disciplinary'   => 'Disciplinary',
            'settings'       => 'Settings',
            'analytics'      => 'Analytics & Charts',
            'hiring_trend'   => 'Hiring Trend (6 Months)',
            'gender_dist'    => 'Gender Distribution',
            'disc_types'     => 'Disciplinary by Type',
            'dept_hc'        => 'Headcount by Department',
            'payroll_status' => 'Payroll Status (This Month)',
            'leave_overview' => 'Leave Overview',
            'workforce'      => 'Workforce Summary',
            'on_leave'       => 'On Leave',
            'absent'         => 'Absent',
            'total'          => 'Total',
            'paid'           => 'Paid',
            'draft'          => 'Draft',
            'male'           => 'Male',
            'female'         => 'Female',
            'other'          => 'Other',
            'new_hires'      => 'New Hires',
            'inactive'       => 'Inactive',
            'hr_admins'      => 'HR Admins',
            'view_leaves'    => 'View all leave requests →',
            'on_leave_now'   => 'Currently On Leave',
        ],
        'zh_CN' => [
            'project'        => '卡云加-巴勒-加利拉亚公路(87公里)设计与建设项目，含码头',
            'system'         => 'CRBC乌干达人力资源管理系统',
            'designer'       => '系统设计与开发：Bernard Nasinyama工程师',
            'title'          => '管理员控制中心',
            'subtitle'       => '系统全面监控',
            'switch'         => 'English',
            'total_emp'      => '员工总数',
            'present'        => '今日出勤',
            'pending_lv'     => '待审批假期',
            'depts'          => '部门数量',
            'docs'           => '文件管理',
            'disc'           => '纪律记录',
            'payroll'        => '工资管理',
            'days_left'      => '天后发薪',
            'active'         => '在职',
            'uploaded'       => '已上传',
            'open'           => '未结案例',
            'approved'       => '已批准',
            'att_rate'       => '出勤率',
            'quick'          => '快捷操作',
            'add_emp'        => '添加员工',
            'employees'      => '员工管理',
            'attendance'     => '考勤',
            'leaves'         => '请假',
            'documents'      => '文件管理',
            'disciplinary'   => '纪律记录',
            'settings'       => '设置',
            'analytics'      => '数据分析',
            'hiring_trend'   => '招聘趋势（近6个月）',
            'gender_dist'    => '性别分布',
            'disc_types'     => '纪律案例类型',
            'dept_hc'        => '各部门人数',
            'payroll_status' => '本月工资状态',
            'leave_overview' => '假期概览',
            'workforce'      => '人力资源概况',
            'on_leave'       => '请假',
            'absent'         => '缺勤',
            'total'          => '总计',
            'paid'           => '已发放',
            'draft'          => '草稿',
            'male'           => '男',
            'female'         => '女',
            'other'          => '其他',
            'new_hires'      => '新入职',
            'inactive'       => '离职',
            'hr_admins'      => '人事管理员',
            'view_leaves'    => '查看所有请假申请 →',
            'on_leave_now'   => '当前请假',
        ],
    ];
    $t = fn($k) => $tx[$lang][$k] ?? $tx['en'][$k] ?? $k;
@endphp

<x-filament-panels::page>
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=DM+Sans:wght@300;400;500;600&family=Noto+Sans+SC:wght@400;500;700&display=swap');
.adm*{box-sizing:border-box}
.adm{font-family:'DM Sans','Noto Sans SC',sans-serif}

/* HERO */
.adm-hero{position:relative;background:linear-gradient(130deg,#0f0c29 0%,#1a1a2e 30%,#16213e 60%,#0f3460 100%);border-radius:16px;overflow:hidden;margin-bottom:1.25rem;box-shadow:0 24px 64px rgba(0,0,0,.45)}
.adm-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(99,102,241,.08) 1px,transparent 1px),linear-gradient(90deg,rgba(99,102,241,.08) 1px,transparent 1px);background-size:40px 40px;animation:gMove 20s linear infinite}
@keyframes gMove{to{background-position:40px 40px}}
.adm-orb1{position:absolute;width:350px;height:350px;top:-100px;right:-80px;background:radial-gradient(circle,rgba(99,102,241,.3) 0%,transparent 70%);pointer-events:none}
.adm-orb2{position:absolute;width:250px;height:250px;bottom:-80px;left:30%;background:radial-gradient(circle,rgba(14,165,233,.2) 0%,transparent 70%);pointer-events:none}
.adm-hi{position:relative;z-index:2;display:flex;align-items:stretch}
.adm-hb{flex:1;padding:1.5rem 2rem}
.proj-strip{background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.35);border-radius:8px;padding:.5rem 1rem;margin-bottom:.85rem}
.proj-title{font-family:'Rajdhani',sans-serif;font-size:.85rem;font-weight:700;color:#a5b4fc;letter-spacing:.1em;text-transform:uppercase;line-height:1.4}
.proj-sub{font-size:.62rem;color:rgba(255,255,255,.45);letter-spacing:.06em;margin-top:2px}
.adm-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.4);color:#a5b4fc;font-size:.65rem;font-weight:600;letter-spacing:.14em;text-transform:uppercase;padding:.28rem .75rem;border-radius:4px;margin-bottom:.7rem}
.adm-bdot{width:6px;height:6px;background:#a5b4fc;border-radius:50%;animation:blink 2s ease-in-out infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.25}}
.adm-ttl{font-family:'Rajdhani',sans-serif;font-size:2.2rem;font-weight:700;color:#fff;line-height:1;letter-spacing:.02em;margin-bottom:.25rem}
.adm-ttl em{color:#818cf8;font-style:normal}
.adm-sub{font-size:.78rem;color:rgba(255,255,255,.4);margin-bottom:.75rem}
.adm-tags{display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:.75rem}
.adm-tag{display:inline-flex;align-items:center;gap:5px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.7);font-size:.7rem;font-weight:500;padding:.3rem .75rem;border-radius:20px}
.adm-tag span{font-family:'Rajdhani',sans-serif;font-weight:700;color:#fff;font-size:.9rem}

/* LANG TOGGLE */
.lang-toggle{display:inline-flex;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:20px;overflow:hidden;margin-bottom:.5rem}
.lang-opt{padding:.3rem .9rem;font-size:.72rem;font-weight:600;letter-spacing:.06em;color:rgba(255,255,255,.5);text-decoration:none;transition:all .2s;cursor:pointer}
.lang-opt.active{background:#6366f1;color:#fff}
.lang-opt:hover:not(.active){background:rgba(255,255,255,.1);color:#fff}

.designer{font-size:.62rem;color:rgba(255,255,255,.25);letter-spacing:.06em}
.designer strong{color:rgba(165,180,252,.6)}
.adm-hs{width:220px;flex-shrink:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.85rem;padding:1.5rem 1.25rem;border-left:1px solid rgba(99,102,241,.2);background:rgba(0,0,0,.2)}
.adm-clk{font-family:'Rajdhani',sans-serif;font-size:2.2rem;font-weight:700;color:#fff;letter-spacing:.06em;text-align:center}
.adm-cdt{font-size:.68rem;color:rgba(255,255,255,.35);text-align:center;margin-top:2px}
.adm-hst{width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:.8rem 1rem;text-align:center;transition:all .2s}
.adm-hst:hover{background:rgba(99,102,241,.15);border-color:rgba(99,102,241,.4)}
.adm-hsv{font-family:'Rajdhani',sans-serif;font-size:1.9rem;font-weight:700;color:#818cf8;line-height:1}
.adm-hsl{font-size:.65rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-top:2px}

/* STATUS BAR */
.sb-row{display:grid;grid-template-columns:repeat(7,1fr);gap:.75rem;margin-bottom:1.25rem}
.sb{background:white;border-radius:12px;padding:.9rem 1rem;box-shadow:0 2px 12px rgba(0,0,0,.06);border:1px solid rgba(0,0,0,.05);position:relative;overflow:hidden;transition:transform .2s,box-shadow .2s}
.dark .sb{background:rgb(30 41 59);border-color:rgba(255,255,255,.06)}
.sb:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.1)}
.sb::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%;background:var(--ac);border-radius:4px 0 0 4px}
.sb-ico{position:absolute;top:.75rem;right:.75rem;font-size:1.2rem;opacity:.1}
.sb-lbl{font-size:.6rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:.35rem}
.sb-val{font-family:'Rajdhani',sans-serif;font-size:1.7rem;font-weight:700;color:#0f172a;line-height:1;margin-bottom:.15rem}
.dark .sb-val{color:#f1f5f9}
.sb-sub{font-size:.62rem;color:#64748b}

/* ACTIONS */
.sec-lbl{font-family:'Rajdhani',sans-serif;font-size:1rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#0f172a;margin-bottom:.75rem}
.dark .sec-lbl{color:#e2e8f0}
.qa-row{display:grid;grid-template-columns:repeat(9,1fr);gap:.65rem;margin-bottom:1.25rem}
.qa{display:flex;flex-direction:column;align-items:center;gap:.45rem;padding:.9rem .4rem;background:white;border:1px solid rgba(0,0,0,.06);border-radius:12px;cursor:pointer;transition:all .2s;text-decoration:none;box-shadow:0 1px 4px rgba(0,0,0,.04)}
.dark .qa{background:rgb(30 41 59);border-color:rgba(255,255,255,.06)}
.qa:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,.1);border-color:var(--qc)}
.qa-ico{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;background:var(--qbg)}
.qa-lbl{font-size:.62rem;font-weight:600;color:#475569;text-align:center;line-height:1.3}
.dark .qa-lbl{color:#94a3b8}

/* PANELS */
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem}
.grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:1.25rem}
.grid-14{display:grid;grid-template-columns:1.4fr 1fr;gap:1rem;margin-bottom:1.25rem}
.panel{background:white;border-radius:12px;border:1px solid rgba(0,0,0,.05);box-shadow:0 2px 12px rgba(0,0,0,.05);overflow:hidden}
.dark .panel{background:rgb(30 41 59);border-color:rgba(255,255,255,.06)}
.ph{padding:.9rem 1.25rem;border-bottom:1px solid rgba(0,0,0,.05);display:flex;justify-content:space-between;align-items:center}
.dark .ph{border-color:rgba(255,255,255,.06)}
.pt{font-family:'Rajdhani',sans-serif;font-size:.95rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#0f172a}
.dark .pt{color:#e2e8f0}
.pill{font-size:.62rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;padding:.18rem .55rem;border-radius:4px}
.pb{padding:.75rem 1.25rem 1rem}
.chart-wrap{position:relative;height:180px;display:flex;align-items:center;justify-content:center}
.chart-wrap-sm{position:relative;height:150px;display:flex;align-items:center;justify-content:center}

.dept-row{display:flex;align-items:center;gap:.75rem;padding:.5rem 0;border-bottom:1px solid rgba(0,0,0,.04)}
.dark .dept-row{border-color:rgba(255,255,255,.04)}
.dept-row:last-child{border-bottom:none}
.dept-rank{font-family:'Rajdhani',sans-serif;font-size:1rem;font-weight:700;color:#cbd5e1;width:20px;text-align:center;flex-shrink:0}
.dept-name{font-size:.76rem;font-weight:500;color:#334155;flex:1}
.dark .dept-name{color:#cbd5e1}
.dept-bar{flex:1;height:6px;background:#f1f5f9;border-radius:6px;overflow:hidden;max-width:80px}
.dark .dept-bar{background:rgba(255,255,255,.08)}
.dept-fill{height:100%;border-radius:6px;background:linear-gradient(90deg,#6366f1,#818cf8)}
.dept-n{font-family:'Rajdhani',sans-serif;font-size:.9rem;font-weight:700;color:#0f172a;width:22px;text-align:right;flex-shrink:0}
.dark .dept-n{color:#e2e8f0}

.lv-item{display:flex;align-items:center;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid rgba(0,0,0,.04)}
.dark .lv-item{border-color:rgba(255,255,255,.04)}
.lv-item:last-child{border-bottom:none}
.lv-dot-lbl{display:flex;align-items:center;gap:.6rem}
.lv-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.lv-lbl{font-size:.76rem;color:#475569}
.dark .lv-lbl{color:#94a3b8}
.lv-val{font-family:'Rajdhani',sans-serif;font-size:1.1rem;font-weight:700;color:#0f172a}
.dark .lv-val{color:#e2e8f0}

.hc-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem}
.hc-item{background:#f8fafc;border-radius:8px;padding:.6rem;text-align:center}
.dark .hc-item{background:rgba(255,255,255,.04)}
.hc-val{font-family:'Rajdhani',sans-serif;font-size:1.4rem;font-weight:700;line-height:1}
.hc-lbl{font-size:.6rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-top:2px}

.adm-footer{display:flex;align-items:center;justify-content:space-between;padding:.6rem 1rem;background:rgba(0,0,0,.025);border-radius:10px;margin-top:.5rem}
.dark .adm-footer{background:rgba(255,255,255,.03)}
.ft{font-size:.65rem;color:#94a3b8;letter-spacing:.04em}
.ft strong{color:#64748b}

@media(max-width:1200px){.sb-row{grid-template-columns:repeat(4,1fr)}.qa-row{grid-template-columns:repeat(5,1fr)}.grid-3,.grid-14,.grid-2{grid-template-columns:1fr}.adm-hs{display:none}}
@media(max-width:700px){.sb-row{grid-template-columns:repeat(2,1fr)}.qa-row{grid-template-columns:repeat(3,1fr)}}
</style>

<div class="adm">

{{-- HERO --}}
<div class="adm-hero">
    <div class="adm-grid"></div>
    <div class="adm-orb1"></div>
    <div class="adm-orb2"></div>
    <div class="adm-hi">
        <div class="adm-hb">
            <div class="proj-strip">
                <div class="proj-title">{{ $t('project') }}</div>
                <div class="proj-sub">{{ $t('system') }}</div>
            </div>
            <div class="adm-badge"><span class="adm-bdot"></span> CRBC Uganda &middot; Admin Panel &middot; Live</div>
            <div class="adm-ttl"><em>Admin</em> {{ $t('title') }}</div>
            <div class="adm-sub">{{ $t('subtitle') }} &mdash; {{ date('l, d F Y') }}</div>
            <div class="adm-tags">
                <div class="adm-tag">👥 <span>{{ $totalEmployees }}</span> {{ $t('total_emp') }}</div>
                <div class="adm-tag">🏢 <span>{{ $totalDepartments }}</span> {{ $t('depts') }}</div>
                <div class="adm-tag">✅ <span>{{ $activeEmployees }}</span> {{ $t('active') }}</div>
                <div class="adm-tag">📁 <span>{{ $docsCount }}</span> {{ $t('docs') }}</div>
                <div class="adm-tag">⚠️ <span>{{ $disciplinaryOpen }}</span> {{ $t('open') }}</div>
            </div>
            {{-- DUAL LANGUAGE TOGGLE --}}
            <div class="lang-toggle">
                <a href="?lang=en" class="lang-opt {{ $lang === 'en' ? 'active' : '' }}">🇬🇧 EN</a>
                <a href="?lang=zh_CN" class="lang-opt {{ $lang === 'zh_CN' ? 'active' : '' }}">🇨🇳 中文</a>
            </div>
            <div class="designer">{{ $t('designer') }}</div>
        </div>
        <div class="adm-hs">
            <div>
                <div class="adm-clk" id="adm-clk">--:--:--</div>
                <div class="adm-cdt" id="adm-cdt"></div>
            </div>
            <div class="adm-hst"><div class="adm-hsv">{{ $attendancePct }}%</div><div class="adm-hsl">{{ $t('att_rate') }}</div></div>
            <div class="adm-hst"><div class="adm-hsv" style="color:#22c55e">{{ $presentToday }}</div><div class="adm-hsl">{{ $t('present') }}</div></div>
        </div>
    </div>
</div>

{{-- STATUS BAR --}}
<div class="sb-row">
    <div class="sb" style="--ac:#6366f1"><div class="sb-ico">👥</div><div class="sb-lbl">{{ $t('total_emp') }}</div><div class="sb-val">{{ $totalEmployees }}</div><div class="sb-sub">{{ $activeEmployees }} {{ $t('active') }}</div></div>
    <div class="sb" style="--ac:#22c55e"><div class="sb-ico">✅</div><div class="sb-lbl">{{ $t('present') }}</div><div class="sb-val" style="color:#22c55e">{{ $presentToday }}</div><div class="sb-sub">{{ $attendancePct }}% rate</div></div>
    <div class="sb" style="--ac:#f59e0b"><div class="sb-ico">📋</div><div class="sb-lbl">{{ $t('pending_lv') }}</div><div class="sb-val" style="color:#f59e0b">{{ $pendingLeaves }}</div><div class="sb-sub">{{ $approvedLeaves }} {{ $t('approved') }}</div></div>
    <div class="sb" style="--ac:#0ea5e9"><div class="sb-ico">🏢</div><div class="sb-lbl">{{ $t('depts') }}</div><div class="sb-val" style="color:#0ea5e9">{{ $totalDepartments }}</div><div class="sb-sub">{{ $hrAdmins }} {{ $t('hr_admins') }}</div></div>
    <div class="sb" style="--ac:#8b5cf6"><div class="sb-ico">📁</div><div class="sb-lbl">{{ $t('docs') }}</div><div class="sb-val" style="color:#8b5cf6">{{ $docsCount }}</div><div class="sb-sub">{{ $t('uploaded') }}</div></div>
    <div class="sb" style="--ac:#dc2626"><div class="sb-ico">⚠️</div><div class="sb-lbl">{{ $t('disc') }}</div><div class="sb-val" style="color:#dc2626">{{ $disciplinaryOpen }}</div><div class="sb-sub">{{ $t('open') }}</div></div>
    <div class="sb" style="--ac:#ef4444"><div class="sb-ico">🗓️</div><div class="sb-lbl">{{ $t('payroll') }}</div><div class="sb-val" style="font-size:1.2rem;color:#ef4444">{{ date('M Y') }}</div><div class="sb-sub">{{ $daysLeft }} {{ $t('days_left') }}</div></div>
</div>

{{-- QUICK ACTIONS --}}
<div class="sec-lbl">{{ $t('quick') }}</div>
<div class="qa-row">
    <a href="{{ url('/admin/employees/create') }}" class="qa" style="--qc:#6366f1;--qbg:#ede9fe"><div class="qa-ico">➕</div><div class="qa-lbl">{{ $t('add_emp') }}</div></a>
    <a href="{{ url('/admin/employees') }}" class="qa" style="--qc:#3b82f6;--qbg:#dbeafe"><div class="qa-ico">👥</div><div class="qa-lbl">{{ $t('employees') }}</div></a>
    <a href="{{ url('/admin/departments') }}" class="qa" style="--qc:#0ea5e9;--qbg:#e0f2fe"><div class="qa-ico">🏢</div><div class="qa-lbl">{{ $t('depts') }}</div></a>
    <a href="{{ url('/admin/attendances') }}" class="qa" style="--qc:#22c55e;--qbg:#dcfce7"><div class="qa-ico">🕐</div><div class="qa-lbl">{{ $t('attendance') }}</div></a>
    <a href="{{ url('/admin/leaves') }}" class="qa" style="--qc:#f59e0b;--qbg:#fef3c7"><div class="qa-ico">📋</div><div class="qa-lbl">{{ $t('leaves') }}</div></a>
    <a href="{{ url('/admin/payrolls') }}" class="qa" style="--qc:#8b5cf6;--qbg:#ede9fe"><div class="qa-ico">💰</div><div class="qa-lbl">{{ $t('payroll') }}</div></a>
    <a href="{{ url('/admin/employee-documents') }}" class="qa" style="--qc:#7c3aed;--qbg:#ede9fe"><div class="qa-ico">📁</div><div class="qa-lbl">{{ $t('documents') }}</div></a>
    <a href="{{ url('/admin/disciplinary-records') }}" class="qa" style="--qc:#dc2626;--qbg:#fee2e2"><div class="qa-ico">⚠️</div><div class="qa-lbl">{{ $t('disciplinary') }}</div></a>
    <a href="{{ url('/admin/settings') }}" class="qa" style="--qc:#64748b;--qbg:#f1f5f9"><div class="qa-ico">⚙️</div><div class="qa-lbl">{{ $t('settings') }}</div></a>
</div>

{{-- ANALYTICS SECTION --}}
<div class="sec-lbl">{{ $t('analytics') }}</div>

{{-- ROW 1: Hiring + Gender + Disciplinary --}}
<div class="grid-3">
    <div class="panel">
        <div class="ph"><span class="pt">{{ $t('hiring_trend') }}</span><span class="pill" style="background:#dbeafe;color:#1d4ed8">6mo</span></div>
        <div class="pb"><div class="chart-wrap"><canvas id="hiringChart"></canvas></div></div>
    </div>
    <div class="panel">
        <div class="ph"><span class="pt">{{ $t('gender_dist') }}</span><span class="pill" style="background:#fce7f3;color:#be185d">{{ $totalEmployees }} total</span></div>
        <div class="pb"><div class="chart-wrap"><canvas id="genderChart"></canvas></div></div>
    </div>
    <div class="panel">
        <div class="ph"><span class="pt">{{ $t('payroll_status') }}</span><span class="pill" style="background:#dcfce7;color:#15803d">{{ date('M') }}</span></div>
        <div class="pb"><div class="chart-wrap"><canvas id="payrollChart"></canvas></div></div>
    </div>
</div>

{{-- ROW 2: Dept headcount + Disciplinary types --}}
<div class="grid-14">
    <div class="panel">
        <div class="ph"><span class="pt">{{ $t('dept_hc') }}</span><span class="pill" style="background:#ede9fe;color:#6d28d9">Top {{ $departments->count() }}</span></div>
        <div class="pb">
            @forelse($departments as $i => $dept)
            <div class="dept-row">
                <div class="dept-rank">#{{ $i+1 }}</div>
                <div class="dept-name">{{ $dept->name }}</div>
                <div class="dept-bar"><div class="dept-fill" style="width:{{ round(($dept->employees_count/$maxDeptCount)*100) }}%"></div></div>
                <div class="dept-n">{{ $dept->employees_count }}</div>
            </div>
            @empty
            <div style="font-size:.78rem;color:#94a3b8;padding:.5rem 0">No departments found.</div>
            @endforelse
        </div>
    </div>
    <div class="panel">
        <div class="ph"><span class="pt">{{ $t('disc_types') }}</span><span class="pill" style="background:#fee2e2;color:#dc2626">{{ $disciplinaryOpen }} open</span></div>
        <div class="pb">
            @if(count($discTypes) > 0)
            <div class="chart-wrap"><canvas id="discChart"></canvas></div>
            @else
            <div style="font-size:.78rem;color:#94a3b8;padding:1rem 0;text-align:center">No disciplinary records yet</div>
            @endif
        </div>
    </div>
</div>

{{-- ROW 3: Leave overview + Workforce --}}
<div class="grid-2">
    <div class="panel">
        <div class="ph"><span class="pt">{{ $t('leave_overview') }}</span><span class="pill" style="background:#fef3c7;color:#d97706">{{ $pendingLeaves }} pending</span></div>
        <div class="pb">
            <div class="lv-item"><div class="lv-dot-lbl"><div class="lv-dot" style="background:#f59e0b"></div><div class="lv-lbl">{{ $t('pending_lv') }}</div></div><div class="lv-val" style="color:#f59e0b">{{ $pendingLeaves }}</div></div>
            <div class="lv-item"><div class="lv-dot-lbl"><div class="lv-dot" style="background:#22c55e"></div><div class="lv-lbl">{{ $t('approved') }}</div></div><div class="lv-val" style="color:#22c55e">{{ $approvedLeaves }}</div></div>
            <div class="lv-item"><div class="lv-dot-lbl"><div class="lv-dot" style="background:#3b82f6"></div><div class="lv-lbl">{{ $t('on_leave_now') }}</div></div><div class="lv-val" style="color:#3b82f6">{{ $onLeaveToday }}</div></div>
            <div class="lv-item"><div class="lv-dot-lbl"><div class="lv-dot" style="background:#8b5cf6"></div><div class="lv-lbl">{{ $t('docs') }}</div></div><div class="lv-val" style="color:#8b5cf6">{{ $docsCount }}</div></div>
            <div class="lv-item"><div class="lv-dot-lbl"><div class="lv-dot" style="background:#dc2626"></div><div class="lv-lbl">{{ $t('disc') }}</div></div><div class="lv-val" style="color:#dc2626">{{ $disciplinaryOpen }}</div></div>
            <div style="margin-top:.75rem"><a href="{{ url('/admin/leaves') }}" style="font-size:.75rem;font-weight:600;color:#6366f1;text-decoration:none">{{ $t('view_leaves') }}</a></div>
        </div>
    </div>
    <div class="panel">
        <div class="ph"><span class="pt">{{ $t('workforce') }}</span><span class="pill" style="background:#dbeafe;color:#1d4ed8">{{ date('M Y') }}</span></div>
        <div class="pb">
            <div class="hc-grid">
                <div class="hc-item"><div class="hc-val" style="color:#6366f1">{{ $totalEmployees }}</div><div class="hc-lbl">{{ $t('total_emp') }}</div></div>
                <div class="hc-item"><div class="hc-val" style="color:#22c55e">{{ $activeEmployees }}</div><div class="hc-lbl">{{ $t('active') }}</div></div>
                <div class="hc-item"><div class="hc-val" style="color:#ef4444">{{ $inactiveEmployees }}</div><div class="hc-lbl">{{ $t('inactive') }}</div></div>
                <div class="hc-item"><div class="hc-val" style="color:#0ea5e9">{{ $totalDepartments }}</div><div class="hc-lbl">{{ $t('depts') }}</div></div>
                <div class="hc-item"><div class="hc-val" style="color:#8b5cf6">{{ $docsCount }}</div><div class="hc-lbl">{{ $t('docs') }}</div></div>
                <div class="hc-item"><div class="hc-val" style="color:#dc2626">{{ $disciplinaryOpen }}</div><div class="hc-lbl">{{ $t('disc') }}</div></div>
                <div class="hc-item"><div class="hc-val" style="color:#22c55e">{{ $payrollPaid }}</div><div class="hc-lbl">{{ $t('paid') }}</div></div>
                <div class="hc-item"><div class="hc-val" style="color:#f59e0b">{{ $payrollPending }}</div><div class="hc-lbl">Pending Pay</div></div>
                <div class="hc-item"><div class="hc-val" style="color:#94a3b8">{{ $newHires }}</div><div class="hc-lbl">{{ $t('new_hires') }}</div></div>
            </div>
        </div>
    </div>
</div>

@livewire(\App\Filament\Resources\Departments\Widgets\StatsOverview::class)
@livewire(\App\Filament\Resources\Employees\Widgets\StatsOverview::class)

<div class="adm-footer">
    <div class="ft"><strong>CRBC Uganda</strong> &nbsp;&middot;&nbsp; {{ $t('system') }}</div>
    <div class="ft"><strong>{{ $t('designer') }}</strong> &nbsp;&middot;&nbsp; {{ date('Y') }}</div>
</div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
(function(){
    // Clock
    function tick(){
        var n=new Date();
        var c=document.getElementById('adm-clk');
        var d=document.getElementById('adm-cdt');
        if(c) c.textContent=n.toLocaleTimeString('en-UG',{hour12:false});
        if(d) d.textContent=n.toLocaleDateString('en-UG',{weekday:'short',day:'2-digit',month:'short'});
    }
    tick(); setInterval(tick,1000);

    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const textColor = isDark ? '#94a3b8' : '#64748b';

    // Hiring trend bar chart
    new Chart(document.getElementById('hiringChart'), {
        type: 'bar',
        data: {
            labels: @json($hiringLabels),
            datasets: [{
                label: '{{ $t('new_hires') }}',
                data: @json($hiringData),
                backgroundColor: 'rgba(99,102,241,0.7)',
                borderColor: '#6366f1',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { color: textColor, stepSize: 1 }, grid: { color: gridColor } },
                x: { ticks: { color: textColor }, grid: { display: false } }
            }
        }
    });

    // Gender pie
    new Chart(document.getElementById('genderChart'), {
        type: 'doughnut',
        data: {
            labels: ['{{ $t('male') }}', '{{ $t('female') }}', '{{ $t('other') }}'],
            datasets: [{
                data: [{{ $maleCount }}, {{ $femaleCount }}, {{ $otherCount }}],
                backgroundColor: ['#3b82f6','#ec4899','#94a3b8'],
                borderWidth: 2,
                borderColor: isDark ? '#1e293b' : '#fff',
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: { legend: { position: 'bottom', labels: { color: textColor, padding: 10, font: { size: 11 } } } },
            cutout: '60%',
        }
    });

    // Payroll status doughnut
    new Chart(document.getElementById('payrollChart'), {
        type: 'doughnut',
        data: {
            labels: ['{{ $t('paid') }}', 'Pending', '{{ $t('draft') }}'],
            datasets: [{
                data: [{{ $payrollPaid }}, {{ $payrollPending }}, {{ $payrollDraft }}],
                backgroundColor: ['#22c55e','#f59e0b','#94a3b8'],
                borderWidth: 2,
                borderColor: isDark ? '#1e293b' : '#fff',
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: { legend: { position: 'bottom', labels: { color: textColor, padding: 8, font: { size: 11 } } } },
            cutout: '60%',
        }
    });

    // Disciplinary types
    @if(count($discTypes) > 0)
    new Chart(document.getElementById('discChart'), {
        type: 'bar',
        data: {
            labels: @json($discTypes),
            datasets: [{
                data: @json($discCounts),
                backgroundColor: ['#ef4444','#f59e0b','#dc2626','#b91c1c','#fca5a5','#fcd34d'],
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true, indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { color: textColor, stepSize: 1 }, grid: { color: gridColor } },
                y: { ticks: { color: textColor, font: { size: 10 } }, grid: { display: false } }
            }
        }
    });
    @endif
})();
</script>

</x-filament-panels::page>
