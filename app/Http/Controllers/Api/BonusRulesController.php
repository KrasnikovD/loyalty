<?php

namespace App\Http\Controllers\Api;

use App\Exports\BonusRuleExport;
use App\Exports\CardExport;
use App\Http\Controllers\Controller;
use App\Models\Bills;
use App\Models\BonusRules;
use App\Models\CardHistory;
use App\Models\Cards;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class BonusRulesController extends Controller
{
    /**
     * @api {post} /api/bonus_rules/create Create Bonus Rule
     * @apiName CreateBonusRule
     * @apiGroup AdminBonusRules
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} name
     * @apiParam {integer=1,2,3} date_trigger_type
     * @apiParam {integer=1,2} trigger_type
     * @apiParam {string} [start_dt]
     * @apiParam {integer} [month]
     * @apiParam {integer} [day]
     * @apiParam {integer=0,1} [is_birthday]
     * @apiParam {integer} duration
     * @apiParam {integer} [field_id]
     * @apiParam {integer=0,1} [sex]
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
     * @apiParam {string} [name]
     * @apiParam {integer=1,2,3} [date_trigger_type]
     * @apiParam {integer=1,2} [trigger_type]
     * @apiParam {string} [start_dt]
     * @apiParam {integer} [month]
     * @apiParam {integer} [day]
     * @apiParam {integer=0,1} [is_birthday]
     * @apiParam {integer} [duration]
     * @apiParam {integer} [value]
     * @apiParam {integer=0,1} [enabled]
     */

    public function edit_bonus_rules(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 400;
        $bonus = null;

        Validator::extend('date_trigger_validate', function($attribute, $value, $parameters, $validator) {
            $month = $parameters[0];
            $day = $parameters[1];
            $startDt = $parameters[2];
            $isBirthday = $parameters[3] === "" ? null : $parameters[3];
            if ($value == BonusRules::TYPE_DATE_TRIGGER_DATE) {
                return !empty($startDt) && empty($month) && empty($day) && !isset($isBirthday);
            }
            if ($value == BonusRules::TYPE_DATE_TRIGGER_MONTHDAY) {
                return !empty($month) && !empty($day) && empty($startDt) && !isset($isBirthday);
            }
            if ($value == BonusRules::TYPE_DATE_TRIGGER_BIRTHDAY) {
                return empty($month) && empty($day) && empty($startDt) && isset($isBirthday);
            }
            return false;
        });
        Validator::extend('trigger_validate', function($attribute, $value, $parameters, $validator) {
            $sex = $parameters[0] === "" ? null : $parameters[0];
            $fieldId = $parameters[1];
            if ($value == BonusRules::TYPE_TRIGGER_SEX) {
                return isset($sex) && empty($fieldId);
            }
            if ($value == BonusRules::TYPE_TRIGGER_FIELD) {
                return !isset($sex) && !empty($fieldId);
            }
            return false;
        });
        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validatorRules = [
            'start_dt' => 'nullable|date|date_format:Y-m-d',
            'month' => 'nullable|integer|between:1,12',
            'day' => 'nullable|integer|between:1,31',
            'sex' => 'nullable|integer|in:0,1',
            'is_birthday' => 'nullable|integer|in:0,1',
            'date_trigger_type' => ($id ? 'nullable' : 'required')  . '|in:1,2,3|date_trigger_validate:' . $request->month . ',' . $request->day . ',' . $request->start_dt . ',' . $request->is_birthday,
            'trigger_type' => ($id ? 'nullable' : 'required')  . '|in:1,2|trigger_validate:' . $request->sex . ',' . $request->field_id,
        ];
        if (!$id) {
            $validatorRules['field_id'] = 'nullable|exists:fields,id';
            $validatorRules['value'] = 'required|integer';
            $validatorRules['name'] = 'required';
            $validatorRules['duration'] = 'required|integer|between:1,365';
        } else
            $validatorRules['id'] = 'exists:bonus_rules,id';

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
        }
        if (!isset($request->field_id) && !isset($request->sex)) {
            $errors['field_id'] = 'Either field_id or sex must be specified';
        }
        if (!isset($request->is_birthday) && !isset($request->start_dt) && !isset($request->month)) {
            $errors['field_id'] = 'Either is_birthday or start_dt or month & day must be specified';
        }
        if ((isset($request->month) || isset($request->day)) && (!isset($request->month) || !isset($request->day))) {
            $errors['field_id'] = 'month and day - both fields must be specified';
        }
        if (empty($errors)) {
            $httpStatus = 200;
            $bonus = $id ? BonusRules::where('id', '=', $id)->first() : new BonusRules;
            if ($request->is_birthday == 1) {
                $bonus->is_birthday = $request->is_birthday;
                $bonus->duration = $bonus->start_dt = $bonus->month = $bonus->day = null;
            } else {
                if ($request->start_dt) {
                    $bonus->start_dt = $request->start_dt;
                    $bonus->month = $bonus->day = null;
                } elseif ($request->month && $request->day) {
                    $bonus->month = $request->month;
                    $bonus->day = $request->day;
                    $bonus->start_dt = null;
                }
                if ($request->start_dt || ($request->month && $request->day))
                    $bonus->is_birthday = 0;
            }

            if (!$id) {
                if (isset($request->sex))
                    $bonus->sex = $request->sex;
                elseif ($request->field_id)
                    $bonus->field_id = $request->field_id;
            }
            if ($request->value)
                $bonus->value = $request->value;
            if ($request->name)
                $bonus->name = $request->name;
            if ($request->duration) {
                $bonus->duration = $request->duration;
                if ($request->is_birthday == 1 && $request->duration % 2 !== 0)
                    $bonus->duration ++;
            }
            if ($request->date_trigger_type)
                $bonus->date_trigger_type = $request->date_trigger_type;

            if ($request->trigger_type)
                $bonus->trigger_type = $request->trigger_type;

            if ($request->enabled)
                $bonus->enabled = $request->enabled;

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

    /**
     * @api {get} /api/bonus_rules/history/:id Bonus Rule History
     * @apiName BonusRuleHistory
     * @apiGroup AdminBonusRules
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function bonus_rule_history($id)
    {
        $errors = [];
        $httpStatus = 200;
        $report = null;
        $validator = Validator::make(['id' => $id], ['id' => 'required|exists:bonus_rules,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $bills = Bills::select('cards.id as card_id', 'bills.id as bill_id')
                ->join('cards', 'cards.id', '=', 'bills.card_id')
                ->where('rule_id', '=', $id)->get();
            $cardIds = array_column($bills->toArray(), 'card_id');
            $billsIds = array_column($bills->toArray(), 'bill_id');
            $historyData = Cards::select('card_history.id', 'cards.number', 'card_history.type', 'card_history.data', 'card_history.created_at')
                ->join('card_history', 'card_history.card_id', '=', 'cards.id')
                ->whereIn('card_history.type', [CardHistory::BONUS_BY_RULE_ADDED, CardHistory::SALE])
                ->whereIn('cards.id', $cardIds)
                ->orderBy('card_history.created_at', 'desc')
                ->get();

            $report = [];
            foreach ($historyData->toArray() as $entry) {
                $entryData = json_decode($entry['data']);
                if (in_array($entryData->bill_id, $billsIds)) {
                    $amount = $entry['type'] == CardHistory::SALE ? "-{$entryData->debited}" : "+{$entryData->value}";
                    $report[] = [
                        'number' => $entry['number'],
                        'bill_id' => $entryData->bill_id,
                        'type' => $entry['type'] == CardHistory::SALE ? 'debited' : 'added',
                        'date' => date('Y-m-d', strtotime($entry['created_at'])),
                        'amount' => $amount
                    ];
                }
            }
        }
        return response()->json(['errors' => $errors, 'data' => $report], $httpStatus);
    }

    /**
     * @api {get} /api/generate_bonus_rules_report/:id Generate Bonus Rules Report
     * @apiName GenerateBonusRulesReport
     * @apiGroup AdminReports
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function generate_bonus_rules_report($id)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $validator = Validator::make(['id' => $id], ['id' => 'required|exists:bonus_rules,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $fileName = "reports/" . date('Y-m-d_H_i_s') . '_bonus_rule_' . $id . '.xlsx';
            Storage::disk('local')->put($fileName, '');
            $export = new BonusRuleExport($id);
            Excel::store($export, Storage::path($fileName));
            $data = $fileName;
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }
}
