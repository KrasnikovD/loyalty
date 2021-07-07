<?php

namespace App\Notifications;

use App\Channels\ExpoChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    public function __construct()
    {}

    public function via($notifiable)
    {
        return [ExpoChannel::class];
    }

    public function toExpoPush($notifiable)
    {
        $message = new \stdClass();
        $message->expo_token = $notifiable->expo_token;
        $message->title = "title";
        $message->body = "Hello World!";
        return $message;
    }
}
