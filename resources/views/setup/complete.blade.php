<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Setup Complete!</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:"Segoe UI",sans-serif;background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.card{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(16,185,129,.3);border-radius:20px;width:100%;max-width:500px;padding:40px;text-align:center}
.icon{font-size:64px;margin-bottom:20px}
h1{color:#34d399;font-size:28px;font-weight:800;margin-bottom:10px}
p{color:rgba(255,255,255,.6);font-size:14px;margin-bottom:24px;line-height:1.6}
.info-box{background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);border-radius:12px;padding:20px;margin-bottom:24px;text-align:left}
.info-row{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.05);font-size:13px}
.info-row:last-child{border:none}
.info-label{color:rgba(255,255,255,.4)}
.info-value{color:#f1f5f9;font-weight:600}
.btn{display:block;background:linear-gradient(135deg,#7c3aed,#4f46e5);color:white;text-decoration:none;border-radius:10px;padding:14px;font-size:15px;font-weight:700;margin-bottom:10px;box-shadow:0 4px 15px rgba(124,58,237,.4)}
.btn-hr{display:block;background:rgba(255,255,255,.08);color:#c4b5fd;text-decoration:none;border-radius:10px;padding:12px;font-size:14px;font-weight:600;border:1px solid rgba(139,92,246,.3)}
</style>
</head>
<body>
<div class="card">
    <div class="icon">🎉</div>
    <h1>Setup Complete!</h1>
    <p>Your HRM system is ready. Save your login credentials below before proceeding.</p>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">System</span>
            <span class="info-value">{{ config("app_branding.name") }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Admin URL</span>
            <span class="info-value">/admin</span>
        </div>
        <div class="info-row">
            <span class="info-label">HR Portal</span>
            <span class="info-value">/hr</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email</span>
            <span class="info-value">{{ session("email") }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Password</span>
            <span class="info-value">{{ session("password") }}</span>
        </div>
    </div>

    <a href="/admin" class="btn">🔐 Go to Admin Panel</a>
    <a href="/hr" class="btn-hr">👥 Go to HR Portal</a>

    <p style="margin-top:20px;font-size:11px;color:rgba(255,255,255,.2)">
        Powered by SiteHRM — Built for African Construction Companies
    </p>
</div>
</body>
</html>