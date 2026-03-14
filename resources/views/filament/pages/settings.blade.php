{{-- resources/views/filament/pages/settings.blade.php --}}
@php
    $isSuper = auth()->user()?->hasRole('super_admin');
@endphp
<x-filament-panels::page>
<div class="space-y-6">

    {{-- Header --}}
    <div style="background:linear-gradient(135deg,#1e293b,#0f172a);border-radius:12px;padding:24px;border:1px solid rgba(99,102,241,.3)">
        <h2 style="color:#a5b4fc;font-size:20px;font-weight:700;margin:0 0 4px">⚙️ System Settings</h2>
        <p style="color:rgba(255,255,255,.5);font-size:13px;margin:0">Super Admin only — CRBC Uganda HRM Configuration</p>
    </div>

    {{-- Email / SMTP Settings --}}
    <div style="background:#1e293b;border-radius:12px;padding:24px;border:1px solid rgba(255,255,255,.08)">
        <h3 style="color:#f1f5f9;font-size:16px;font-weight:600;margin:0 0 16px">📧 Email / SMTP Settings</h3>
        <p style="color:rgba(255,255,255,.4);font-size:12px;margin:0 0 20px">Configure Gmail SMTP so leave approvals and payslips are emailed automatically.</p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
            <div>
                <label style="color:#94a3b8;font-size:12px;display:block;margin-bottom:6px">Mail Driver</label>
                <select wire:model="mail_mailer" style="width:100%;background:#0f172a;color:white;border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:8px 12px;font-size:14px">
                    <option value="log">Log (Testing — no real email)</option>
                    <option value="smtp">SMTP (Gmail — Live)</option>
                </select>
            </div>
            <div>
                <label style="color:#94a3b8;font-size:12px;display:block;margin-bottom:6px">Mail Host</label>
                <input wire:model="mail_host" type="text" placeholder="smtp.gmail.com"
                    style="width:100%;background:#0f172a;color:white;border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:8px 12px;font-size:14px;box-sizing:border-box">
            </div>
            <div>
                <label style="color:#94a3b8;font-size:12px;display:block;margin-bottom:6px">Mail Port</label>
                <input wire:model="mail_port" type="text" placeholder="587"
                    style="width:100%;background:#0f172a;color:white;border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:8px 12px;font-size:14px;box-sizing:border-box">
            </div>
            <div>
                <label style="color:#94a3b8;font-size:12px;display:block;margin-bottom:6px">Gmail Address</label>
                <input wire:model="mail_username" type="email" placeholder="einsteinbernard3000@gmail.com"
                    style="width:100%;background:#0f172a;color:white;border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:8px 12px;font-size:14px;box-sizing:border-box">
            </div>
            <div>
                <label style="color:#94a3b8;font-size:12px;display:block;margin-bottom:6px">App Password <span style="color:#f59e0b;font-size:11px">(16 chars from Google)</span></label>
                <input wire:model="mail_password" type="password" placeholder="xxxx xxxx xxxx xxxx"
                    style="width:100%;background:#0f172a;color:white;border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:8px 12px;font-size:14px;box-sizing:border-box">
            </div>
            <div>
                <label style="color:#94a3b8;font-size:12px;display:block;margin-bottom:6px">From Name</label>
                <input wire:model="mail_from_name" type="text" placeholder="CRBC Uganda HRM"
                    style="width:100%;background:#0f172a;color:white;border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:8px 12px;font-size:14px;box-sizing:border-box">
            </div>
        </div>

        <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);border-radius:8px;padding:12px;margin-bottom:16px">
            <p style="color:#f59e0b;font-size:12px;margin:0">
                💡 <strong>Get App Password:</strong> Go to <strong>myaccount.google.com/apppasswords</strong> →
                Select "Mail" → "Windows Computer" → Copy the 16-character password
            </p>
        </div>

        <button wire:click="saveEmailSettings"
            style="background:linear-gradient(135deg,#6366f1,#4f46e5);color:white;border:none;padding:10px 24px;border-radius:8px;font-weight:600;font-size:14px;cursor:pointer">
            💾 Save Email Settings
        </button>
    </div>

    {{-- System Tools --}}
    <div style="background:#1e293b;border-radius:12px;padding:24px;border:1px solid rgba(255,255,255,.08)">
        <h3 style="color:#f1f5f9;font-size:16px;font-weight:600;margin:0 0 16px">🛠️ System Tools</h3>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            <button wire:click="clearCache"
                style="background:#0f172a;color:#a5b4fc;border:1px solid rgba(99,102,241,.3);padding:10px 20px;border-radius:8px;font-size:13px;cursor:pointer;font-weight:500">
                🗑️ Clear Cache
            </button>
            <button wire:click="clearLogs"
                style="background:#0f172a;color:#f87171;border:1px solid rgba(248,113,113,.3);padding:10px 20px;border-radius:8px;font-size:13px;cursor:pointer;font-weight:500">
                📋 Clear Log File
            </button>
            <a href="/admin/audit-trail"
                style="background:#0f172a;color:#34d399;border:1px solid rgba(52,211,153,.3);padding:10px 20px;border-radius:8px;font-size:13px;text-decoration:none;font-weight:500;display:inline-block">
                🔍 View Audit Trail
            </a>
        </div>
    </div>

    {{-- Remote Access Guide --}}
    <div style="background:#1e293b;border-radius:12px;padding:24px;border:1px solid rgba(255,255,255,.08)">
        <h3 style="color:#f1f5f9;font-size:16px;font-weight:600;margin:0 0 16px">🌐 Remote Access</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div style="background:#0f172a;border-radius:8px;padding:16px;border:1px solid rgba(255,255,255,.06)">
                <div style="font-size:20px;margin-bottom:8px">🏢</div>
                <div style="color:#f1f5f9;font-weight:600;font-size:14px;margin-bottom:6px">Office LAN</div>
                <div style="color:rgba(255,255,255,.5);font-size:12px;margin-bottom:10px">Everyone on same WiFi</div>
                <code style="background:#1e293b;color:#a5b4fc;padding:4px 8px;border-radius:4px;font-size:11px">
                    php artisan serve --host=0.0.0.0 --port=8000
                </code>
            </div>
            <div style="background:#0f172a;border-radius:8px;padding:16px;border:1px solid rgba(255,255,255,.06)">
                <div style="font-size:20px;margin-bottom:8px">☁️</div>
                <div style="color:#f1f5f9;font-weight:600;font-size:14px;margin-bottom:6px">Internet (Cloudflare)</div>
                <div style="color:rgba(255,255,255,.5);font-size:12px;margin-bottom:10px">Free, works anywhere</div>
                <code style="background:#1e293b;color:#a5b4fc;padding:4px 8px;border-radius:4px;font-size:11px">
                    ./cloudflared tunnel --url http://localhost:8000
                </code>
            </div>
        </div>
    </div>

    {{-- System Info --}}
    <div style="background:#1e293b;border-radius:12px;padding:24px;border:1px solid rgba(255,255,255,.08)">
        <h3 style="color:#f1f5f9;font-size:16px;font-weight:600;margin:0 0 16px">ℹ️ System Information</h3>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
            @foreach([
                ['Laravel', app()->version()],
                ['PHP', PHP_VERSION],
                ['Filament', \Filament\Facades\Filament::getCurrentPanel()?->getId() ?? '4.x'],
                ['Environment', app()->environment()],
                ['Database', 'SQLite'],
                ['Timezone', config('app.timezone')],
            ] as [$label, $val])
            <div style="background:#0f172a;border-radius:8px;padding:12px;border:1px solid rgba(255,255,255,.06)">
                <div style="color:rgba(255,255,255,.4);font-size:11px;margin-bottom:4px">{{ $label }}</div>
                <div style="color:#a5b4fc;font-size:13px;font-weight:600">{{ $val }}</div>
            </div>
            @endforeach
        </div>
        <div style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(255,255,255,.06);color:rgba(255,255,255,.3);font-size:11px;text-align:center">
            CRBC Uganda HRM · Kayunga-Bbaale-Galiraya Road (87KM) · Designed by Eng. Bernard Nasinyama
        </div>
    </div>

</div>
</x-filament-panels::page>
