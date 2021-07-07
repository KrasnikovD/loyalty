<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use \ExponentPhpSDK\Expo;

class ExpoChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toExpoPush($notifiable);
        $expo = Expo::normalSetup();
        $expo->subscribe('user_528491', $message->expo_token);
        $notification = ['title' => $message->title, 'body' => $message->body, 'sound' => 'default'];
        $expo->notify(['user_528491'], $notification);
    }
}
