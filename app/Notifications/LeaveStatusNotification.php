<?php
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
}