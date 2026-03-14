
@php
$icons = [
    "Correspondence" => ["icon"=>"📬","color"=>"#6366f1","bg"=>"rgba(99,102,241,.15)","label"=>"Correspondence"],
    "SiteExpense"    => ["icon"=>"💰","color"=>"#f59e0b","bg"=>"rgba(245,158,11,.15)","label"=>"Expense"],
    "Task"           => ["icon"=>"📋","color"=>"#10b981","bg"=>"rgba(16,185,129,.15)","label"=>"Task"],
];
$pColors = ["critical"=>"#ef4444","high"=>"#f97316","medium"=>"#f59e0b","low"=>"#10b981"];
$info = $icons[$type] ?? $icons["Task"];
$pc = $pColors[$priority] ?? $pColors["medium"];
@endphp
<div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
    <span style="background:{{$info['bg']}};color:{{$info['color']}};padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">
        {{$info['icon']}} {{$info['label']}}
    </span>
    <span style="background:rgba(0,0,0,.1);color:{{$pc}};padding:2px 6px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase">
        {{$priority}}
    </span>
</div>
