<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StatController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.token');
    }

    /**
     * @api {get} /api/statistic/sales Average Check
     * @apiName AverageCheck
     * @apiGroup AdminStat
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} user_id
     * @apiParam {integer} outlet_id
     * @apiParam {string} [date_from]
     * @apiParam {string} [date_to]
     */

    public function sales(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'outlet_id' => 'nullable|exists:outlets,id',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $q = Sales::select(DB::raw('cast(sales.created_at as date) as date, sum(sales.amount) as total_amount, count(*) as count, sum(bonus_history.added) as total_added, sum(bonus_history.debited) as total_debited'))
                ->leftJoin('bonus_history', 'bonus_history.sale_id', '=', 'sales.id')
                ->groupBy(DB::raw('cast(sales.created_at as date)'));
            if ($request->user_id)
                $q->where('user_id', $request->user_id);
            if ($request->outlet_id)
                $q->where('outlet_id', $request->outlet_id);
            if ($request->date_from && $request->date_to) {
                $from = date("Y-m-d", strtotime($request->date_from));
                $to = date("Y-m-d", strtotime($request->date_to));
                $q->where(DB::raw('cast(sales.created_at as date)'), '>=', $from);
                $q->where(DB::raw('cast(sales.created_at as date)'), '<=', $to);
            }
            $data = $q->get();
            foreach ($data as &$item) {
                $item->average_amount = $item->total_amount / $item->count;
                $item->total_debited = intval($item->total_debited);
            }
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }
}
