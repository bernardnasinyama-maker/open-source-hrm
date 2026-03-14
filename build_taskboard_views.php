<?php
require __DIR__."/vendor/autoload.php";
$app = require __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// ============================================================
// 1. ENHANCED TASK TYPE BADGE COMPONENT
// ============================================================
if (!is_dir('resources/views/filament/components')) {
    mkdir('resources/views/filament/components', 0755, true);
}

file_put_contents('resources/views/filament/components/task-type-badge.blade.php', '
@php
$icons = [
    "Correspondence" => ["icon"=>"📬","color"=>"#6366f1","bg"=>"rgba(99,102,241,.15)","label"=>"Correspondence"],
    "SiteExpense"    => ["icon"=>"💰","color"=>"#f59e0b","bg"=>"rgba(245,158,11,.15)","label"=>"Expense"],
    "Task"           => ["icon"=>"📋","color"=>"#10b981","bg"=>"rgba(16,185,129,.15)","label"=>"Task"],
];
$pColors = ["critical"=>"#ef4444","high"=>"#f97316","medium"=>"#f59e0b","low"=>"#10b981"];
$info = $icons[$type] ?? $icons["Task"];
$pc = $pColors[$priority] ?? $pColors["medium"];
@endphp
<div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
    <span style="background:{{$info[\'bg\']}};color:{{$info[\'color\']}};padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">
        {{$info[\'icon\']}} {{$info[\'label\']}}
    </span>
    <span style="background:rgba(0,0,0,.1);color:{{$pc}};padding:2px 6px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase">
        {{$priority}}
    </span>
</div>
');
echo "Task badge component written\n";

// ============================================================
// 2. ENHANCED TASK BOARD - Role-based views
// ============================================================
file_put_contents('app/Filament/Pages/TaskBoard.php', '<?php
namespace App\Filament\Pages;

use App\Models\Task;
use App\Models\Correspondence;
use App\Models\SiteExpense;
use App\Models\Employee;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\BoardPage;
use Relaticle\Flowforge\Column;
use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\{Textarea, Select, DatePicker, TextInput};
use Filament\Actions\{EditAction, DeleteAction, CreateAction, ViewAction};
use App\Filament\Resources\Correspondences\CorrespondenceResource;
use App\Filament\Resources\Expenses\SiteExpenseResource;
use App\Notifications\LeaveStatusNotification;

class TaskBoard extends BoardPage
{
    protected static string|null|\BackedEnum $navigationIcon = "heroicon-o-view-columns";
    protected static ?string $navigationLabel = "Task Board";
    protected static ?string $title = "Task Board";
    protected static string|\UnitEnum|null $navigationGroup = "Work space";
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant"]) ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        $overdue = Task::where("board_status","!=","completed")
            ->whereNotNull("due_date")
            ->where("due_date","<",now())
            ->count();
        return $overdue > 0 ? (string)$overdue : null;
    }

    public static function getNavigationBadgeColor(): ?string { return "danger"; }

    public function board(Board $board): Board
    {
        $isSuperAdmin = auth()->user()?->hasRole("super_admin");
        $isAdmin      = auth()->user()?->hasAnyRole(["super_admin","admin"]);

        return $board
            ->searchable(["title","description"])
            ->query($this->getUnifiedQuery())
            ->recordTitleAttribute("title")
            ->columnIdentifier("board_status")
            ->positionIdentifier("board_position")

            ->cardSchema(fn(Schema $schema) => $schema->components([
                Grid::make(1)->schema([
                    TextEntry::make("type_badge")
                        ->hiddenLabel()
                        ->formatStateUsing(fn($state, $record) => view("filament.components.task-type-badge", [
                            "type"     => $record->taskable_type ? class_basename($record->taskable_type) : "Task",
                            "priority" => $record->priority ?? "medium",
                            "source"   => $record->taskable
                        ])),
                    TextEntry::make("title")
                        ->weight(FontWeight::Bold)
                        ->hiddenLabel()
                        ->limit(35),
                    TextEntry::make("taskable.ref_number")
                        ->hiddenLabel()
                        ->badge()
                        ->color("gray")
                        ->visible(fn($record) => !empty($record->taskable?->ref_number)),
                    Grid::make(2)->schema([
                        TextEntry::make("assignee.first_name")
                            ->icon("heroicon-o-user")
                            ->hiddenLabel()
                            ->default("Unassigned")
                            ->formatStateUsing(fn($s, $r) => $r->assignee ? $r->assignee->first_name . " " . $r->assignee->last_name : "Unassigned"),
                        TextEntry::make("due_date")
                            ->date("d M Y")
                            ->icon("heroicon-o-calendar")
                            ->hiddenLabel()
                            ->color(fn($record) => $record->due_date && $record->due_date->isPast() && $record->board_status !== "completed" ? "danger" : "gray"),
                    ]),
                    TextEntry::make("description")
                        ->hiddenLabel()
                        ->limit(45)
                        ->visible(fn($record) => !empty($record->description)),
                ]),
            ]))

            ->cardActions([
                ViewAction::make()
                    ->modalHeading(fn($record) => $record->title)
                    ->modalWidth("lg")
                    ->infolist(fn($record) => [
                        TextEntry::make("title")->weight(FontWeight::Bold),
                        TextEntry::make("description")->markdown()->visible(fn($r) => !empty($r->description)),
                        Grid::make(3)->schema([
                            TextEntry::make("assignee.first_name")->label("Assignee")
                                ->formatStateUsing(fn($s,$r) => $r->assignee ? $r->assignee->first_name." ".$r->assignee->last_name : "Unassigned"),
                            TextEntry::make("due_date")->label("Due Date")->date("d M Y"),
                            TextEntry::make("priority")->label("Priority")->badge()
                                ->color(fn($s) => match($s){"critical"=>"danger","high"=>"warning","medium"=>"info","low"=>"success",default=>"gray"}),
                        ]),
                        Grid::make(2)->schema([
                            TextEntry::make("taskable.ref_number")->label("Reference")->badge()->color("primary")
                                ->visible(fn($r) => !empty($r->taskable?->ref_number)),
                            TextEntry::make("taskable.from_party")->label("From Party")
                                ->visible(fn($r) => $r->taskable_type === Correspondence::class),
                            TextEntry::make("taskable.subject")->label("Subject")
                                ->visible(fn($r) => $r->taskable_type === Correspondence::class),
                            TextEntry::make("taskable.status")->label("Status")->badge()
                                ->visible(fn($r) => !empty($r->taskable?->status)),
                            TextEntry::make("taskable.amount")->label("Amount (UGX)")->money("UGX")
                                ->visible(fn($r) => $r->taskable_type === SiteExpense::class),
                            TextEntry::make("taskable.category")->label("Category")->badge()
                                ->visible(fn($r) => $r->taskable_type === SiteExpense::class),
                        ]),
                    ]),

                EditAction::make()
                    ->visible($isAdmin)
                    ->modalHeading("Edit Task")
                    ->form([
                        TextInput::make("title")->required(),
                        Textarea::make("description")->rows(3),
                        Grid::make(2)->schema([
                            Select::make("assignee_id")->label("Assign To")
                                ->options(fn() => Employee::whereNotIn("employee_code",["SYS-001","CRBC-VIEW"])
                                    ->get()->mapWithKeys(fn($e) => [$e->id => $e->first_name." ".$e->last_name]))
                                ->searchable(),
                            DatePicker::make("due_date")->label("Due Date"),
                            Select::make("board_status")->label("Status")->options([
                                "todo"           => "📋 To Do",
                                "in_progress"    => "🔄 In Progress",
                                "pending_review" => "👀 Pending Review",
                                "completed"      => "✅ Completed",
                            ]),
                            Select::make("priority")->options([
                                "low"      => "🟢 Low",
                                "medium"   => "🟡 Medium",
                                "high"     => "🟠 High",
                                "critical" => "🔴 Critical",
                            ]),
                        ]),
                    ]),
                DeleteAction::make()->visible($isSuperAdmin),
            ])
            ->cardAction("view")

            ->columns([
                Column::make("todo")->label("📋 To Do")->color("gray"),
                Column::make("in_progress")->label("🔄 In Progress")->color("blue"),
                Column::make("pending_review")->label("👀 Pending Review")->color("warning"),
                Column::make("completed")->label("✅ Done")->color("green"),
            ])

            ->columnActions([
                CreateAction::make()
                    ->label(" ")
                    ->iconButton()
                    ->icon("heroicon-o-plus")
                    ->visible($isAdmin)
                    ->modalHeading("Create Task")
                    ->form([
                        Select::make("task_type")->label("Task Type")->live()
                            ->options([
                                "task"            => "📋 General Task",
                                "correspondence"  => "📬 Correspondence Follow-up",
                                "expense"         => "💰 Expense Approval",
                            ])->required(),
                        TextInput::make("title")->required(),
                        Textarea::make("description")->rows(2),
                        Grid::make(2)->schema([
                            Select::make("assignee_id")->label("Assign To")
                                ->options(fn() => Employee::whereNotIn("employee_code",["SYS-001","CRBC-VIEW"])
                                    ->get()->mapWithKeys(fn($e) => [$e->id => $e->first_name." ".$e->last_name]))
                                ->searchable(),
                            DatePicker::make("due_date"),
                            Select::make("priority")->options([
                                "low"=>"🟢 Low","medium"=>"🟡 Medium","high"=>"🟠 High","critical"=>"🔴 Critical"
                            ])->default("medium"),
                        ]),
                        Select::make("source_correspondence_id")->label("Link Correspondence")
                            ->options(fn() => Correspondence::whereNotIn("status",["closed"])
                                ->get()->mapWithKeys(fn($c) => [$c->id => "{$c->ref_number} — {$c->subject}"]))
                            ->searchable()->visible(fn($get) => $get("task_type") === "correspondence"),
                        Select::make("source_expense_id")->label("Link Expense")
                            ->options(fn() => SiteExpense::where("status","pending")
                                ->get()->mapWithKeys(fn($e) => [$e->id => "{$e->ref_number} — {$e->title}"]))
                            ->searchable()->visible(fn($get) => $get("task_type") === "expense"),
                    ])
                    ->mutateFormDataUsing(function (array $data, array $arguments) {
                        $status = $arguments["column"] ?? "todo";
                        $task = new Task();
                        $task->title          = $data["title"];
                        $task->description    = $data["description"] ?? null;
                        $task->assignee_id    = $data["assignee_id"] ?? null;
                        $task->due_date       = $data["due_date"] ?? null;
                        $task->priority       = $data["priority"] ?? "medium";
                        $task->board_status   = $status;
                        $task->board_position = Task::where("board_status",$status)->max("board_position") + 1;
                        $task->user_id        = auth()->id();
                        if (!empty($data["source_correspondence_id"])) {
                            $task->taskable_type = Correspondence::class;
                            $task->taskable_id   = $data["source_correspondence_id"];
                        } elseif (!empty($data["source_expense_id"])) {
                            $task->taskable_type = SiteExpense::class;
                            $task->taskable_id   = $data["source_expense_id"];
                        }
                        $task->save();

                        // Notify assignee
                        if ($task->assignee_id && $task->assignee) {
                            try {
                                $task->assignee->notify(new \App\Notifications\TaskAssignedNotification($task));
                            } catch (\Exception $e) {}
                        }
                        return $task->toArray();
                    }),
            ]);
    }

    protected function getUnifiedQuery(): Builder
    {
        $user = auth()->user();
        $query = Task::query()->with(["assignee","taskable"])->orderBy("board_position");
        // HR assistant only sees tasks assigned to them
        if ($user?->hasRole("hr_assistant")) {
            $query->where("assignee_id", $user->id);
        }
        return $query;
    }
}');
echo "TaskBoard.php rewritten\n";

// ============================================================
// 3. TASK ASSIGNED NOTIFICATION
// ============================================================
file_put_contents('app/Notifications/TaskAssignedNotification.php', '<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Task;

class TaskAssignedNotification extends Notification {
    use Queueable;
    public function __construct(public Task $task) {}
    public function via($n): array { return ["mail","database"]; }
    public function toMail($n): MailMessage {
        $due = $this->task->due_date ? $this->task->due_date->format("d M Y") : "No due date";
        return (new MailMessage)
            ->subject("New Task Assigned — CRBC Uganda HRM")
            ->greeting("Dear {$n->first_name},")
            ->line("A new task has been assigned to you.")
            ->line("**Task:** {$this->task->title}")
            ->line("**Priority:** " . ucfirst($this->task->priority ?? "medium"))
            ->line("**Due:** {$due}")
            ->action("View Task Board", url("/admin/task-board"))
            ->salutation("CRBC Uganda — Kayunga-Bbaale-Galiraya Road");
    }
    public function toArray($n): array {
        return ["type"=>"task_assigned","task_id"=>$this->task->id,"message"=>"New task: {$this->task->title}"];
    }
}');
echo "TaskAssignedNotification created\n";

// ============================================================
// 4. ENHANCED EMPLOYEE DASHBOARD - Role-aware
// ============================================================
if (!is_dir('resources/views/filament/employee/pages')) {
    mkdir('resources/views/filament/employee/pages', 0755, true);
}

file_put_contents('resources/views/filament/employee/pages/dashboard.blade.php', '
@php
$user = auth()->user();
$isAdmin = $user?->hasAnyRole(["super_admin","admin"]);
$isHR = $user?->hasAnyRole(["super_admin","admin","hr_assistant"]);
$myTasks = App\Models\Task::where("assignee_id",$user->id)->where("board_status","!=","completed")->get();
$overdueCorr = App\Models\Correspondence::whereNotIn("status",["closed","responded"])->whereNotNull("response_due_date")->where("response_due_date","<",now())->count();
$pendingExp = App\Models\SiteExpense::where("status","pending")->count();
$myLeaves = App\Models\Leave::where("employee_id",$user->id)->where("status","pending")->count();
$myAttendance = App\Models\Attendance::where("employee_id",$user->id)->whereDate("date",today())->first();
@endphp
<x-filament-panels::page>
<div style="space-y:20px">

{{-- Welcome strip --}}
<div style="background:linear-gradient(135deg,#1e3a5f,#0f172a);border-radius:12px;padding:20px 24px;margin-bottom:20px;border:1px solid rgba(99,102,241,.3);display:flex;align-items:center;justify-content:space-between">
    <div>
        <div style="color:#a5b4fc;font-size:13px;font-weight:500;margin-bottom:4px">Welcome back</div>
        <div style="color:white;font-size:22px;font-weight:700">{{$user->first_name}} {{$user->last_name}}</div>
        <div style="color:rgba(255,255,255,.4);font-size:12px;margin-top:4px">{{now()->format("l, d F Y")}}</div>
    </div>
    <div style="text-align:right">
        <div style="color:#fbbf24;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.08em">CRBC Uganda</div>
        <div style="color:rgba(255,255,255,.4);font-size:10px">Kayunga-Bbaale-Galiraya Road</div>
    </div>
</div>

{{-- My stat cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:20px">
    <div style="background:#1e293b;border-radius:10px;padding:16px;border:1px solid rgba(255,255,255,.07)">
        <div style="color:rgba(255,255,255,.4);font-size:11px;margin-bottom:8px">MY TASKS</div>
        <div style="color:#a5b4fc;font-size:28px;font-weight:700">{{$myTasks->count()}}</div>
        <div style="color:rgba(255,255,255,.3);font-size:11px">pending</div>
    </div>
    <div style="background:#1e293b;border-radius:10px;padding:16px;border:1px solid rgba(255,255,255,.07)">
        <div style="color:rgba(255,255,255,.4);font-size:11px;margin-bottom:8px">TODAY</div>
        <div style="color:{{$myAttendance ? "#10b981" : "#f87171"}};font-size:28px;font-weight:700">{{$myAttendance ? "IN" : "OUT"}}</div>
        <div style="color:rgba(255,255,255,.3);font-size:11px">attendance</div>
    </div>
    <div style="background:#1e293b;border-radius:10px;padding:16px;border:1px solid rgba(255,255,255,.07)">
        <div style="color:rgba(255,255,255,.4);font-size:11px;margin-bottom:8px">MY LEAVES</div>
        <div style="color:#f59e0b;font-size:28px;font-weight:700">{{$myLeaves}}</div>
        <div style="color:rgba(255,255,255,.3);font-size:11px">pending</div>
    </div>
    @if($isHR)
    <div style="background:#1e293b;border-radius:10px;padding:16px;border:1px solid rgba(248,113,113,.2)">
        <div style="color:rgba(255,255,255,.4);font-size:11px;margin-bottom:8px">OVERDUE CORR.</div>
        <div style="color:#f87171;font-size:28px;font-weight:700">{{$overdueCorr}}</div>
        <div style="color:rgba(255,255,255,.3);font-size:11px">need response</div>
    </div>
    <div style="background:#1e293b;border-radius:10px;padding:16px;border:1px solid rgba(245,158,11,.2)">
        <div style="color:rgba(255,255,255,.4);font-size:11px;margin-bottom:8px">EXPENSES</div>
        <div style="color:#f59e0b;font-size:28px;font-weight:700">{{$pendingExp}}</div>
        <div style="color:rgba(255,255,255,.3);font-size:11px">pending approval</div>
    </div>
    @endif
</div>

{{-- My Tasks list --}}
@if($myTasks->count() > 0)
<div style="background:#1e293b;border-radius:12px;padding:20px;margin-bottom:20px;border:1px solid rgba(255,255,255,.07)">
    <div style="color:#f1f5f9;font-weight:600;font-size:15px;margin-bottom:14px">📋 My Pending Tasks</div>
    @foreach($myTasks->take(5) as $task)
    @php
        $pColors = ["critical"=>"#ef4444","high"=>"#f97316","medium"=>"#f59e0b","low"=>"#10b981"];
        $pc = $pColors[$task->priority ?? "medium"] ?? "#f59e0b";
        $isOverdue = $task->due_date && $task->due_date->isPast();
    @endphp
    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:rgba(255,255,255,.03);border-radius:8px;margin-bottom:8px;border-left:3px solid {{$pc}}">
        <div>
            <div style="color:#f1f5f9;font-size:13px;font-weight:500">{{$task->title}}</div>
            @if($task->taskable?->ref_number)
            <div style="color:rgba(255,255,255,.3);font-size:11px">{{$task->taskable->ref_number}}</div>
            @endif
        </div>
        <div style="text-align:right">
            <div style="color:{{$isOverdue ? "#f87171" : "rgba(255,255,255,.3)"}}; font-size:11px">
                {{$task->due_date ? $task->due_date->format("d M") : "No due date"}}
                {{$isOverdue ? "⚠️" : ""}}
            </div>
            <div style="color:{{$pc}};font-size:10px;text-transform:uppercase;font-weight:700">{{$task->priority}}</div>
        </div>
    </div>
    @endforeach
    @if($myTasks->count() > 5)
    <div style="color:rgba(255,255,255,.3);font-size:12px;text-align:center;margin-top:8px">+{{$myTasks->count()-5}} more tasks on the board</div>
    @endif
</div>
@endif

{{-- HR: Overdue correspondence summary --}}
@if($isHR && $overdueCorr > 0)
<div style="background:rgba(239,68,68,.05);border:1px solid rgba(239,68,68,.2);border-radius:12px;padding:20px;margin-bottom:20px">
    <div style="color:#f87171;font-weight:600;font-size:15px;margin-bottom:14px">⚠️ Overdue Correspondence ({{$overdueCorr}})</div>
    @foreach(App\Models\Correspondence::whereNotIn("status",["closed","responded"])->whereNotNull("response_due_date")->where("response_due_date","<",now())->take(4)->get() as $corr)
    <div style="display:flex;justify-content:space-between;padding:10px 12px;background:rgba(239,68,68,.05);border-radius:8px;margin-bottom:6px;border:1px solid rgba(239,68,68,.1)">
        <div>
            <span style="background:rgba(99,102,241,.2);color:#a5b4fc;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:600">{{$corr->ref_number}}</span>
            <span style="color:#f1f5f9;font-size:13px;margin-left:8px">{{Str::limit($corr->subject,40)}}</span>
        </div>
        <div style="color:#f87171;font-size:11px;white-space:nowrap">Due: {{$corr->response_due_date->format("d M Y")}}</div>
    </div>
    @endforeach
    <a href="/admin/correspondence" style="color:#f87171;font-size:12px;text-decoration:none">View all correspondence →</a>
</div>
@endif

{{-- HR: Expense trends --}}
@if($isAdmin)
@php
$expByCategory = App\Models\SiteExpense::where("status","approved")
    ->whereMonth("expense_date",now()->month)
    ->get()
    ->groupBy("category")
    ->map(fn($g) => $g->sum("amount"))
    ->sortDesc()
    ->take(5);
$totalThisMonth = $expByCategory->sum();
@endphp
@if($totalThisMonth > 0)
<div style="background:#1e293b;border-radius:12px;padding:20px;border:1px solid rgba(255,255,255,.07)">
    <div style="color:#f1f5f9;font-weight:600;font-size:15px;margin-bottom:4px">💰 Expense Summary — {{now()->format("F Y")}}</div>
    <div style="color:rgba(255,255,255,.3);font-size:12px;margin-bottom:14px">Total approved: UGX {{number_format($totalThisMonth)}}</div>
    @foreach($expByCategory as $cat => $amt)
    @php $pct = $totalThisMonth > 0 ? ($amt/$totalThisMonth)*100 : 0; @endphp
    <div style="margin-bottom:10px">
        <div style="display:flex;justify-content:space-between;margin-bottom:4px">
            <span style="color:rgba(255,255,255,.6);font-size:12px;text-transform:capitalize">{{str_replace("_"," ",$cat)}}</span>
            <span style="color:#f1f5f9;font-size:12px;font-weight:600">UGX {{number_format($amt)}}</span>
        </div>
        <div style="background:rgba(255,255,255,.06);border-radius:4px;height:6px">
            <div style="background:linear-gradient(90deg,#6366f1,#8b5cf6);height:6px;border-radius:4px;width:{{round($pct)}}%"></div>
        </div>
    </div>
    @endforeach
    <a href="/admin/expenses" style="color:#a5b4fc;font-size:12px;text-decoration:none">View all expenses →</a>
</div>
@endif
@endif

</div>
</x-filament-panels::page>
');
echo "Employee dashboard enhanced\n";

// ============================================================
// 5. ENHANCED STATS OVERVIEW WIDGET - role aware
// ============================================================
file_put_contents('app/Filament/Employee/Widgets/StatsOverview.php', '<?php
namespace App\Filament\Employee\Widgets;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\{Employee,Attendance,Leave,Task,Correspondence,SiteExpense};
use Carbon\Carbon;

class StatsOverview extends BaseWidget {
    protected function getStats(): array {
        $user = auth()->user();
        $isAdmin = $user?->hasAnyRole(["super_admin","admin"]);
        $isHR = $user?->hasAnyRole(["super_admin","admin","hr_assistant"]);
        $stats = [];

        if ($isHR) {
            $stats[] = Stat::make("Staff On Site", Attendance::whereDate("date",today())->count())
                ->description("Present today out of " . Employee::where("is_active",true)->whereNotIn("employee_code",["SYS-001","CRBC-VIEW"])->count())
                ->descriptionIcon("heroicon-m-users")->color("success");
            $stats[] = Stat::make("Pending Leaves", Leave::where("status","pending")->count())
                ->description("Awaiting approval")->descriptionIcon("heroicon-m-document-text")->color("warning");
        }

        $myTasks = Task::where("assignee_id",$user->id)->where("board_status","!=","completed")->count();
        $stats[] = Stat::make("My Tasks", $myTasks)
            ->description("Pending on board")->descriptionIcon("heroicon-m-clipboard-document-list")->color("info");

        if ($isHR) {
            $overdueCorr = Correspondence::whereNotIn("status",["closed","responded"])
                ->whereNotNull("response_due_date")->where("response_due_date","<",now())->count();
            $stats[] = Stat::make("Overdue Correspondence", $overdueCorr)
                ->description("Need response now")->descriptionIcon("heroicon-m-envelope")->color($overdueCorr > 0 ? "danger" : "success");
        }

        if ($isAdmin) {
            $pendingExp = SiteExpense::where("status","pending")->count();
            $thisMonthExp = SiteExpense::where("status","approved")->whereMonth("expense_date",now()->month)->sum("amount");
            $stats[] = Stat::make("Pending Expenses", $pendingExp)
                ->description("UGX ".number_format($thisMonthExp)." approved this month")
                ->descriptionIcon("heroicon-m-banknotes")->color($pendingExp > 0 ? "warning" : "success");
        }

        return $stats;
    }
}');
echo "StatsOverview widget enhanced\n";

echo "\n=== ALL DONE ===\n";
echo "✅ Task Board — role-based, overdue badge, notifications\n";
echo "✅ Employee Dashboard — smart cards, overdue correspondence, expense trends\n";
echo "✅ Stats Widget — role-aware stats\n";
echo "✅ TaskAssignedNotification — email when task assigned\n";
echo "\nRun: php artisan optimize:clear && php artisan serve --host=0.0.0.0 --port=8000\n";
