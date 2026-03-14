<?php
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
                Grid::make(2)->schema([
                    TextEntry::make("assignee.first_name")
                        ->icon("heroicon-o-user")
                        ->hiddenLabel()
                        ->default("Unassigned")
                        ->formatStateUsing(fn($s, $r) => $r?->assignee ? $r->assignee->first_name . " " . $r->assignee->last_name : "Unassigned"),
                    TextEntry::make("due_date")
                        ->date("d M Y")
                        ->icon("heroicon-o-calendar")
                        ->hiddenLabel()
                        ->color(fn($record) => $record->due_date && $record->due_date->isPast() && $record->board_status !== "completed" ? "danger" : "gray"),
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
}