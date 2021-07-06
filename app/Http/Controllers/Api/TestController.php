<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        $expo = Expo::normalSetup();
        return response()->json([]);
    }
}
