<?php

// ============================================================
// 1. STYLE THE CARD - keep original logic, just restyle
// ============================================================
file_put_contents('resources/views/vendor/flowforge/livewire/card.blade.php', <<<'BLADE'
@props(['columnId', 'record'])

@php
    $processedRecordActions = $this->getBoard()->getBoardRecordActions($record);
    $hasActions = !empty($processedRecordActions);
    $cardAction = $this->getBoard()->getCardAction();
    $hasCardAction = $cardAction !== null;
    $hasPositionIdentifier = $this->getBoard()->getPositionIdentifierAttribute() !== null;

    $priority = $record['priority'] ?? 'medium';
    $type     = isset($record['taskable_type']) ? class_basename($record['taskable_type']) : 'Task';

    $borderColor = match($priority) {
        'critical' => '#ef4444',
        'high'     => '#f97316',
        'medium'   => '#f59e0b',
        'low'      => '#10b981',
        default    => '#6366f1',
    };
    $typeIcon = match($type) {
        'Correspondence' => '📬',
        'SiteExpense'    => '💰',
        default          => '📋',
    };
@endphp

<div
    @class(['flowforge-card mb-2 overflow-hidden transition-all duration-150'])
    style="
        background: #1e293b;
        border: 1px solid rgba(255,255,255,.08);
        border-left: 3px solid {{ $borderColor }};
        border-radius: 10px;
        {{ ($hasActions || $hasCardAction) ? 'cursor:pointer;' : '' }}
        {{ $hasPositionIdentifier ? 'cursor:grab;' : '' }}
    "
    onmouseover="this.style.background='#263548';this.style.boxShadow='0 4px 20px rgba(0,0,0,.4)';this.style.transform='translateY(-1px)'"
    onmouseout="this.style.background='#1e293b';this.style.boxShadow='none';this.style.transform='none'"
    @if($hasPositionIdentifier)
        x-sortable-handle
        x-sortable-item="{{ $record['id'] }}"
    @endif
    data-card-id="{{ $record['id'] }}"
>
    {{-- Header: type icon + priority + actions --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px 0">
        <span style="font-size:10px;color:rgba(255,255,255,.35);font-weight:600;text-transform:uppercase;letter-spacing:.06em">
            {{ $typeIcon }} {{ $type === 'SiteExpense' ? 'Expense' : $type }}
        </span>
        <div style="display:flex;align-items:center;gap:6px">
            <span style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:{{ $borderColor }}">
                {{ $priority }}
            </span>
            @if($hasActions)
                <div>
                    <x-filament-actions::group :actions="$processedRecordActions"/>
                </div>
            @endif
        </div>
    </div>

    {{-- Title --}}
    <div
        style="padding:6px 12px 8px;color:#f1f5f9;font-size:13px;font-weight:600;line-height:1.4"
        @if($hasCardAction && $cardAction)
            wire:click="mountAction('{{ $cardAction }}', [], @js(['recordKey' => $record['id']]))"
        @endif
    >
        {{ Str::limit($record['title'], 50) }}
    </div>

    {{-- Schema (description, ref, assignee, due date from cardSchema) --}}
    @if(filled($record['schema']))
        <div
            style="padding:0 12px 10px"
            @if($hasCardAction && $cardAction)
                wire:click="mountAction('{{ $cardAction }}', [], @js(['recordKey' => $record['id']]))"
            @endif
        >
            {{ $record['schema'] }}
        </div>
    @endif
</div>
BLADE);

echo "Card styled\n";

// ============================================================
// 2. SIMPLIFY cardSchema in TaskBoard - remove title (already shown above)
//    and style the remaining entries minimally
// ============================================================
$tb = file_get_contents('app/Filament/Pages/TaskBoard.php');

// Replace the cardSchema block with a minimal clean version
$oldSchema = '->cardSchema(fn(Schema $schema) => $schema->components([
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
                            ->formatStateUsing(fn($s, $r) => $r?->assignee ? $r->assignee->first_name . " " . $r->assignee->last_name : "Unassigned"),
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
            ]))';

$newSchema = '->cardSchema(fn(Schema $schema) => $schema->components([
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
            ]))';

if (strpos($tb, '->cardSchema(') !== false) {
    $tb = str_replace($oldSchema, $newSchema, $tb);
    file_put_contents('app/Filament/Pages/TaskBoard.php', $tb);
    echo "CardSchema simplified\n";
} else {
    echo "CardSchema pattern not matched - check manually\n";
}

// ============================================================
// 3. STYLE THE COLUMN HEADER only (keep original logic)
// ============================================================
$col = file_get_contents('resources/views/vendor/flowforge/livewire/column.blade.php');
$col = str_replace(
    'class="flowforge-column w-[300px] min-w-[300px] flex-shrink-0 border border-gray-200 dark:border-gray-700 shadow-sm dark:shadow-md rounded-xl flex flex-col max-h-full overflow-hidden"',
    'class="flowforge-column w-[280px] min-w-[280px] flex-shrink-0 rounded-xl flex flex-col max-h-full overflow-hidden" style="border:1px solid rgba(255,255,255,.08);background:rgba(15,23,42,.5)"',
    $col
);
$col = str_replace(
    'class="flowforge-column-header flex items-center justify-between py-3 px-4 border-b border-gray-200 dark:border-gray-700"',
    'class="flowforge-column-header flex items-center justify-between py-3 px-4" style="border-bottom:1px solid rgba(255,255,255,.08)"',
    $col
);
$col = str_replace(
    'class="text-sm font-medium text-gray-700 dark:text-gray-200"',
    'class="text-sm font-semibold" style="color:#f1f5f9"',
    $col
);
$col = str_replace(
    'class="flowforge-column-content p-3 flex-1 overflow-y-auto overflow-x-hidden overscroll-contain kanban-cards"',
    'class="flowforge-column-content p-3 flex-1 overflow-y-auto overflow-x-hidden overscroll-contain kanban-cards" style="scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.1) transparent"',
    $col
);
file_put_contents('resources/views/vendor/flowforge/livewire/column.blade.php', $col);
echo "Column styled\n";

// ============================================================
// 4. BOARD PAGE - clean and simple
// ============================================================
file_put_contents('resources/views/vendor/flowforge/filament/pages/board-page.blade.php',
'<x-filament-panels::page>
    <div class="h-[calc(100vh-11rem)]">
        {{ $this->board }}
    </div>
</x-filament-panels::page>');
echo "Board page reset\n";

echo "\nAll done! Run: php artisan optimize:clear && php artisan serve --host=0.0.0.0 --port=8000\n";
