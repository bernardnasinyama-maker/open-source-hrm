<?php
// Run this once after restore to add tasks
// php ADD_TASKS.php

// Tasks via tinker-safe approach
$commands = [
    "App\\Models\\Task::truncate();",
    "App\\Models\\Task::create(['title'=>'Follow up on RFI-2026-001','description'=>'Chase consultant for road design response','board_status'=>'todo','board_position'=>1,'priority'=>'high','assignee_id'=>App\\Models\\Employee::where('employee_code','CRBC-ENG-001')->value('id'),'due_date'=>now()->addDays(3),'user_id'=>1,'taskable_type'=>'App\\\\Models\\\\Correspondence','taskable_id'=>App\\Models\\Correspondence::where('ref_number','RFI-2026-001')->value('id')]);",
    "App\\Models\\Task::create(['title'=>'Draft response to NCR-2026-001','description'=>'Prepare formal response to compaction failure','board_status'=>'todo','board_position'=>2,'priority'=>'critical','assignee_id'=>App\\Models\\Employee::where('employee_code','CRBC-ENG-001')->value('id'),'due_date'=>now()->addDays(2),'user_id'=>1]);",
    "App\\Models\\Task::create(['title'=>'Approve March fuel expenses','description'=>'Review 3 pending fuel claims','board_status'=>'in_progress','board_position'=>1,'priority'=>'medium','assignee_id'=>App\\Models\\Employee::where('employee_code','CRBC-ADM-001')->value('id'),'due_date'=>now()->addDays(1),'user_id'=>1,'taskable_type'=>'App\\\\Models\\\\SiteExpense','taskable_id'=>App\\Models\\SiteExpense::where('ref_number','EXP-2026-001')->value('id')]);",
    "App\\Models\\Task::create(['title'=>'Submit March attendance report','description'=>'Compile March 2026 attendance for PM','board_status'=>'pending_review','board_position'=>1,'priority'=>'critical','due_date'=>now()->subDay(),'user_id'=>1]);",
    "App\\Models\\Task::create(['title'=>'Daily progress report week 11','description'=>'Chainage Ch 34+200 to Ch 35+600 done','board_status'=>'completed','board_position'=>1,'priority'=>'low','due_date'=>now(),'user_id'=>1]);",
    "echo 'Tasks: '.App\\Models\\Task::count().PHP_EOL;",
];

foreach ($commands as $cmd) {
    echo shell_exec("php artisan tinker --execute=\"{$cmd}\"");
}
