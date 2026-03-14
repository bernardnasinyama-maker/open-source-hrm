<?php

// ============================================================
// 1. BEAUTIFUL BOARD PAGE - lilac blue theme
// ============================================================
$blade = <<<'BLADE'
<x-filament-panels::page class="!p-0 !overflow-hidden">
<style>
/* ── Layout fix ─────────────────────────────────────────── */
[x-data*="flowforge"] {
    display: flex !important;
    flex-direction: column !important;
    height: calc(100vh - 9rem) !important;
}
[x-data*="flowforge"] > div:nth-child(2) {
    flex: 1 !important;
    overflow: hidden !important;
}
[x-data*="flowforge"] > div:nth-child(2) > div {
    display: flex !important;
    flex-direction: row !important;
    gap: 14px !important;
    height: 100% !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    align-items: flex-start !important;
    padding: 4px 4px 16px 4px !important;
}

/* ── Column ─────────────────────────────────────────────── */
.flowforge-column {
    min-width: 275px !important;
    max-width: 275px !important;
    flex-shrink: 0 !important;
    border-radius: 14px !important;
    border: 1px solid rgba(139,92,246,0.2) !important;
    background: linear-gradient(160deg, #1e1b4b 0%, #1e293b 100%) !important;
    overflow: hidden !important;
}
.flowforge-column-header {
    background: rgba(139,92,246,0.12) !important;
    border-bottom: 1px solid rgba(139,92,246,0.2) !important;
    padding: 12px 14px !important;
}
.flowforge-column-header h3 {
    color: #c4b5fd !important;
    font-weight: 700 !important;
    font-size: 12px !important;
    text-transform: uppercase !important;
    letter-spacing: 0.07em !important;
}
.flowforge-column-content {
    max-height: calc(100vh - 15rem) !important;
    overflow-y: auto !important;
    padding: 10px !important;
    scrollbar-width: thin !important;
    scrollbar-color: rgba(139,92,246,0.3) transparent !important;
}

/* ── Cards ──────────────────────────────────────────────── */
.flowforge-card {
    background: linear-gradient(135deg, #2d2a6e 0%, #1e2d5a 100%) !important;
    border: 1px solid rgba(139,92,246,0.25) !important;
    border-radius: 12px !important;
    margin-bottom: 10px !important;
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease !important;
    overflow: hidden !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important;
}
.flowforge-card:hover {
    transform: translateY(-3px) !important;
    box-shadow: 0 8px 28px rgba(99,102,241,0.35) !important;
    border-color: rgba(139,92,246,0.6) !important;
}
.flowforge-card .flowforge-card-content h4 {
    color: #e2e8f0 !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    line-height: 1.45 !important;
    padding: 12px 12px 4px !important;
}
/* Schema text inside cards */
.flowforge-card .fi-in-text {
    color: rgba(196,181,253,0.7) !important;
    font-size: 11px !important;
}
.flowforge-card .fi-icon {
    color: rgba(139,92,246,0.6) !important;
}

/* ── Empty column ────────────────────────────────────────── */
.flowforge-column-content .border-dashed {
    border-color: rgba(139,92,246,0.2) !important;
    border-radius: 10px !important;
    background: rgba(139,92,246,0.04) !important;
}
.flowforge-column-content .border-dashed p {
    color: rgba(196,181,253,0.4) !important;
    font-size: 12px !important;
}
.flowforge-column-content .border-dashed svg {
    color: rgba(139,92,246,0.3) !important;
}

/* ── Badges ─────────────────────────────────────────────── */
.fi-badge {
    border-radius: 20px !important;
}

/* ── Search bar ─────────────────────────────────────────── */
.fi-ta-search-field .fi-input {
    background: rgba(139,92,246,0.08) !important;
    border-color: rgba(139,92,246,0.2) !important;
    color: #e2e8f0 !important;
    border-radius: 8px !important;
}

/* ── Scrollbar ──────────────────────────────────────────── */
::-webkit-scrollbar { height: 5px; width: 5px; }
::-webkit-scrollbar-track { background: rgba(139,92,246,0.05); border-radius: 3px; }
::-webkit-scrollbar-thumb { background: rgba(139,92,246,0.3); border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: rgba(139,92,246,0.5); }

/* ── Add button ─────────────────────────────────────────── */
.fi-ac-icon-btn-action {
    color: #a78bfa !important;
}
.fi-ac-icon-btn-action:hover {
    color: #c4b5fd !important;
}
</style>
    <div class="h-[calc(100vh-8rem)] overflow-hidden">
        {{ $this->board }}
    </div>
</x-filament-panels::page>
BLADE;

file_put_contents('resources/views/vendor/flowforge/filament/pages/board-page.blade.php', $blade);
echo "Board page styled\n";

// ============================================================
// 2. ADD MISSING TASKS (the 3 that failed earlier)
// ============================================================
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$existing = App\Models\Task::count();
echo "Existing tasks: $existing\n";

$shi  = App\Models\Employee::where('email','mr.shi@crbc.com')->first();
$me   = App\Models\Employee::where('email','bernardnasinyama@gmail.com')->first();
$corr = App\Models\Correspondence::first();
$exp  = App\Models\SiteExpense::first();

if ($existing < 2) {
    App\Models\Task::create([
        'title'          => 'Approve March fuel expenses',
        'description'    => 'Review 3 pending fuel claims from site team',
        'board_status'   => 'in_progress',
        'board_position' => 1,
        'priority'       => 'medium',
        'assignee_id'    => $shi?->id,
        'due_date'       => now()->addDays(1),
        'user_id'        => 1,
        'taskable_type'  => $exp ? App\Models\SiteExpense::class : null,
        'taskable_id'    => $exp?->id,
    ]);
}

if ($existing < 3) {
    App\Models\Task::create([
        'title'          => 'Submit March attendance report',
        'description'    => 'Compile and submit March 2026 attendance to PM',
        'board_status'   => 'pending_review',
        'board_position' => 1,
        'priority'       => 'critical',
        'assignee_id'    => $me?->id,
        'due_date'       => now()->subDay(),
        'user_id'        => 1,
    ]);
}

if ($existing < 4 && $corr) {
    App\Models\Task::create([
        'title'          => 'Draft response: ' . Str::limit($corr->subject, 35),
        'description'    => 'Prepare formal response to ' . $corr->ref_number,
        'board_status'   => 'todo',
        'board_position' => 2,
        'priority'       => 'critical',
        'assignee_id'    => $me?->id,
        'due_date'       => now()->addDays(2),
        'user_id'        => 1,
        'taskable_type'  => App\Models\Correspondence::class,
        'taskable_id'    => $corr->id,
    ]);
}

App\Models\Task::create([
    'title'          => 'Update site daily progress report',
    'description'    => 'Fill in chainage progress for week 11',
    'board_status'   => 'completed',
    'board_position' => 1,
    'priority'       => 'low',
    'assignee_id'    => $me?->id,
    'due_date'       => now(),
    'user_id'        => 1,
]);

echo "Total tasks now: " . App\Models\Task::count() . "\n";
echo "\nDone! Run: php artisan optimize:clear && php artisan serve --host=0.0.0.0 --port=8000\n";
