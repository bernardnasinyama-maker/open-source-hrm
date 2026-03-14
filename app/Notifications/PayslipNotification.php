<?php
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
}