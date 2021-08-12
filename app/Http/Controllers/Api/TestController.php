<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cards;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    /**
     * @api {post} /api/test Test
     * @apiName Test
     * @apiGroup TestApi
     */
    public function test()
    {
        //print_r(Cards::whereIn('number', ['73146223'])->first());
        print_r(DB::table('cards')->whereRaw("number='73146223'")->exists());
        return response()->json([]);
    }
}
