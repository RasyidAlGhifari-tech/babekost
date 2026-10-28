<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\AppNotice;
use Illuminate\Support\Facades\Notification;

class Notifier
{
    public static function toAdmins(string $title, string $body, ?string $url = null, string $icon = 'bell'): void
    {
        $admins = User::where('role', 'admin')->where('is_active', true)->get();
        Notification::send($admins, new AppNotice($title, $body, $url, $icon));
    }

    public static function toUser(?User $user, string $title, string $body, ?string $url = null, string $icon = 'bell'): void
    {
        if ($user) {
            $user->notify(new AppNotice($title, $body, $url, $icon));
        }
    }
}