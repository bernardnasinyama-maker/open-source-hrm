<?php
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
}