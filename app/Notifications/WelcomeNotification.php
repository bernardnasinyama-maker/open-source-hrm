<?php
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
}