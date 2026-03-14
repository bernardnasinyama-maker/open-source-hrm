<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payslip - {{ $employee->name }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Arial,sans-serif;font-size:13px;color:#1a1a1a;background:#f5f5f5}
.page{max-width:800px;margin:20px auto;background:white;box-shadow:0 4px 20px rgba(0,0,0,.1)}
.header{background:linear-gradient(135deg,#0f3460,#1565c0);color:white;padding:24px 32px;display:flex;justify-content:space-between;align-items:flex-start}
.company-name{font-size:22px;font-weight:700;letter-spacing:.05em;margin-bottom:4px}
.company-sub{font-size:10px;opacity:.7;letter-spacing:.08em;text-transform:uppercase;line-height:1.6}
.payslip-title{text-align:right}
.payslip-title h2{font-size:18px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
.payslip-title .period{font-size:11px;opacity:.7;margin-top:4px}
.gold-bar{background:#ffc107;height:4px}
.body{padding:24px 32px}
.emp-section{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;padding-bottom:20px;border-bottom:2px solid #f1f5f9}
.emp-card{background:#f8fafc;border-radius:8px;padding:14px 16px;border-left:4px solid #1565c0}
.emp-card h3{font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#64748b;margin-bottom:10px}
.emp-row{display:flex;justify-content:space-between;margin-bottom:6px}
.emp-lbl{font-size:11px;color:#64748b}
.emp-val{font-size:11px;font-weight:600;color:#0f172a}
.section-title{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#1565c0;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #e2e8f0}
.earnings-section{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}
.e-table{width:100%;border-collapse:collapse}
.e-table th{background:#1565c0;color:white;font-size:10px;font-weight:700;padding:8px 12px;text-align:left}
.e-table td{padding:8px 12px;font-size:12px;border-bottom:1px solid #f1f5f9}
.e-table .total-row td{background:#f8fafc;font-weight:700;border-top:2px solid #e2e8f0}
.net-pay-box{background:linear-gradient(135deg,#0f3460,#1565c0);color:white;border-radius:10px;padding:20px 24px;display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
.net-pay-label{font-size:12px;opacity:.8;text-transform:uppercase;letter-spacing:.08em}
.net-pay-amount{font-size:28px;font-weight:800;letter-spacing:.02em}
.footer{background:#f8fafc;padding:16px 32px;display:flex;justify-content:space-between;border-top:2px solid #e2e8f0;font-size:10px;color:#64748b}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase}
.badge-paid{background:#dcfce7;color:#16a34a}
.badge-pending{background:#fef9c3;color:#ca8a04}
.print-btn{position:fixed;bottom:24px;right:24px;background:#1565c0;color:white;border:none;padding:12px 24px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;box-shadow:0 4px 15px rgba(21,101,192,.4)}
@media print{.print-btn{display:none}body{background:white}.page{box-shadow:none;margin:0}}
</style>
</head>
<body>
<div class="page">
    <div class="header">
        <div>
            <div class="company-name">CRBC UGANDA LTD</div>
            <div class="company-sub">
                Kayunga-Bbaale-Galiraya Road (87KM)<br>
                Contract No: MOWT/WORKS/2024-25/00115<br>
                P.O Box 1234, Kampala, Uganda
            </div>
        </div>
        <div class="payslip-title">
            <h2>Pay Slip</h2>
            <div class="period">Period: {{ $period }}</div>
            <div class="period">Pay Date: {{ \Carbon\Carbon::parse($payDate)->format("d M Y") }}</div>
            <div style="margin-top:8px">
                <span class="badge {{ $status === "completed" ? "badge-paid" : "badge-pending" }}">
                    {{ strtoupper($status) }}
                </span>
            </div>
        </div>
    </div>
    <div class="gold-bar"></div>

    <div class="body">
        {{-- Employee Info --}}
        <div class="emp-section">
            <div class="emp-card">
                <h3>Employee Details</h3>
                <div class="emp-row"><span class="emp-lbl">Name</span><span class="emp-val">{{ $employee->name }}</span></div>
                <div class="emp-row"><span class="emp-lbl">Employee Code</span><span class="emp-val">{{ $employee->employee_code }}</span></div>
                <div class="emp-row"><span class="emp-lbl">Department</span><span class="emp-val">{{ $employee->department?->name ?? "N/A" }}</span></div>
                <div class="emp-row"><span class="emp-lbl">Position</span><span class="emp-val">{{ $employee->position?->name ?? "N/A" }}</span></div>
            </div>
            <div class="emp-card">
                <h3>Employment Details</h3>
                <div class="emp-row"><span class="emp-lbl">Employment Type</span><span class="emp-val">{{ $employee->employment_type }}</span></div>
                <div class="emp-row"><span class="emp-lbl">Hire Date</span><span class="emp-val">{{ \Carbon\Carbon::parse($employee->hire_date)->format("d M Y") }}</span></div>
                <div class="emp-row"><span class="emp-lbl">NSSF No.</span><span class="emp-val">{{ $employee->nssf_number ?? "N/A" }}</span></div>
                <div class="emp-row"><span class="emp-lbl">Status</span><span class="emp-val">{{ $employee->is_active ? "Active" : "Inactive" }}</span></div>
            </div>
        </div>

        {{-- Earnings & Deductions --}}
        <div class="earnings-section">
            <div>
                <div class="section-title">Earnings</div>
                <table class="e-table">
                    <tr><th>Description</th><th style="text-align:right">Amount (UGX)</th></tr>
                    <tr><td>Basic / Gross Pay</td><td style="text-align:right">{{ number_format($gross) }}</td></tr>
                    @foreach($allowancesRaw as $type => $amount)
                    <tr><td>{{ $type }}</td><td style="text-align:right">{{ number_format($amount) }}</td></tr>
                    @endforeach
                    @foreach($bonusesRaw as $type => $amount)
                    <tr><td>{{ $type }} (Bonus)</td><td style="text-align:right">{{ number_format($amount) }}</td></tr>
                    @endforeach
                    <tr class="total-row"><td>Total Earnings</td><td style="text-align:right">{{ number_format($gross + $totalAllowances) }}</td></tr>
                </table>
            </div>
            <div>
                <div class="section-title">Deductions</div>
                <table class="e-table">
                    <tr><th>Description</th><th style="text-align:right">Amount (UGX)</th></tr>
                    <tr><td>PAYE</td><td style="text-align:right">{{ number_format($paye) }}</td></tr>
                    <tr><td>NSSF (Employee 5%)</td><td style="text-align:right">{{ number_format($nssfEmployee) }}</td></tr>
                    <tr><td>LST</td><td style="text-align:right">{{ number_format($lst) }}</td></tr>
                    @foreach($otherDeductions as $type => $amount)
                    <tr><td>{{ $type }}</td><td style="text-align:right">{{ number_format($amount) }}</td></tr>
                    @endforeach
                    <tr class="total-row"><td>Total Deductions</td><td style="text-align:right">{{ number_format($totalDeductions) }}</td></tr>
                </table>
                <div style="margin-top:10px;padding:10px;background:#f0fdf4;border-radius:6px;font-size:11px;color:#166534">
                    NSSF Employer Contribution (10%): UGX {{ number_format($nssfEmployer) }}<br>
                    <span style="opacity:.7">(Not deducted from employee — paid by company)</span>
                </div>
            </div>
        </div>

        {{-- Net Pay --}}
        <div class="net-pay-box">
            <div>
                <div class="net-pay-label">Net Pay (Take Home)</div>
                @if($notes)
                <div style="font-size:11px;opacity:.6;margin-top:4px">{{ $notes }}</div>
                @endif
            </div>
            <div class="net-pay-amount">UGX {{ number_format($netPay) }}</div>
        </div>

        {{-- Signatures --}}
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-top:20px">
            @foreach(["Prepared By","Approved By","Employee Signature"] as $sig)
            <div style="text-align:center">
                <div style="border-top:1px solid #cbd5e1;padding-top:8px;font-size:11px;color:#64748b">{{ $sig }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="footer">
        <div>Generated: {{ now()->format("d M Y, H:i") }} EAT</div>
        <div>CRBC Uganda Ltd — Confidential</div>
        <div>{{ $employee->employee_code }} / {{ $period }}</div>
    </div>
</div>

<button class="print-btn" onclick="window.print()">🖨️ Print Payslip</button>
</body>
</html>