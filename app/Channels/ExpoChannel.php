<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;

class ExpoChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toExpoPush($notifiable);
        return true;
    }
}
