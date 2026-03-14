<?php

namespace App\Filament\Widgets;

use App\Models\Correspondence;
use App\Models\SiteExpense;
use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TaskBoardStats extends BaseWidget
{
    protected function getStats(): array
    {
        $overdueCorrespondence = Correspondence::whereNotIn('status', ['closed', 'responded'])
            ->whereNotNull('response_due_date')
            ->where('response_due_date', '<', now())
            ->count();

        $pendingFollowups = DB::table('correspondence_followups')
            ->where('status', 'pending')
            ->whereDate('follow_up_date', '<=', now())
            ->count();

        $pendingExpenses = SiteExpense::where('status', 'pending')->count();

        $activeTasks = Task::whereNotIn('board_status', ['completed'])->count();

        return [
            Stat::make('Active Tasks', $activeTasks)
                ->description('Tasks in progress')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
            
            Stat::make('Overdue Responses', $overdueCorrespondence)
                ->description('Correspondence needing attention')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
            
            Stat::make('Pending Follow-ups', $pendingFollowups)
                ->description('Actions required')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            
            Stat::make('Expenses to Approve', $pendingExpenses)
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}