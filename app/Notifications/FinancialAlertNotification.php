<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class FinancialAlertNotification extends Notification
{
    public function __construct(
        public readonly string $alertType,
        public readonly string $title,
        public readonly string $message,
        public readonly ?string $url = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'alert_type' => $this->alertType,
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }
}
