<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * @api {post} /api/clients/sms Send Auth Sms
     * @apiName SendAuthSms
     * @apiGroup ClientAuth
     *
     * @apiParam {string} phone
     */

    public function sendSms(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
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
            }

        }
        return response()->json(array('errors' => $errors, 'data' => null), $httpStatus);
    }
}
