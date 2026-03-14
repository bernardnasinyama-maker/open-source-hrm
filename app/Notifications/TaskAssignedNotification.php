<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Task;

class TaskAssignedNotification extends Notification {
    use Queueable;
    public function __construct(public Task $task) {}
    public function via($n): array { return ["mail","database"]; }
    public function toMail($n): MailMessage {
        $due = $this->task->due_date ? $this->task->due_date->format("d M Y") : "No due date";
        return (new MailMessage)
            ->subject("New Task Assigned — CRBC Uganda HRM")
            ->greeting("Dear {$n->first_name},")
            ->line("A new task has been assigned to you.")
            ->line("**Task:** {$this->task->title}")
            ->line("**Priority:** " . ucfirst($this->task->priority ?? "medium"))
            ->line("**Due:** {$due}")
            ->action("View Task Board", url("/admin/task-board"))
            ->salutation("CRBC Uganda — Kayunga-Bbaale-Galiraya Road");
    }
    public function toArray($n): array {
        return ["type"=>"task_assigned","task_id"=>$this->task->id,"message"=>"New task: {$this->task->title}"];
    }
}