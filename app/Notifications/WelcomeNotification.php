<?php

namespace App\Notifications;

use App\Channels\ExpoChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    private $title;
    private $body;
    private $data;
    private $ttl;

    public function __construct($title, $body, $data = null, $ttl = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = @$data;
        $this->ttl = $ttl ?: 3600;
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
        $message->ttl = $this->ttl;
        return $message;
    }
}
