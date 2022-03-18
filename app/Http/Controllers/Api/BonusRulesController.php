<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BonusRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BonusRulesController extends Controller
{
    /**
     * @api {post} /api/bonus_rules/create Create Bonus Rule
     * @apiName CreateBonusRule
     * @apiGroup AdminBonusRules
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [start_dt]
     * @apiParam {integer} [month]
     * @apiParam {integer} [day]
     * @apiParam {integer} duration
     * @apiParam {integer} field_id
     * @apiParam {integer} value
     * @apiParam {integer=0,1} [enabled]
     */

    /**
     * @api {post} /api/bonus_rules/edit/:id Edit Bonus Rule
     * @apiName EditBonusRule
     * @apiGroup AdminBonusRules
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [start_dt]
     * @apiParam {integer} [month]
     * @apiParam {integer} [day]
     * @apiParam {integer} duration
     * @apiParam {integer} [value]
     * @apiParam {integer=0,1} [enabled]
     */

    public function edit_bonus_rules(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $bonus = null;
        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validatorRules = [
            'start_dt' => 'nullable|date|date_format:Y-m-d',
            'month' => 'nullable|integer|between:0,12',
            'day' => 'nullable|integer|between:0,31',
            'duration' => (!$id ? 'required|' : 'nullable|') . 'integer',
        ];
        if (!$id) {
            $validatorRules['field_id'] = 'required|exists:fields,id';
            $validatorRules['value'] = 'required|integer';
        } else
            $validatorRules['id'] = 'exists:bonus_rules,id';

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $bonus = $id ? BonusRules::where('id', '=', $id)->first() : new BonusRules;
            if ($request->start_dt)
                $bonus->start_dt = $request->start_dt;
            if ($request->month && $request->day) {
                $bonus->month = $request->month;
                $bonus->day = $request->day;
            }
            if ($request->month === '0') {
                $bonus->month = $bonus->day = null;
            }
            if ($request->duration)
                $bonus->duration = $request->duration;
            if (!$id)
                $bonus->field_id = $request->field_id;
            if ($request->value)
                $bonus->value = $request->value;

            $bonus->save();
        }
        return response()->json(['errors' => $errors, 'data' => $bonus], $httpStatus);
    }

    /**
     * @api {post} /api/bonus_rules/list Get Bonus Rules List
     * @apiName GetBonusRulesList
     * @apiGroup AdminBonusRules
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     */

    public function list_bonus_rules(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;

        $validatorData = $request->all();
        $validatorRules = [
            'dir' => 'in:asc,desc',
            'order' => 'in:start_dt,month,day,duration,field_id,created_at,updated_at',
            'offset' => 'integer',
            'limit' => 'integer',
        ];

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }

        if (empty($errors)) {
            $query = BonusRules::select('*');
            $count = $query->count();
            $order = $request->order ?: 'bonus_rules.id';
            $dir = $request->dir ?: 'asc';
            $offset = $request->offset;
            $limit = $request->limit;
            $query->orderBy($order, $dir);
            if ($limit) {
                $query->limit($limit);
                if ($offset) $query->offset($offset);
            }
            $data = ['count' => $count, 'list' => $query->get()];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/bonus_rules/get/:id Get Bonus Rule
     * @apiName GetBonusRule
     * @apiGroup AdminBonusRules
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function get_bonus_rule($id)
    {
        $errors = [];
        $httpStatus = 200;
        $bonus = null;

        $validator = Validator::make(['id' => $id], ['id' => 'required|exists:bonus_rules,id,deleted_at,NULL']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }

        if (empty($errors)) {
            $bonus = BonusRules::where('id', '=', $id)->first();
        }
        return response()->json(['errors' => $errors, 'data' => $bonus], $httpStatus);
    }

    /**
     * @api {get} /api/bonus_rules/delete/:id Delete Bonus Rules
     * @apiName DeleteBonusRules
     * @apiGroup AdminBonusRules
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function delete_bonus_rules($id)
    {
        $errors = [];
        $httpStatus = 200;
        $validator = Validator::make(['id' => $id], ['id' => 'required|exists:bonus_rules,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            BonusRules::where('id', '=', $id)->delete();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }
}
