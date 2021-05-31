<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BillPrograms;
use App\Models\Bills;
use App\Models\CommonActions;
use App\Models\Sales;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('client.token',
            ['except' => [
                'login'
            ]]);
    }

    /**
     * @api {post} /api/clients/sms Send Auth Sms
     * @apiName SendAuthSms
     * @apiGroup ClientAuth
     *
     * @apiParam {string} phone
     */

    public function send_auth_sms(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $validatorData = $request->all();
        $validator = Validator::make($validatorData, ['phone' => 'required']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $phone = str_replace(array("(", ")", " ", "-"), "", $request->phone);
            $user = Users::where([['type', '=', 1], ['phone', '=', $phone]])->first();
            if (empty($user)) {
                $errors['user'] = 'User not found';
                $httpStatus = 400;
            } else {
                $user->code = mt_rand(10000,90000);
                $user->save();
                $data = CommonActions::sendSms($phone, $user->code);
            }
        }
        return response()->json(array('errors' => $errors, 'data' => $data), $httpStatus);
    }

    /**
     * @api {post} /api/clients/login Login
     * @apiName Login
     * @apiGroup ClientAuth
     *
     * @apiParam {string} code
     */

    public function login(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $user = null;
        $validatorData = $request->all();
        $validator = Validator::make($validatorData, ['code' => 'required']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $user = Users::where([['type', '=', 1], ['code', '=', $request->code]])->first();
            if (empty($user)) {
                $errors['user'] = 'User not found';
                $httpStatus = 400;
            } else $user->token = md5($user->token);
        }
        return response()->json(array('errors' => $errors, 'data' => $user), $httpStatus);
    }

    /**
     * @api {post} /api/clients/sales/create Create Sale
     * @apiName CreateSale
     * @apiGroup ClientSales
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} outlet_id
     * @apiParam {integer} bill_id
     * @apiParam {integer} amount
     */

    public function edit_sale(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $sale = null;
        $validatorData = $request->all();
        $validator = Validator::make($validatorData,
            [
                'amount' => 'required|integer',
                'outlet_id' => 'required|exists:outlets,id',
                'bill_id' => 'required|exists:bills,id',
            ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $sale = new Sales;
            $sale->user_id = Auth::user()->id;
            $sale->outlet_id = $request->outlet_id;
            $sale->card_id = Bills::where('id', '=', $request->bill_id)->first()->card_id;
            $sale->bill_id = $request->bill_id;
            $sale->amount = $request->amount;
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
