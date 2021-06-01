<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BillPrograms;
use App\Models\Bills;
use App\Models\Sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OutletController extends Controller
{
    /**
     * @api {post} /api/outlets/sales/create Create Sale
     * @apiName CreateSale
     * @apiGroup OutletSales
     *
     * @apiParam {integer} outlet_id
     * @apiParam {integer} user_id
     * @apiParam {integer} bill_id
     * @apiParam {integer} amount
     */

    public function edit_sale(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $sale = null;
        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validator = Validator::make($validatorData,
            [
                'amount' => (!$id ? 'required|' : '') . 'integer',
                'outlet_id' => (!$id ? 'required|' : '') . 'exists:outlets,id',
                'bill_id' => (!$id ? 'required|' : '') . 'exists:bills,id',
                'user_id' => (!$id ? 'required|' : '') . 'exists:users,id',
                'id' => 'exists:sales,id',
            ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $sale = $id ? Sales::where('id', '=', $id)->first() : new Sales;
            if(isset($request->user_id)) $sale->user_id = $request->user_id;
            if(isset($request->outlet_id)) $sale->outlet_id = $request->outlet_id;
            if(isset($request->bill_id)) $sale->card_id = Bills::where('id', '=', $request->bill_id)->first()->card_id;
            if(isset($request->bill_id)) $sale->bill_id = $request->bill_id;
            if(isset($request->amount)) $sale->amount = $request->amount;
            $sale->dt = date('Y-m-d H:i:s');
            //$sale->outlet_name = Outlet::where('id', '=', $request->outlet_id)->first()->name;
            $sale->save();
            $program = null;
            foreach (BillPrograms::where('bill_id', '=', $request->bill_id)->get() as $row) {
                if ($request->amount >= $row->from && $request->amount <= $row->to) {
                    $program = $row;
                    break;
                }
            }
            if ($program) {
                $bill = Bills::where('id', '=', $program->bill_id)->first();
                $bill->value = intval($bill->value) + $program->percent * 0.01 * $request->amount;
                $bill->save();
            }
        }
        return response()->json(array('errors' => $errors, 'data' => $sale), $httpStatus);
    }
}
