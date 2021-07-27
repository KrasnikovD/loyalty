<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class TestController extends Controller
{
    /**
     * @api {post} /api/test Test
     * @apiName Test
     * @apiGroup TestApi
     */
    public function test()
    {
        return response()->json([]);
    }
}
