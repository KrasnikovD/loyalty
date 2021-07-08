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
        $expo->subscribe($message->expo_token, $message->expo_token);
        $notification = ['title' => $message->title, 'body' => $message->body, 'sound' => 'default'];
        $expo->notify([$message->expo_token], $notification);
    }
}
