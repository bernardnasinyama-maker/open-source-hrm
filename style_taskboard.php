<?php

// ============================================================
// CARD - the individual task card on the kanban
// ============================================================
file_put_contents('resources/views/vendor/flowforge/livewire/card.blade.php', '
@php
$priority = $record->priority ?? "medium";
$status   = $record->board_status ?? "todo";
$type     = $record->taskable_type ? class_basename($record->taskable_type) : "Task";
$isOverdue = $record->due_date && $record->due_date->isPast() && $status !== "completed";

$priorityBorder = match($priority) {
    "critical" => "#ef4444",
    "high"     => "#f97316",
    "medium"   => "#f59e0b",
    "low"      => "#10b981",
    default    => "#6366f1",
};
$priorityColor = $priorityBorder;

$typeIcon = match($type) {
    "Correspondence" => "📬",
    "SiteExpense"    => "💰",
    default          => "📋",
};
$typeBg = match($type) {
    "Correspondence" => "rgba(99,102,241,.2)",
    "SiteExpense"    => "rgba(245,158,11,.2)",
    default          => "rgba(16,185,129,.2)",
};
$typeColor = match($type) {
    "Correspondence" => "#a5b4fc",
    "SiteExpense"    => "#fbbf24",
    default          => "#6ee7b7",
};
@endphp

<div
    wire:key="card-{{ $record->id }}"
    style="
        background: #1e293b;
        border: 1px solid rgba(255,255,255,.07);
        border-left: 3px solid {{ $priorityBorder }};
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all .15s ease;
        position: relative;
    "
    onmouseover="this.style.background=\'#263548\';this.style.borderColor=\'rgba(255,255,255,.15)\';this.style.transform=\'translateY(-1px)\'"
    onmouseout="this.style.background=\'#1e293b\';this.style.borderColor=\'rgba(255,255,255,.07)\';this.style.transform=\'none\'"
    x-on:click="selectCard({{ $record->id }})"
>
    {{-- Type + Priority badges --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
        <span style="background:{{ $typeBg }};color:{{ $typeColor }};padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600">
            {{ $typeIcon }} {{ $type === "SiteExpense" ? "Expense" : $type }}
        </span>
        <span style="color:{{ $priorityColor }};font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em">
            {{ $priority }}
        </span>
    </div>

    {{-- Title --}}
    <div style="color:#f1f5f9;font-size:13px;font-weight:600;line-height:1.4;margin-bottom:8px">
        {{ Str::limit($record->title, 45) }}
    </div>

    {{-- Reference badge if linked --}}
    @if($record->taskable?->ref_number)
    <div style="margin-bottom:8px">
        <span style="background:rgba(99,102,241,.15);color:#a5b4fc;padding:2px 7px;border-radius:4px;font-size:10px;font-weight:600;font-family:monospace">
            {{ $record->taskable->ref_number }}
        </span>
    </div>
    @endif

    {{-- Description snippet --}}
    @if($record->description)
    <div style="color:rgba(255,255,255,.35);font-size:11px;line-height:1.5;margin-bottom:10px">
        {{ Str::limit($record->description, 55) }}
    </div>
    @endif

    {{-- Footer: assignee + due date --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;padding-top:8px;border-top:1px solid rgba(255,255,255,.06)">
        <div style="display:flex;align-items:center;gap:5px">
            @if($record->assignee)
            <div style="width:22px;height:22px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;color:white;flex-shrink:0">
                {{ strtoupper(substr($record->assignee->first_name,0,1)) }}{{ strtoupper(substr($record->assignee->last_name,0,1)) }}
            </div>
            <span style="color:rgba(255,255,255,.4);font-size:10px">{{ $record->assignee->first_name }}</span>
            @else
            <span style="color:rgba(255,255,255,.2);font-size:10px;font-style:italic">Unassigned</span>
            @endif
        </div>
        @if($record->due_date)
        <span style="color:{{ $isOverdue ? "#f87171" : "rgba(255,255,255,.3)" }};font-size:10px;display:flex;align-items:center;gap:3px">
            {{ $isOverdue ? "⚠️" : "📅" }} {{ $record->due_date->format("d M") }}
        </span>
        @endif
    </div>
</div>
');

// ============================================================
// COLUMN - the kanban column container
// ============================================================
file_put_contents('resources/views/vendor/flowforge/livewire/column.blade.php', '
@php
$colColors = [
    "todo"           => ["bg"=>"rgba(99,102,241,.1)",  "border"=>"rgba(99,102,241,.3)",  "dot"=>"#6366f1", "label"=>"#a5b4fc"],
    "in_progress"    => ["bg"=>"rgba(59,130,246,.1)",  "border"=>"rgba(59,130,246,.3)",  "dot"=>"#3b82f6", "label"=>"#93c5fd"],
    "pending_review" => ["bg"=>"rgba(245,158,11,.1)",  "border"=>"rgba(245,158,11,.3)",  "dot"=>"#f59e0b", "label"=>"#fcd34d"],
    "completed"      => ["bg"=>"rgba(16,185,129,.1)",  "border"=>"rgba(16,185,129,.3)",  "dot"=>"#10b981", "label"=>"#6ee7b7"],
];
$col = $colColors[$columnId] ?? ["bg"=>"rgba(255,255,255,.04)","border"=>"rgba(255,255,255,.1)","dot"=>"#6b7280","label"=>"#9ca3af"];
$count = count($column["records"] ?? []);
@endphp

<div
    style="
        min-width: 280px;
        max-width: 300px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        height: 100%;
    "
    wire:key="column-{{ $columnId }}"
>
    {{-- Column Header --}}
    <div style="
        background: {{ $col["bg"] }};
        border: 1px solid {{ $col["border"] }};
        border-radius: 10px 10px 0 0;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0;
    ">
        <div style="display:flex;align-items:center;gap:8px">
            <div style="width:8px;height:8px;border-radius:50%;background:{{ $col["dot"] }};box-shadow:0 0 6px {{ $col["dot"] }}"></div>
            <span style="color:{{ $col["label"] }};font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em">
                {{ $column["label"] }}
            </span>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <span style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.5);padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">
                {{ $count }}
            </span>
            @if(isset($column["actions"]))
                @foreach($column["actions"] as $action)
                    {{ $action }}
                @endforeach
            @endif
        </div>
    </div>

    {{-- Cards container --}}
    <div
        style="
            background: rgba(15,23,42,.6);
            border: 1px solid {{ $col["border"] }};
            border-top: none;
            border-radius: 0 0 10px 10px;
            padding: 10px;
            flex: 1;
            overflow-y: auto;
            min-height: 200px;
        "
        wire:sortable
        wire:sortable.options="{ group: \'cards\', animation: 150 }"
        x-on:end="reorderCards($event, \'{{ $columnId }}\')"
    >
        @forelse($column["records"] ?? [] as $record)
            <div wire:sortable.item="{{ $record->id }}" wire:key="sortable-{{ $record->id }}">
                <livewire:flowforge.card
                    :record="$record"
                    :config="$config"
                    wire:key="card-component-{{ $record->id }}"
                />
            </div>
        @empty
            <div style="text-align:center;padding:30px 10px">
                <div style="font-size:28px;margin-bottom:8px;opacity:.3">📭</div>
                <div style="color:rgba(255,255,255,.2);font-size:11px">No tasks here</div>
            </div>
        @endforelse
    </div>
</div>
');

// ============================================================
// EMPTY COLUMN STATE
// ============================================================
file_put_contents('resources/views/vendor/flowforge/livewire/empty-column.blade.php', '
<div style="text-align:center;padding:40px 20px">
    <div style="font-size:32px;margin-bottom:10px;opacity:.25">📭</div>
    <div style="color:rgba(255,255,255,.2);font-size:12px">No tasks</div>
</div>
');

// ============================================================
// BOARD PAGE WRAPPER
// ============================================================
file_put_contents('resources/views/vendor/flowforge/filament/pages/board-page.blade.php', '
<x-filament-panels::page>
    <style>
        .fi-page-header { margin-bottom: 16px !important; }
        /* Scrollbar styling */
        .flowforge-board::-webkit-scrollbar { height: 6px; }
        .flowforge-board::-webkit-scrollbar-track { background: rgba(255,255,255,.03); border-radius: 3px; }
        .flowforge-board::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 3px; }
        .flowforge-board::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,.2); }
    </style>
    <div class="flowforge-board" style="width:100%;overflow-x:auto;padding-bottom:16px">
        @include("flowforge::index", ["columns" => $columns, "config" => $config])
    </div>
</x-filament-panels::page>
');

echo "All flowforge views styled!\n";
echo "Run: php artisan optimize:clear && php artisan serve --host=0.0.0.0 --port=8000\n";
