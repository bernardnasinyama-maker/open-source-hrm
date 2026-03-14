<?php

// ============================================================
// CARD - correct structure matching flowforge's actual blade
// ============================================================
file_put_contents('resources/views/vendor/flowforge/livewire/card.blade.php', '
@props(["columnId", "record"])

@php
    $processedRecordActions = $this->getBoard()->getBoardRecordActions($record);
    $hasActions             = !empty($processedRecordActions);
    $cardAction             = $this->getBoard()->getCardAction();
    $hasCardAction          = $cardAction !== null;
    $hasPositionIdentifier  = $this->getBoard()->getPositionIdentifierAttribute() !== null;

    $priority = $record["priority"] ?? "medium";
    $type     = isset($record["taskable_type"]) ? class_basename($record["taskable_type"]) : "Task";
    $isOverdue = isset($record["due_date"]) && $record["due_date"] && \Carbon\Carbon::parse($record["due_date"])->isPast() && ($record["board_status"] ?? "") !== "completed";

    $borderColor = match($priority) {
        "critical" => "#ef4444",
        "high"     => "#f97316",
        "medium"   => "#f59e0b",
        "low"      => "#10b981",
        default    => "#6366f1",
    };
    $typeIcon = match($type) {
        "Correspondence" => "📬",
        "SiteExpense"    => "💰",
        default          => "📋",
    };
    $typeLabel = match($type) {
        "Correspondence" => "Correspondence",
        "SiteExpense"    => "Expense",
        default          => "Task",
    };
    $typeBg    = match($type) { "Correspondence"=>"rgba(99,102,241,.18)","SiteExpense"=>"rgba(245,158,11,.18)",default=>"rgba(16,185,129,.18)" };
    $typeColor = match($type) { "Correspondence"=>"#a5b4fc","SiteExpense"=>"#fbbf24",default=>"#6ee7b7" };
    $priColor  = match($priority) { "critical"=>"#f87171","high"=>"#fb923c","medium"=>"#fbbf24","low"=>"#34d399",default=>"#a5b4fc" };

    // Assignee initials
    $assigneeName    = "";
    $assigneeInitials = "?";
    if (isset($record["assignee"]) && $record["assignee"]) {
        $a = $record["assignee"];
        $fn = is_array($a) ? ($a["first_name"] ?? "") : ($a->first_name ?? "");
        $ln = is_array($a) ? ($a["last_name"]  ?? "") : ($a->last_name  ?? "");
        $assigneeName     = trim("$fn $ln") ?: "Unassigned";
        $assigneeInitials = strtoupper(substr($fn,0,1).substr($ln,0,1));
    }

    $dueDate = isset($record["due_date"]) && $record["due_date"]
        ? \Carbon\Carbon::parse($record["due_date"])->format("d M")
        : null;

    $ref = $record["taskable"]["ref_number"] ?? (is_object($record["taskable"] ?? null) ? $record["taskable"]?->ref_number : null) ?? null;
@endphp

<div
    @class(["flowforge-card mb-2"])
    style="
        background: #1e293b;
        border: 1px solid rgba(255,255,255,.07);
        border-left: 3px solid {{ $borderColor }};
        border-radius: 10px;
        overflow: hidden;
        transition: all .15s ease;
        {{ ($hasActions || $hasCardAction) ? "cursor:pointer;" : "" }}
        {{ $hasPositionIdentifier ? "cursor:grab;" : "" }}
    "
    onmouseover="this.style.background=\'#263548\';this.style.transform=\'translateY(-1px)\';this.style.boxShadow=\'0 4px 12px rgba(0,0,0,.3)\'"
    onmouseout="this.style.background=\'#1e293b\';this.style.transform=\'none\';this.style.boxShadow=\'none\'"
    @if($hasPositionIdentifier)
        x-sortable-handle
        x-sortable-item="{{ $record["id"] }}"
    @endif
    data-card-id="{{ $record["id"] }}"
    data-position="{{ $record["position"] ?? "" }}"
>
    <div style="padding:12px 14px">

        {{-- Row 1: Type badge + Priority + Actions --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="background:{{ $typeBg }};color:{{ $typeColor }};padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600">
                {{ $typeIcon }} {{ $typeLabel }}
            </span>
            <div style="display:flex;align-items:center;gap:6px">
                <span style="color:{{ $priColor }};font-size:10px;font-weight:700;text-transform:uppercase">{{ $priority }}</span>
                @if($hasActions)
                    <div style="margin-left:4px">
                        <x-filament-actions::group :actions="$processedRecordActions"/>
                    </div>
                @endif
            </div>
        </div>

        {{-- Row 2: Title --}}
        <div
            style="color:#f1f5f9;font-size:13px;font-weight:600;line-height:1.4;margin-bottom:8px"
            @if($hasCardAction && $cardAction)
                wire:click="mountAction(\'{{ $cardAction }}\', [], @js([\'recordKey\' => $record[\'id\']]))"
            @endif
        >
            {{ Str::limit($record["title"], 50) }}
        </div>

        {{-- Row 3: Reference pill --}}
        @if($ref)
        <div style="margin-bottom:8px">
            <span style="background:rgba(99,102,241,.15);color:#a5b4fc;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:700;font-family:monospace;letter-spacing:.03em">
                {{ $ref }}
            </span>
        </div>
        @endif

        {{-- Row 4: Schema (from cardSchema in TaskBoard) --}}
        @if(filled($record["schema"] ?? null))
        <div
            style="margin-bottom:8px"
            @if($hasCardAction && $cardAction)
                wire:click="mountAction(\'{{ $cardAction }}\', [], @js([\'recordKey\' => $record[\'id\']]))"
            @endif
        >
            {{ $record["schema"] }}
        </div>
        @endif

        {{-- Row 5: Footer --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:8px;border-top:1px solid rgba(255,255,255,.06)">
            {{-- Assignee --}}
            @if($assigneeName && $assigneeName !== "Unassigned")
            <div style="display:flex;align-items:center;gap:5px">
                <div style="width:22px;height:22px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:8px;font-weight:800;color:white;flex-shrink:0">
                    {{ $assigneeInitials }}
                </div>
                <span style="color:rgba(255,255,255,.4);font-size:10px">{{ Str::before($assigneeName," ") }}</span>
            </div>
            @else
            <span style="color:rgba(255,255,255,.2);font-size:10px;font-style:italic">Unassigned</span>
            @endif

            {{-- Due date --}}
            @if($dueDate)
            <span style="color:{{ $isOverdue ? "#f87171" : "rgba(255,255,255,.35)" }};font-size:10px">
                {{ $isOverdue ? "⚠️ " : "📅 " }}{{ $dueDate }}
            </span>
            @endif
        </div>
    </div>
</div>
');

// ============================================================
// COLUMN - use the original structure but inject dark styles
// ============================================================
file_put_contents('resources/views/vendor/flowforge/livewire/column.blade.php', '
@props(["columnId", "column", "config"])

@php
    use Relaticle\Flowforge\Support\ColorResolver;
    $resolvedColor = ColorResolver::resolve($column["color"]);
    $isSemantic    = ColorResolver::isSemantic($resolvedColor);
    $colorShades   = $isSemantic ? null : $resolvedColor;

    $headerColors = [
        "todo"           => ["border"=>"rgba(99,102,241,.4)",  "dot"=>"#6366f1", "text"=>"#a5b4fc", "bg"=>"rgba(99,102,241,.08)"],
        "in_progress"    => ["border"=>"rgba(59,130,246,.4)",  "dot"=>"#3b82f6", "text"=>"#93c5fd", "bg"=>"rgba(59,130,246,.08)"],
        "pending_review" => ["border"=>"rgba(245,158,11,.4)",  "dot"=>"#f59e0b", "text"=>"#fcd34d", "bg"=>"rgba(245,158,11,.08)"],
        "completed"      => ["border"=>"rgba(16,185,129,.4)",  "dot"=>"#10b981", "text"=>"#6ee7b7", "bg"=>"rgba(16,185,129,.08)"],
    ];
    $hc = $headerColors[$columnId] ?? ["border"=>"rgba(255,255,255,.1)","dot"=>"#6b7280","text"=>"#9ca3af","bg"=>"rgba(255,255,255,.04)"];
    $total = $column["total"] ?? count($column["items"] ?? []);
@endphp

<div
    class="flowforge-column flex-shrink-0 flex flex-col max-h-full overflow-hidden"
    style="width:290px;min-width:290px;border:1px solid {{ $hc["border"] }};border-radius:12px;background:rgba(15,23,42,.5)"
>
    {{-- Column Header --}}
    <div style="background:{{ $hc["bg"] }};border-bottom:1px solid {{ $hc["border"] }};padding:10px 14px;display:flex;align-items:center;justify-content:space-between;border-radius:12px 12px 0 0">
        <div style="display:flex;align-items:center;gap:8px">
            <div style="width:8px;height:8px;border-radius:50%;background:{{ $hc["dot"] }};box-shadow:0 0 8px {{ $hc["dot"] }}55;flex-shrink:0"></div>
            <span style="color:{{ $hc["text"] }};font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em">
                {{ $column["label"] }}
            </span>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <span style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.5);padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700">
                {{ $total }}
            </span>
            @php $processedActions = $this->getBoardColumnActions($columnId); @endphp
            @if(count($processedActions) > 0)
                <div>
                    @if(count($processedActions) === 1)
                        {{ $processedActions[0] }}
                    @else
                        <x-filament-actions::group :actions="$processedActions"/>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Cards Area --}}
    <div
        data-column-id="{{ $columnId }}"
        @if($this->getBoard()->getPositionIdentifierAttribute())
            x-sortable
            x-sortable-group="cards"
            @end.stop="handleSortableEnd($event)"
        @endif
        @if(isset($column["total"]) && $column["total"] > count($column["items"] ?? []))
            @scroll.throttle.100ms="handleColumnScroll($event, \'{{ $columnId }}\')"
        @endif
        class="flowforge-column-content flex-1 overflow-y-auto overflow-x-hidden overscroll-contain"
        style="padding:10px;max-height:calc(100vh - 14rem);scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.1) transparent"
    >
        @if(isset($column["items"]) && count($column["items"]) > 0)
            @foreach($column["items"] as $record)
                <x-flowforge::card
                    :record="$record"
                    :config="$config"
                    :columnId="$columnId"
                    wire:key="card-{{ $record[\'id\'] }}"
                />
            @endforeach
            <div class="py-2 text-center">
                @if(isset($column["total"]) && $column["total"] > count($column["items"]))
                    <div x-intersect.margin.300px="handleSmoothScroll(\'{{ $columnId }}\')" class="w-full">
                        <div x-show="isLoadingColumn(\'{{ $columnId }}\')" x-transition class="text-xs" style="color:#6366f1">
                            Loading...
                        </div>
                    </div>
                @endif
            </div>
        @else
            <x-flowforge::empty-column :columnId="$columnId" :pluralCardLabel="$config[\'pluralCardLabel\']"/>
        @endif
    </div>
</div>
');

// ============================================================
// EMPTY COLUMN
// ============================================================
file_put_contents('resources/views/vendor/flowforge/livewire/empty-column.blade.php', '
@props(["columnId","pluralCardLabel"])
<div style="text-align:center;padding:40px 16px">
    <div style="font-size:30px;opacity:.2;margin-bottom:8px">📭</div>
    <div style="color:rgba(255,255,255,.2);font-size:11px">No {{ $pluralCardLabel ?? "tasks" }}</div>
</div>
');

// ============================================================
// BOARD PAGE - dark wrapper
// ============================================================
file_put_contents('resources/views/vendor/flowforge/filament/pages/board-page.blade.php', '
<x-filament-panels::page>
<style>
/* Kill white backgrounds from flowforge defaults */
.flowforge-column { background: rgba(15,23,42,.5) !important; }
.flowforge-card   { background: #1e293b !important; }
/* Scrollbar */
.flowforge-column-content::-webkit-scrollbar { width:4px }
.flowforge-column-content::-webkit-scrollbar-thumb { background:rgba(255,255,255,.1);border-radius:2px }
</style>
<div style="width:100%;overflow-x:auto;padding-bottom:20px">
    @include("flowforge::index", ["columns"=>$columns,"config"=>$config])
</div>
</x-filament-panels::page>
');

echo "Done! All card + column views rewritten.\n";
echo "php artisan optimize:clear && php artisan serve --host=0.0.0.0 --port=8000\n";
