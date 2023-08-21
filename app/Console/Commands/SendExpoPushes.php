<?php

namespace App\Console\Commands;

use App\Models\Devices;
use App\Models\PushTokens;
use App\Notifications\WelcomeNotification;
use Illuminate\Console\Command;
use ExponentPhpSDK\Expo;

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
        $storage = __DIR__ . "/../../../vendor/alymosul/exponent-server-sdk-php/storage/tokens.json";
        $logName = '/' . date('Y-m-d') . '_' . 'pushes.log';
        foreach (PushTokens::where('sent', '=', 0)->get() as $item) {
            $item->sent = 1;
            $item->save();
            if (file_exists($storage))
                unlink($storage);
            foreach (json_decode($item->tokens) as $token) {
                $device = Devices::where('expo_token', '=', $token)->first();
                try {
                    $device->notify(new WelcomeNotification($item->title, $item->body,
                        json_encode(['sound' => 'default', 'ttl' => 30])));
                } catch (\Exception $exception) {
                    print $exception->getMessage() . "\n";
                    file_put_contents(getcwd() . $logName, $exception->getMessage(), FILE_APPEND);
                    file_put_contents(getcwd() . $logName, "\n*****************\n", FILE_APPEND);
                }
            }
        }

    /*    foreach (PushTokens::where('sent', '=', 0)->get() as $item) {
            $storage = __DIR__ . "/../../../vendor/alymosul/exponent-server-sdk-php/storage/tokens.json";
            if (file_exists($storage))
                unlink($storage);
            $item->sent = 1;
            $item->status = "OK";
            try {
                $expo = Expo::normalSetup();
                $channelName = 'channel_' . time();
                print $channelName . "\n";
                foreach (json_decode($item->tokens) as $token) {
                    $expo->subscribe($channelName, $token);
                }
                $expo->notify([$channelName], ['title' => $item->title, 'body' => $item->body, 'sound' => 'default', 'ttl' => 3600]);
            } catch (\Exception $exception) {
                print $exception->getMessage() . "\n";
                $item->status = $exception->getMessage();
            }
            $item->save();
            usleep(1000000);
        }*/


        return 0;
    }
}
