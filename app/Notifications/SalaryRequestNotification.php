<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SalaryRequestNotification extends Notification
{
    use Queueable;

    public string $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    // Hanya via database, bisa tambahkan 'mail' kalau perlu
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message
        ];
    }
}
