<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class AppNotice extends Notification
{
    public function __construct(
        public string $title,
        public string $body,
        public ?string $url = null,
        public string $icon = 'bell',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body'  => $this->body,
            'url'   => $this->url,
            'icon'  => $this->icon,
        ];
    }
}