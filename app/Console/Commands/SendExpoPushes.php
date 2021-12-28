<?php

namespace App\Console\Commands;

use App\Models\Devices;
use App\Models\PushTokens;
use App\Notifications\WelcomeNotification;
use Illuminate\Console\Command;

class SendExpoPushes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send_expo_pushes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        foreach (PushTokens::where('sent', '=', 0)->get() as $item) {
            $item->sent = 1;
            $item->save();
            foreach (json_decode($item->tokens) as $token) {
                $device = Devices::where('expo_token', '=', $token)->first();
                $device->notify(new WelcomeNotification($item->title, $item->body,
                    json_encode(['sound' => 'default', 'ttl' => 30])));
            }
        }

        return 0;
    }
}
