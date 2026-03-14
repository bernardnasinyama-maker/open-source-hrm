<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

// ============================================================
// 1. DEPARTMENTS
// ============================================================
$depts = [
    'Human Resource', 'Engineering', 'Materials', 'Health and Safety',
    'Environmentalist', 'Survey (Measurement)', 'Driver', 'Chef',
    'Administration', 'Finance'
];
foreach ($depts as $d) {
    Department::firstOrCreate(['name' => $d]);
}
echo "Departments: " . Department::count() . "\n";

// ============================================================
// 2. EMPLOYEES
// ============================================================
$dept = fn($name) => Department::where('name', $name)->value('id');

$employees = [
    ['employee_code'=>'SYS-001',      'first_name'=>'System',    'last_name'=>'Administrator','email'=>'einsteinbernard3000@gmail.com','password'=>'ben123#',  'employment_type'=>'Permanent','department_id'=>$dept('Administration'),'gender'=>'Male',  'hire_date'=>'2026-01-01','role'=>'super_admin'],
    ['employee_code'=>'CRBC-ADM-001', 'first_name'=>'Mr',        'last_name'=>'Shi',          'email'=>'mr.shi@crbc.com',              'password'=>'shi123@',  'employment_type'=>'Permanent','department_id'=>$dept('Administration'),'gender'=>'Male',  'hire_date'=>'2026-01-01','role'=>'admin'],
    ['employee_code'=>'CRBC-HR-001',  'first_name'=>'Thembo',    'last_name'=>'Amoni',        'email'=>'thembo.amoni@crbc.com',        'password'=>'thembo123','employment_type'=>'Contract', 'department_id'=>$dept('Human Resource'), 'gender'=>'Male',  'hire_date'=>'2026-01-01','role'=>'hr_assistant'],
    ['employee_code'=>'CRBC-ENG-001', 'first_name'=>'Nasinyama', 'last_name'=>'Bernard',      'email'=>'bernardnasinyama@gmail.com',   'password'=>'ben123#',  'employment_type'=>'Contract', 'department_id'=>$dept('Engineering'),    'gender'=>'Male',  'hire_date'=>'2026-01-01','role'=>'employee'],
    ['employee_code'=>'CRBC-ENG-002', 'first_name'=>'Okwany',    'last_name'=>'Chris',        'email'=>'okwany.chris@crbc.com',        'password'=>'crbc2026', 'employment_type'=>'Contract', 'department_id'=>$dept('Engineering'),    'gender'=>'Male',  'hire_date'=>'2026-01-01','role'=>'employee'],
    ['employee_code'=>'CRBC-MAT-001', 'first_name'=>'Tibaingana','last_name'=>'Brian',        'email'=>'tibaingana.brian@crbc.com',    'password'=>'crbc2026', 'employment_type'=>'Contract', 'department_id'=>$dept('Materials'),      'gender'=>'Male',  'hire_date'=>'2026-01-01','role'=>'employee'],
    ['employee_code'=>'CRBC-MAT-002', 'first_name'=>'Atukunda',  'last_name'=>'Precious',     'email'=>'atukunda.precious@crbc.com',   'password'=>'crbc2026', 'employment_type'=>'Contract', 'department_id'=>$dept('Materials'),      'gender'=>'Female','hire_date'=>'2026-01-01','role'=>'employee'],
    ['employee_code'=>'CRBC-MAT-003', 'first_name'=>'Sentimba',  'last_name'=>'Gerald',       'email'=>'sentimba.gerald@crbc.com',     'password'=>'crbc2026', 'employment_type'=>'Contract', 'department_id'=>$dept('Materials'),      'gender'=>'Male',  'hire_date'=>'2026-01-01','role'=>'employee'],
    ['employee_code'=>'CRBC-HSE-001', 'first_name'=>'Tushemerire','last_name'=>'Glorius',     'email'=>'tushemerire.glorius@crbc.com', 'password'=>'crbc2026', 'employment_type'=>'Contract', 'department_id'=>$dept('Health and Safety'),'gender'=>'Male','hire_date'=>'2026-01-01','role'=>'employee'],
    ['employee_code'=>'CRBC-ENV-001', 'first_name'=>'Adong',     'last_name'=>'Rebecca',      'email'=>'adong.rebecca@crbc.com',       'password'=>'crbc2026', 'employment_type'=>'Contract', 'department_id'=>$dept('Environmentalist'),'gender'=>'Female','hire_date'=>'2026-01-01','role'=>'employee'],
    ['employee_code'=>'CRBC-SUR-001', 'first_name'=>'Orombi',    'last_name'=>'Josephine',    'email'=>'orombi.josephine@crbc.com',    'password'=>'crbc2026', 'employment_type'=>'Contract', 'department_id'=>$dept('Survey (Measurement)'),'gender'=>'Female','hire_date'=>'2026-01-01','role'=>'employee'],
    ['employee_code'=>'CRBC-SUR-002', 'first_name'=>'Nuwamanya', 'last_name'=>'Joram',        'email'=>'nuwamanya.joram@crbc.com',     'password'=>'crbc2026', 'employment_type'=>'Contract', 'department_id'=>$dept('Survey (Measurement)'),'gender'=>'Male','hire_date'=>'2026-01-01','role'=>'employee'],
    ['employee_code'=>'CRBC-DRV-001', 'first_name'=>'Kisakye',   'last_name'=>'James',        'email'=>'kisakye.james@crbc.com',       'password'=>'crbc2026', 'employment_type'=>'Contract', 'department_id'=>$dept('Driver'),         'gender'=>'Male',  'hire_date'=>'2026-01-01','role'=>'employee'],
    ['employee_code'=>'CRBC-DRV-002', 'first_name'=>'Mpanga',    'last_name'=>'Shafiq',       'email'=>'mpanga.shafiq@crbc.com',       'password'=>'crbc2026', 'employment_type'=>'Contract', 'department_id'=>$dept('Driver'),         'gender'=>'Male',  'hire_date'=>'2026-01-01','role'=>'employee'],
    ['employee_code'=>'CRBC-DRV-003', 'first_name'=>'Mufumbiro', 'last_name'=>'Norman',       'email'=>'mufumbiro.norman@crbc.com',    'password'=>'crbc2026', 'employment_type'=>'Contract', 'department_id'=>$dept('Driver'),         'gender'=>'Male',  'hire_date'=>'2026-01-01','role'=>'employee'],
    ['employee_code'=>'CRBC-CHF-001', 'first_name'=>'Abinji',    'last_name'=>'Morris',       'email'=>'abinji.morris@crbc.com',       'password'=>'crbc2026', 'employment_type'=>'Contract', 'department_id'=>$dept('Chef'),           'gender'=>'Male',  'hire_date'=>'2026-01-01','role'=>'employee'],
];

foreach ($employees as $data) {
    $role = $data['role'];
    unset($data['role']);
    $data['password'] = Hash::make($data['password']);
    $data['is_active'] = true;
    $emp = Employee::updateOrCreate(['employee_code' => $data['employee_code']], $data);
    $emp->syncRoles([$role]);
    echo "✅ {$emp->first_name} {$emp->last_name} ({$role})\n";
}

// ============================================================
// 3. CORRESPONDENCE
// ============================================================
$bernard = Employee::where('employee_code','CRBC-ENG-001')->first();
$corrs = [
    ['ref_number'=>'RFI-2026-001','subject'=>'Road design query at Chainage 12+500','type'=>'rfi','direction'=>'outgoing','from_party'=>'CRBC Uganda','to_party'=>'Consultant Engineer','date_sent_received'=>'2026-03-01','response_due_date'=>'2026-03-10','priority'=>'high','status'=>'overdue','assigned_to'=>$bernard?->id,'created_by'=>$bernard?->id],
    ['ref_number'=>'NCR-2026-001','subject'=>'Subbase compaction failure at Ch 45+200','type'=>'ncr','direction'=>'incoming','from_party'=>'Supervising Engineer','to_party'=>'CRBC Uganda','date_sent_received'=>'2026-03-05','response_due_date'=>'2026-03-12','priority'=>'critical','status'=>'pending_response','assigned_to'=>$bernard?->id,'created_by'=>$bernard?->id],
    ['ref_number'=>'SI-2026-001','subject'=>'Site instruction: Modify drainage design Ch 67+800','type'=>'si','direction'=>'incoming','from_party'=>'Resident Engineer','to_party'=>'CRBC Uganda','date_sent_received'=>'2026-03-08','response_due_date'=>'2026-03-20','priority'=>'medium','status'=>'open','assigned_to'=>$bernard?->id,'created_by'=>$bernard?->id],
];
foreach ($corrs as $c) {
    App\Models\Correspondence::firstOrCreate(['ref_number'=>$c['ref_number']], $c);
}
echo "Correspondences: " . App\Models\Correspondence::count() . "\n";

// ============================================================
// 4. SITE EXPENSES
// ============================================================
$exps = [
    ['ref_number'=>'EXP-2026-001','title'=>'Fuel for site vehicles March W1','category'=>'fuel','amount'=>450000,'currency'=>'UGX','expense_date'=>'2026-03-03','status'=>'pending','employee_id'=>$bernard?->id,'created_by'=>$bernard?->id],
    ['ref_number'=>'EXP-2026-002','title'=>'Airtime & data for site team','category'=>'airtime','amount'=>120000,'currency'=>'UGX','expense_date'=>'2026-03-05','status'=>'approved','employee_id'=>$bernard?->id,'created_by'=>$bernard?->id],
    ['ref_number'=>'EXP-2026-003','title'=>'Per diem for survey team','category'=>'per_diem','amount'=>750000,'currency'=>'UGX','expense_date'=>'2026-03-10','status'=>'pending','employee_id'=>$bernard?->id,'created_by'=>$bernard?->id],
];
foreach ($exps as $e) {
    App\Models\SiteExpense::firstOrCreate(['ref_number'=>$e['ref_number']], $e);
}
echo "Expenses: " . App\Models\SiteExpense::count() . "\n";

// ============================================================
// 5. TASKS
// ============================================================
$shi  = Employee::where('employee_code','CRBC-ADM-001')->first();
$corr = App\Models\Correspondence::where('ref_number','RFI-2026-001')->first();
$exp  = App\Models\SiteExpense::where('ref_number','EXP-2026-001')->first();

$tasks = [
    ['title'=>'Follow up on RFI-2026-001','description'=>'Chase consultant for response on road design query','board_status'=>'todo','board_position'=>1,'priority'=>'high','assignee_id'=>$bernard?->id,'due_date'=>now()->addDays(3),'user_id'=>1,'taskable_type'=>App\Models\Correspondence::class,'taskable_id'=>$corr?->id],
    ['title'=>'Draft response to NCR-2026-001','description'=>'Prepare formal response to compaction failure NCR','board_status'=>'todo','board_position'=>2,'priority'=>'critical','assignee_id'=>$bernard?->id,'due_date'=>now()->addDays(2),'user_id'=>1],
    ['title'=>'Approve March fuel expenses','description'=>'Review 3 pending fuel claims from site team','board_status'=>'in_progress','board_position'=>1,'priority'=>'medium','assignee_id'=>$shi?->id,'due_date'=>now()->addDays(1),'user_id'=>1,'taskable_type'=>App\Models\SiteExpense::class,'taskable_id'=>$exp?->id],
    ['title'=>'Submit March attendance report','description'=>'Compile and submit March 2026 attendance to PM','board_status'=>'pending_review','board_position'=>1,'priority'=>'critical','assignee_id'=>$bernard?->id,'due_date'=>now()->subDay(),'user_id'=>1],
    ['title'=>'Daily progress report week 11','description'=>'Chainage progress Ch 34+200 to Ch 35+600 completed','board_status'=>'completed','board_position'=>1,'priority'=>'low','assignee_id'=>$bernard?->id,'due_date'=>now(),'user_id'=>1],
];

App\Models\Task::truncate();
foreach ($tasks as $t) {
    App\Models\Task::create($t);
}
echo "Tasks: " . App\Models\Task::count() . "\n";

echo "\n=== ALL DATA RESTORED ===\n";
echo "Employees: " . Employee::count() . "\n";
echo "Departments: " . Department::count() . "\n";
echo "Run: php artisan optimize:clear && php artisan serve --host=0.0.0.0 --port=8000\n";
echo "Login: einsteinbernard3000@gmail.com / ben123#\n";
