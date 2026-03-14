<?php
require __DIR__."/vendor/autoload.php";
$app = require __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Notifications folder
if (!is_dir('app/Notifications')) mkdir('app/Notifications', 0755, true);
if (!is_dir('public/pwa')) mkdir('public/pwa', 0755, true);

// Write LeaveStatusNotification
file_put_contents('app/Notifications/LeaveStatusNotification.php', '<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Leave;

class LeaveStatusNotification extends Notification {
    use Queueable;
    public function __construct(public Leave $leave, public string $status) {}
    public function via($n): array { return ["mail","database"]; }
    public function toMail($n): MailMessage {
        $status = ucfirst($this->status);
        $type   = ucwords(str_replace("_"," ",$this->leave->leave_type));
        $start  = \Carbon\Carbon::parse($this->leave->start_date)->format("d M Y");
        $end    = \Carbon\Carbon::parse($this->leave->end_date)->format("d M Y");
        return (new MailMessage)
            ->subject("Leave Request {$status} — CRBC Uganda HRM")
            ->greeting("Dear {$n->first_name},")
            ->line("Your **{$type}** leave request has been **{$status}**.")
            ->line("**Period:** {$start} to {$end}")
            ->salutation("CRBC Uganda HR Team");
    }
    public function toArray($n): array {
        return ["type"=>"leave_status","leave_id"=>$this->leave->id,"status"=>$this->status,"message"=>"Your leave request has been {$this->status}."];
    }
}');
echo "LeaveStatusNotification written\n";

// Write PayslipNotification
file_put_contents('app/Notifications/PayslipNotification.php', '<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Payroll;

class PayslipNotification extends Notification {
    use Queueable;
    public function __construct(public Payroll $payroll) {}
    public function via($n): array { return ["mail","database"]; }
    public function toMail($n): MailMessage {
        $period = \Carbon\Carbon::parse($this->payroll->pay_date)->format("F Y");
        $net    = number_format($this->payroll->net_pay);
        $gross  = number_format($this->payroll->gross_pay);
        return (new MailMessage)
            ->subject("Payslip for {$period} — CRBC Uganda HRM")
            ->greeting("Dear {$n->first_name},")
            ->line("Your payslip for **{$period}** is ready.")
            ->line("**Gross Pay:** UGX {$gross}")
            ->line("**Net Pay:** UGX {$net}")
            ->action("View Payslip", url("/payslip/{$n->id}"))
            ->salutation("CRBC Uganda HR Team");
    }
    public function toArray($n): array {
        return ["type"=>"payslip","payroll_id"=>$this->payroll->id,"message"=>"Your payslip is ready."];
    }
}');
echo "PayslipNotification written\n";

// Write WelcomeNotification
file_put_contents('app/Notifications/WelcomeNotification.php', '<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WelcomeNotification extends Notification {
    use Queueable;
    public function __construct(public string $password) {}
    public function via($n): array { return ["mail"]; }
    public function toMail($n): MailMessage {
        return (new MailMessage)
            ->subject("Welcome to CRBC Uganda HRM")
            ->greeting("Welcome, {$n->first_name}!")
            ->line("Your HRM account has been created.")
            ->line("**Email:** {$n->email}")
            ->line("**Password:** {$this->password}")
            ->action("Login Now", url("/admin"))
            ->salutation("CRBC Uganda HR Team");
    }
}');
echo "WelcomeNotification written\n";

// Write PWA manifest
file_put_contents('public/manifest.json', json_encode([
    "name"             => "CRBC Uganda HRM",
    "short_name"       => "CRBC HRM",
    "description"      => "HR Management — Kayunga-Bbaale-Galiraya Road",
    "start_url"        => "/admin",
    "display"          => "standalone",
    "background_color" => "#0f172a",
    "theme_color"      => "#1565c0",
    "icons"            => [
        ["src"=>"/pwa/icon-192.png","sizes"=>"192x192","type"=>"image/png","purpose"=>"any maskable"],
        ["src"=>"/pwa/icon-512.png","sizes"=>"512x512","type"=>"image/png","purpose"=>"any maskable"],
    ]
], JSON_PRETTY_PRINT));
echo "manifest.json written\n";

// Write service worker
file_put_contents('public/sw.js', '
const CACHE="crbc-hrm-v1";
self.addEventListener("install",e=>{e.waitUntil(caches.open(CACHE).then(c=>c.add("/offline.html")));self.skipWaiting();});
self.addEventListener("activate",e=>{e.waitUntil(caches.keys().then(ks=>Promise.all(ks.filter(k=>k!==CACHE).map(k=>caches.delete(k)))));self.clients.claim();});
self.addEventListener("fetch",e=>{if(e.request.method!=="GET")return;e.respondWith(fetch(e.request).then(r=>{if(r.ok){const c=r.clone();caches.open(CACHE).then(ca=>ca.put(e.request,c));}return r;}).catch(()=>caches.match(e.request).then(c=>c||caches.match("/offline.html"))));});
');
echo "sw.js written\n";

// Write offline page
file_put_contents('public/offline.html', "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>CRBC HRM Offline</title><style>body{font-family:Arial,sans-serif;background:#0f172a;color:white;display:flex;align-items:center;justify-content:center;min-height:100vh;text-align:center}.card{background:rgba(255,255,255,.05);border-radius:16px;padding:40px;max-width:400px}.icon{font-size:64px;margin-bottom:16px}h1{color:#a5b4fc;margin-bottom:12px}p{color:rgba(255,255,255,.6);margin-bottom:20px}.btn{background:#6366f1;color:white;padding:10px 24px;border-radius:8px;text-decoration:none;font-weight:600}.proj{font-size:11px;color:rgba(255,193,7,.5);margin-top:20px;text-transform:uppercase;letter-spacing:.08em}</style></head><body><div class='card'><div class='icon'>📡</div><h1>You're Offline</h1><p>CRBC HRM needs internet. Check your connection.</p><a href='/admin' class='btn'>Try Again</a><div class='proj'>CRBC Uganda · Kayunga-Bbaale-Galiraya Road</div></div></body></html>");
echo "offline.html written\n";

// Add PWA to AppServiceProvider
$f = "app/Providers/AppServiceProvider.php";
$c = file_get_contents($f);
if (strpos($c, "manifest.json") === false) {
    $hook = '
        \Filament\Facades\Filament::serving(function () {
            \Filament\Support\Facades\FilamentView::renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn() => new \Illuminate\Support\HtmlString(\'<link rel="manifest" href="/manifest.json"><meta name="theme-color" content="#1565c0"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-title" content="CRBC HRM"><script>if("serviceWorker"in navigator){navigator.serviceWorker.register("/sw.js").then(()=>console.log("CRBC HRM PWA ready")).catch(e=>console.log(e))}<\/script>\')
            );
        });';
    $c = str_replace(
        "public function boot(): void\n    {",
        "public function boot(): void\n    {" . $hook,
        $c
    );
    file_put_contents($f, $c);
    echo "PWA hooks added to AppServiceProvider\n";
}

// Run notifications migration
\Illuminate\Support\Facades\Artisan::call("notifications:table");
\Illuminate\Support\Facades\Artisan::call("migrate", ["--force"=>true]);
echo "Notifications table ready\n";

echo "\n=== DONE! ===\n";
echo "Notifications: app/Notifications/ (3 classes)\n";
echo "PWA: public/manifest.json + public/sw.js + public/offline.html\n";
echo "\n=== EMAIL SETUP ===\n";
echo "Edit .env and set:\n";
echo "MAIL_MAILER=smtp\n";
echo "MAIL_HOST=smtp.gmail.com\n";
echo "MAIL_PORT=587\n";
echo "MAIL_USERNAME=einsteinbernard3000@gmail.com\n";
echo "MAIL_PASSWORD=your-16-char-app-password\n";
echo "MAIL_ENCRYPTION=tls\n";
echo "MAIL_FROM_ADDRESS=einsteinbernard3000@gmail.com\n";
echo "MAIL_FROM_NAME=\"CRBC Uganda HRM\"\n";
echo "\nGet App Password: https://myaccount.google.com/apppasswords\n";
