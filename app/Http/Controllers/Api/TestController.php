<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Devices;
use App\Notifications\WelcomeNotification;
use ExponentPhpSDK\Expo;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * @api {post} /api/test Test
     * @apiName Test
     * @apiGroup TestApi
     */
    public function test()
    {
       /* $expo = Expo::normalSetup();
        $expo->subscribe('news', 'ExponentPushToken[HcbduaCnrfgrk6AD5aP7Qv]');
        $notification = ['title' => 'Title','sound' => 'default', 'body' => 'Hello World11111'];

        $expo->notify(['news'], $notification);*/
        $device = Devices::where('id', '=', 2)->first();
        $device->notify(new WelcomeNotification());
        return response()->json([]);
    }
}
