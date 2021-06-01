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
                'login',
                'send_auth_sms'
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
}
