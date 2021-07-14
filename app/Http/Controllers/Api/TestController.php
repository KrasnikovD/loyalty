<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommonActions;
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
       /* $device = Devices::where('id', '=', 2)->first();
        $device->notify(new WelcomeNotification("Hello Title", "Hello Body"));*/
       // $responseList = CommonActions::sendSms(["+38 (071) 340-53-91", "+38 (071) 340-53-92", "+38 (095) 340-53-91","+38 (095) 340-53-92"], "Hello");
       // return response()->json($responseList);
        print_r(CommonActions::sendSms(["+38 (071) 340-53-91","+38 (071) 340-53","+9 18(5)165-645","+111111111111"],"test"));
        die();
    }
}
