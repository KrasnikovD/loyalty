<?php

namespace App\Http\Controllers\Api;

use App\Exports\CardExport;
use App\Http\Controllers\Controller;
use App\Models\Cards;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class TestController extends Controller
{
    /**
     * @api {post} /api/test Test
     * @apiName Test
     * @apiGroup TestApi
     */
    public function test()
    {
        $fileName = "reports/" . date('Y-m-d_H_i_s') . '_cards.xlsx';
        Storage::disk('local')->put($fileName, '');
        $export = new CardExport();
        Excel::store($export, Storage::path($fileName));
        return response()->json([$fileName]);
    }
}
