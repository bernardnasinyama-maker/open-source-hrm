
<x-filament-panels::page>
<style>
.att-row { display:grid; grid-template-columns:32px 1fr 120px 100px 100px 80px; gap:10px; align-items:center; padding:10px 14px; border-radius:8px; margin-bottom:6px; }
.att-row:hover { background:rgba(255,255,255,.04); }
.att-header { background:rgba(139,92,246,.15); font-size:11px; font-weight:700; color:#a78bfa; text-transform:uppercase; letter-spacing:.06em; }
.att-name { font-size:13px; font-weight:600; color:#f1f5f9; }
.att-dept { font-size:11px; color:rgba(255,255,255,.35); }
.att-code { font-size:10px; color:rgba(139,92,246,.7); font-family:monospace; }
.time-input { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.1); border-radius:6px; padding:4px 8px; color:#f1f5f9; font-size:12px; width:90px; }
.late-badge { background:rgba(245,158,11,.2); color:#fbbf24; padding:2px 8px; border-radius:20px; font-size:10px; font-weight:700; }
.present-check { width:18px; height:18px; accent-color:#8b5cf6; cursor:pointer; }
.stats-bar { display:flex; gap:16px; padding:12px 16px; background:rgba(139,92,246,.1); border-radius:10px; margin-bottom:16px; border:1px solid rgba(139,92,246,.2); }
</style>

{{-- Date selector & stats --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <div style="display:flex;align-items:center;gap:12px">
        <div style="color:#a78bfa;font-size:13px;font-weight:600">Date:</div>
        <input type="date" wire:model.live="date" style="background:rgba(255,255,255,.08);border:1px solid rgba(139,92,246,.3);border-radius:8px;padding:6px 12px;color:#f1f5f9;font-size:13px">
    </div>
    <div style="display:flex;gap:8px">
        <span style="background:rgba(16,185,129,.15);color:#34d399;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600">
            ✅ {{ collect($attendance)->where("present", true)->count() }} Present
        </span>
        <span style="background:rgba(239,68,68,.15);color:#f87171;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600">
            ❌ {{ collect($attendance)->where("present", false)->count() }} Absent
        </span>
        <span style="background:rgba(245,158,11,.15);color:#fbbf24;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600">
            ⚠️ {{ collect($attendance)->where("present", true)->filter(fn($a) => ($a["time_in"] ?? "08:00") > "08:30")->count() }} Late
        </span>
    </div>
</div>

{{-- Table header --}}
<div class="att-row att-header">
    <div>✓</div>
    <div>Employee</div>
    <div>Time In</div>
    <div>Time Out</div>
    <div>Status</div>
    <div>Late?</div>
</div>

{{-- Employee rows --}}
@foreach($attendance as $empId => $data)
@php $isLate = ($data["time_in"] ?? "08:00") > "08:30" && $data["present"]; @endphp
<div class="att-row" style="{{ $data["present"] ? "background:rgba(16,185,129,.05);border:1px solid rgba(16,185,129,.1)" : "border:1px solid rgba(255,255,255,.04)" }}">
    {{-- Checkbox --}}
    <input type="checkbox" class="present-check"
        wire:model.live="attendance.{{ $empId }}.present">

    {{-- Name --}}
    <div>
        <div class="att-name">{{ $data["name"] }}</div>
        <div style="display:flex;gap:6px;margin-top:2px">
            <span class="att-code">{{ $data["code"] }}</span>
            <span class="att-dept">· {{ $data["dept"] }}</span>
        </div>
    </div>

    {{-- Time In --}}
    <input type="time" class="time-input"
        wire:model.live="attendance.{{ $empId }}.time_in"
        {{ !$data["present"] ? "disabled" : "" }}
        style="{{ !$data["present"] ? "opacity:.3" : ($isLate ? "border-color:#f59e0b;color:#fbbf24" : "border-color:rgba(16,185,129,.4);color:#34d399") }}">

    {{-- Time Out --}}
    <input type="time" class="time-input"
        wire:model.live="attendance.{{ $empId }}.time_out"
        {{ !$data["present"] ? "disabled" : "" }}
        style="{{ !$data["present"] ? "opacity:.3" : "" }}">

    {{-- Status --}}
    <div>
        @if($data["present"])
            <span style="background:rgba(16,185,129,.15);color:#34d399;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">Present</span>
        @else
            <span style="background:rgba(239,68,68,.1);color:#f87171;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">Absent</span>
        @endif
    </div>

    {{-- Late --}}
    <div>
        @if($isLate)
            <span class="late-badge">LATE</span>
        @endif
    </div>
</div>
@endforeach

{{-- Bottom save button --}}
<div style="margin-top:16px;text-align:right">
    <button wire:click="saveAttendance"
        style="background:linear-gradient(135deg,#7c3aed,#4f46e5);color:white;border:none;border-radius:10px;padding:10px 28px;font-size:14px;font-weight:700;cursor:pointer;box-shadow:0 4px 15px rgba(124,58,237,.4)">
        💾 Save Attendance
    </button>
</div>
</x-filament-panels::page>
