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