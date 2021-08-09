<?php

namespace App\Notifications;

use App\Channels\ExpoChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    private $title;
    private $body;
    private $data;

    public function __construct($title, $body, $data = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = @$data;
    }

    public function via($notifiable)
    {
        return [ExpoChannel::class];
    }

    public function toExpoPush($notifiable)
    {
        $message = new \stdClass();
        $message->expo_token = $notifiable->expo_token;
        $message->title = $this->title;
        $message->body = $this->body;
        $message->data = $this->data;
        return $message;
    }
}
