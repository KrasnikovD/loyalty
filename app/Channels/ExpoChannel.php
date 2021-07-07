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
        $expo->subscribe('user_528491', "ExponentPushToken[v_H1T6GATefgo2uJ9cYe2t]");
        $notification = ['body' => "Hello World!"];
        $expo->notify(['user_528491'], $notification);
    }
}
