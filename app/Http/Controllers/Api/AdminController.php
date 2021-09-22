<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Baskets;
use App\Models\BillPrograms;
use App\Models\Bills;
use App\Models\BillTypes;
use App\Models\BonusHistory;
use App\Models\CardHistory;
use App\Models\Cards;
use App\Models\Categories;
use App\Models\CommonActions;
use App\Models\Coupons;
use App\Models\DataHelper;
use App\Models\Devices;
use App\Models\News;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Reviews;
use App\Models\Sales;
use App\Models\Stocks;
use App\Models\Users;
use App\Models\Fields;
use App\Models\FieldsUsers;
use App\Notifications\WelcomeNotification;
use ExponentPhpSDK\Expo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    const REPLACED_FIRST_NAME = '{first_name}';
    const REPLACED_SECOND_NAME = '{second_name}';

    public function __construct()
    {
        $this->middleware('admin.token',
            ['except' => [
                'login'
            ]]);
    }

    /**
     * @api {post} /api/admin/login Login
     * @apiName Login
     * @apiGroup AdminAuth
     *
     * @apiParam {string} phone
     * @apiParam {string} password
     */

    public function login(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $user = null;
        $validatorData = $request->all();
        $validator = Validator::make($validatorData,
            [
                'phone' => 'required',
                'password' => 'required'
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $phone = str_replace(array("(", ")", " ", "-"), "", $request->phone);
            $user = Users::where([['type', '=', 0], ['phone', '=', $phone], ['archived', 0], ['password', '=', md5($request->password)]])->first();
            if (empty($user)) {
                $errors['user'] = __('auth.failed');
                $httpStatus = 400;
            } else $user->token = md5($user->token);
        }
        return response()->json(['errors' => $errors, 'data' => $user], $httpStatus);
    }

    /**
     * @api {post} /api/bill_types/create Create Bill Type
     * @apiName CreateBillType
     * @apiGroup AdminBillTypes
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} name
     */

    /**
     * @api {post} /api/bill_types/edit/:id Edit Bill Type
     * @apiName EditBillType
     * @apiGroup AdminBillTypes
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} name
     */

    public function edit_bill_type(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $billType = null;
        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validator = Validator::make($validatorData,
            [
                'name' => 'required|unique:bill_types,name',
                'id' => 'exists:bill_types,id'
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $billType = $id ? BillTypes::where('id', '=', $id)->first() : new BillTypes;
            $billType->name = $request->name;
            $billType->save();

            if(!$id) {
                $billTypeId = $billType->id;
                foreach (Cards::all() as $card) {
                    $bill = new Bills;
                    $bill->card_id = $card->id;
                    $bill->bill_type_id = $billTypeId;
                    $bill->save();
                }
            }
        }
        return response()->json(['errors' => $errors, 'data' => $billType], $httpStatus);
    }

    /**
     * @api {post} /api/bill_types/list Get Bill Type List
     * @apiName GetBillTypeList
     * @apiGroup AdminBillTypes
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     */

    /**
     * @api {get} /api/bill_types/get/:id Get Bill Type
     * @apiName GetBillType
     * @apiGroup AdminBillTypes
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_bill_types(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;

        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        if (!$id) {
            $validatorRules = [
                'dir' => 'in:asc,desc',
                'order' => 'in:id,name,created_at,updated_at',
                'offset' => 'integer',
                'limit' => 'integer',
            ];
        } else {
            Validator::extend('not_default', function($attribute, $value, $parameters, $validator) {
                $billType = BillTypes::where([['name', '=', 'default'],['id', '=', $value]])->first();
                return empty($billType);
            });
            $validatorRules = ['id' => 'exists:bill_types,id|not_default'];
        }
        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $count = 0;
            $query = BillTypes::where('name', '<>', 'default');
            if ($id) $query->where('id', '=', $id);
            else {
                $count = $query->count();
                $order = $request->order ?: 'bill_types.id';
                $dir = $request->dir ?: 'asc';
                $offset = $request->offset;
                $limit = $request->limit;

                $query->orderBy($order, $dir);
                if ($limit) {
                    $query->limit($limit);
                    if ($offset) $query->offset($offset);
                }
            }
            $list = $query->get();
            if ($id) $data = $list[0];
            else $data = ['count' => $count, 'list' => $list];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/bill_types/delete/:id Delete Bill Type
     * @apiName DeleteBillType
     * @apiGroup AdminBillTypes
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function delete_bill_type($id)
    {
        $errors = [];
        $httpStatus = 200;
        Validator::extend('not_default', function($attribute, $value, $parameters, $validator) {
            $billType = BillTypes::where([['name', '=', 'default'],['id', '=', $value]])->first();
            return empty($billType);
        });
        $validator = Validator::make(['id' => $id], ['id' => 'exists:bill_types,id|not_default']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            Bills::where('bill_type_id', '=', $id)->delete();
            BillTypes::where('id', '=', $id)->delete();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {post} /api/users/create Create User
     * @apiName CreateUser
     * @apiGroup AdminUsers
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} first_name
     * @apiParam {string} second_name
     * @apiParam {string} [third_name]
     * @apiParam {string} password
     * @apiParam {string} phone
     * @apiParam {datetime} [birthday]
     * @apiParam {boolean} [archived]
     * @apiParam {boolean} [active]
     * @apiParam {integer=0,1,2} type
     * @apiParam {object[]} [fields]
     * @apiParam {integer} [code]
     */

    /**
     * @api {post} /api/users/edit/:id Edit User
     * @apiName EditUser
     * @apiGroup AdminUsers
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [first_name]
     * @apiParam {string} [second_name]
     * @apiParam {string} [third_name]
     * @apiParam {string} [password]
     * @apiParam {string} [phone]
     * @apiParam {datetime} [birthday]
     * @apiParam {boolean} [archived]
     * @apiParam {boolean} [active]
     * @apiParam {integer=0,1,2} [type]
     * @apiParam {object[]} [fields]
     * @apiParam {integer} [code]
     */

    public function edit_user(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $user = null;
        Validator::extend('phone_validate', function($attribute, $value, $parameters, $validator) {
            $validateData = [['phone', '=', $value]];
            $userId = $parameters[0];
            if (isset($userId))
                $validateData[] = ['id', '<>', $userId];
            return !Users::where($validateData)->exists();
        });
        Validator::extend('field_validation', function($attribute, $value, $parameters, $validator) {
            $fieldName = @$value['name'];
            if (empty($fieldName) || !array_key_exists('value', $value))
                return false;
            return Fields::where('name', $fieldName)->exists();
        });
        if ($request->phone) $request->phone = str_replace(array("(", ")", " ", "-"), "", $request->phone);
        $validatorData = $request->all();
        $validatorData['phone'] = $request->phone;
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validatorRules = [];
        if(!$id) {
            $validatorRules['first_name'] = 'required';
            $validatorRules['second_name'] = 'required';
         //   $validatorRules['password'] = 'required';
        } else {
            $validatorRules['id'] = 'exists:users,id';
        }
        $validatorRules['type'] = (!$id ? 'required|' : '') . 'in:0,1';
        $validatorRules['phone'] = (!$id ? 'required|' : '') . "phone_validate:{$id}";

        $validatorRules['birthday'] = 'nullable|date';
        $validatorRules['archived'] = 'in:0,1';
        $validatorRules['active'] = 'in:0,1';
        $validatorRules['fields'] = 'nullable|array';
        $validatorRules['fields.*'] = 'field_validation';
        $validatorRules['code'] = 'nullable|integer';

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $user = $id ? Users::where('id', '=', $id)->first() : new Users;
            foreach (['first_name', 'second_name', 'third_name', 'phone', 'type', 'code'] as $field)
                if (isset($request->{$field})) $user->{$field} = $request->{$field};

            if ($request->password) $user->password = md5($request->password);
            if (!$id) $user->token = sha1(microtime() . 'salt' . time());

            if ($request->birthday) $user->birthday = date("Y-m-d H:i:s", strtotime($request->birthday));
            $archived = $request->archived;
            $active = $request->active;
            if (isset($archived)) $user->archived = $request->archived;
            if (isset($active)) $user->active = $request->active;
            $user->save();

            if($user->type == 1) {
                foreach (Fields::all() as $field) {
                    $fieldValue = null;
                    $keyExists = false;
                    if (is_array($request->fields) && count($request->fields) > 0) {
                        foreach ($request->fields as $requestField) {
                            if ($requestField['name'] == $field['name']) {
                                $fieldValue = $requestField['value'];
                                $keyExists = true;
                                break;
                            }
                        }
                    }
                    $fieldsUser = $id ? FieldsUsers::where([
                        ['field_id', $field->id],
                        ['user_id', $user->id]])->first() : new FieldsUsers;
                    if ($fieldsUser) {
                        if (!$id) {
                            $fieldsUser->field_id = $field->id;
                            $fieldsUser->user_id = $user->id;
                        }
                        if ($keyExists) $fieldsUser->value = $fieldValue;
                        $fieldsUser->save();
                    }
                }
            }
        }
        return response()->json(['errors' => $errors, 'data' => $user], $httpStatus);
    }

    /**
     * @api {post} /api/users/list Get Users List
     * @apiName GetUsersList
     * @apiGroup AdminUsers
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     * @apiParam {integer=0,1} [hide_deleted]
     * @apiParam {string} [filters]
     */

    /**
     * @api {get} /api/users/get/:id Get User
     * @apiName GetUser
     * @apiGroup AdminUsers
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_users(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;

        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        if (!$id) {
            $validatorRules = [
                'dir' => 'in:asc,desc',
                'order' => 'in:id,code,first_name,second_name,phone,password,token,type,device_token,created_at,updated_at',
                'offset' => 'integer',
                'limit' => 'integer',
                'hide_deleted' => 'in:0,1',
            ];
        } else {
            $validatorRules = ['id' => 'exists:users,id'];
        }
        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }

        if (empty($errors)) {
            $count = 0;
            $query = Users::select('users.*')
                ->leftJoin('cards', 'cards.user_id', '=', 'users.id');
            if ($request->hide_deleted == 1) $query->where('users.archived', '=', 0);
            if ($id) $query->where('users.id', '=', $id);
            else {
                if ($request->filters) {
                    $query->orWhere('users.first_name', 'like', $request->filters . '%');
                    $query->orWhere('users.second_name', 'like',  '%' . $request->filters . '%');
                    $query->orWhere('users.third_name', 'like',  '%' . $request->filters . '%');
                    $query->orWhere('users.phone', 'like', '%' .
                        str_replace(array("(", ")", " ", "-"), "", $request->filters) . '%');
                    $query->orWhere('cards.number', 'like', $request->filters . '%');
                }
                $count = $query->distinct()->count('users.id');
                $order = $request->order ?: 'users.id';
                $dir = $request->dir ?: 'asc';
                $offset = $request->offset;
                $limit = $request->limit;
                $query->orderBy($order, $dir);
                if ($limit) {
                    $query->limit($limit);
                    if ($offset) $query->offset($offset);
                }
            }
            $list = $query->distinct()->get()->toArray();
            DataHelper::collectUsersInfo($list, false);
            DataHelper::collectUserStatInfo($list);
            if ($id) $data = $list[0];
            else $data = ['count' => $count, 'list' => $list];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/users/delete/:id Delete User
     * @apiName DeleteUser
     * @apiGroup AdminUsers
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function delete_user($id)
    {
        $errors = [];
        $httpStatus = 200;
        Validator::extend('archived_validate', function($attribute, $value, $parameters, $validator) {
            return Users::where([['archived', '=', 0], ['id', '=', $value]])->exists();
        });
        $validator = Validator::make(['id' => $id], ['id' => 'exists:users,id|archived_validate']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) Users::where('id', '=', $id)->update(['archived' => 1]);
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {post} /api/cards/create Create Card
     * @apiName CreateCard
     * @apiGroup AdminCards
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} number
     * @apiParam {integer} [user_id]
     * @apiParam {integer=0,1} [is_physical]
     * @apiParam {integer=0,1} [is_main]
     * @apiParam {string} [phone]
     */

    /**
     * @api {post} /api/cards/edit/:id Edit Card
     * @apiName EditCard
     * @apiGroup AdminCards
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [number]
     * @apiParam {integer} [user_id]
     * @apiParam {integer=0,1} [is_physical]
     * @apiParam {integer=0,1} [is_main]
     * @apiParam {string} [phone]
     */

    public function edit_card(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        Validator::extend('validate_card_number', function($attribute, $value, $parameters, $validator) {
            $validateData = [['number', '=', $value]];
            if (!empty($parameters[0])) $validateData[] = ['id', '<>', $parameters[0]];
            return !Cards::where($validateData)->exists();
        });
        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validatorRules = [];
        $validatorRules['number'] = [
            !$id ? 'required' : 'nullable',
            "unique:cards,number,{$id}",
            "regex:/^z\d{7}$|^\d{8}$/i"
        ];
        if ($id) $validatorRules['id'] = 'exists:cards,id,deleted_at,NULL';
        $validatorRules['user_id'] = 'exists:users,id';
        $validatorRules['is_physical'] = 'in:0,1';
        $validatorRules['is_main'] = 'in:0,1';

        $messages = [
            'number.unique' => 'Номер уже занят.',
            'number.regex' => 'Формат номера не корректный.',
            'number.required' => 'Поле номер обязательно для заполнения.',
        ];

        $validator = Validator::make($validatorData, $validatorRules, $messages);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $card = $id ? Cards::where('id', '=', $id)->first() : new Cards;
            if ($request->user_id) $card->user_id = $request->user_id;
            if ($request->number) $card->number = strtoupper($request->number);
            if ($request->phone) $card->phone = $phone = str_replace(array("(", ")", " ", "-"), "", $request->phone);
            $isPhysical = $request->is_physical;
            $isMain = $request->is_main;
            if (isset($isPhysical)) $card->is_physical = $isPhysical;
            if (isset($isMain) && $card->user_id) {
                if ($prevMainCard = Cards::where([['user_id', '=', $card->user_id], ['is_main', '=', 1]])->first()) {
                    $prevMainCard->is_main = 0;
                    $prevMainCard->save();
                    CommonActions::cardHistoryLogEditOrCreate($prevMainCard, false);
                }
                $card->is_main = $isMain;
            }
            $card->save();
            if ($request->user_id)
                Sales::where('card_id', '=', $card->id)->update(['user_id' => $card->user_id]);
            if (!$id) {
                $billProgramId = $remainingAmount = null;
                $programs = BillPrograms::orderBy('from', 'asc')->get();
                if (isset($programs[0]) && $programs[0]->from == 0) {
                    $billProgramId = $programs[0]->id;
                    $remainingAmount = isset($programs[1]) ? $programs[1]->from : $programs[0]->to;
                }
                foreach (BillTypes::all() as $billType) {
                    $bill = new Bills;
                    $bill->card_id = $card->id;
                    $bill->bill_type_id = $billType->id;
                    $bill->bill_program_id = $billProgramId;
                    $bill->remaining_amount = $remainingAmount;
                    $bill->save();
                }
            }
            CommonActions::cardHistoryLogEditOrCreate($card, !$id);
            $data = [$card->toArray()];
            DataHelper::collectCardsInfo($data);
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {post} /api/cards/list Get Cards List
     * @apiName GetCardsList
     * @apiGroup AdminCards
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     * @apiParam {integer} [filters]
     * @apiParam {integer=0,1} [unattached] only without users
     */

    /**
     * @api {get} /api/cards/get/:id Get Card
     * @apiName GetCard
     * @apiGroup AdminCards
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_cards(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        if (!$id) {
            $validatorRules = [
                'dir' => 'in:asc,desc',
                'order' => 'in:id,user_id,number,created_at,updated_at',
                'offset' => 'integer',
                'limit' => 'integer',
                'unattached' => 'nullable|in:0,1',
            ];
        } else {
            $validatorRules = ['id' => 'exists:cards,id'];
        }
        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $count = 0;
            $query = Cards::select('*');
            if ($id) $query->where('id', '=', $id);
            else {
                if ($request->filters) {
                    $query->orWhere('cards.phone', 'like', '%' .
                        str_replace(array("(", ")", " ", "-"), "", $request->filters) . '%');
                    $query->orWhere('cards.number', 'like', $request->filters . '%');
                }
                if (intval($request->unattached) === 1) {
                    $query->whereNull('user_id');
                }
                $count = $query->count();
                $order = $request->order ?: 'cards.id';
                $dir = $request->dir ?: 'asc';
                $offset = $request->offset;
                $limit = $request->limit;

                $query->orderBy($order, $dir);
                if ($limit) {
                    $query->limit($limit);
                    if ($offset) $query->offset($offset);
                }
            }

            $list = $query->get()->toArray();
            DataHelper::collectCardsInfo($list);
            if ($id) $data = $list[0];
            else $data = ['count' => $count, 'list' => $list];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/cards/delete/:id Delete Card
     * @apiName DeleteCard
     * @apiGroup AdminCards
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function delete_card($id)
    {
        $errors = [];
        $httpStatus = 200;

        $validator = Validator::make(['id' => $id], ['id' => 'exists:cards,id,deleted_at,NULL']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
      //      $bills = Bills::where('card_id', '=', $id);
      //      $bills->delete();
            Cards::where('id', '=', $id)->delete();
            CommonActions::cardHistoryLogDelete($id);
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {post} /api/bill_programs/create Create Bill Program
     * @apiName CreateBillProgram
     * @apiGroup AdminBillPrograms
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} from
     * @apiParam {integer} to
     * @apiParam {integer} percent
     * @apiParam {integer} file_content
     */

    /**
     * @api {post} /api/bill_programs/edit/:id Edit Bill Program
     * @apiName EditBillProgram
     * @apiGroup AdminBillPrograms
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} [from]
     * @apiParam {integer} [to]
     * @apiParam {integer} [percent]
     * @apiParam {integer} [file_content]
     */

    public function edit_bill_program(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $billProgram = null;
        $from = @$request->from;
        $to = @$request->to;
        if($id) {
            $bill = BillPrograms::where('id', '=', $id)->first();
            if (!isset($from)) $from = $bill->from;
            if (!isset($to)) $to = $bill->to;
        }
        Validator::extend('from_to_valid', function($attribute, $value, $parameters, $validator) {
            if ((!empty($parameters[0]) || $parameters[0] === '0') && !empty($parameters[1])) {
                list($from, $to) = $parameters;
                if ($from >= $to) return false;
                $q = BillPrograms::select('*');
                if (isset($value))
                    $q->where('id', '<>', $value);
                $programs = $q->orderBy('from')->get();
                $sections = [];
                for ($i = 0; $i < count($programs); $i ++) {
                    $sections[] = [$programs[$i]->from, $programs[$i]->to];
                }
                return !CommonActions::intersection($sections, $from, $to);
            }
            return true;
        });

        $validatorData = $request->all();
        $validatorData = array_merge($validatorData, ['section' => $id]);
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validator = Validator::make($validatorData,
            [
                'from' => (!$id ? 'required|' : '') . "integer",
                'to' => (!$id ? 'required|' : '') . "integer",
                'percent' => (!$id ? 'required|' : '') . 'integer|max:100',
                'id' => 'exists:bill_programs,id',
                'section' => "from_to_valid:{$from},{$to}"
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $billProgram = $id ? BillPrograms::where('id', '=', $id)->first() : new BillPrograms;
            if(isset($request->from)) $billProgram->from = $request->from;
            if(isset($request->to)) $billProgram->to = $request->to;
            if(isset($request->percent)) $billProgram->percent = $request->percent;

            if (isset($request->file_content)) {
                if ($id) @unlink(Storage::path("images/{$billProgram->file}"));
                $fileName = uniqid() . ".jpeg";
                Storage::disk('local')->put("images/$fileName", '');
                $path = Storage::path("images/$fileName");
                $imageTmp = imagecreatefromstring(base64_decode($request->file_content));
                imagejpeg($imageTmp, $path);
                imagedestroy($imageTmp);
                $billProgram->file = $fileName;
            }

            $billProgram->save();
        }
        return response()->json(['errors' => $errors, 'data' => $billProgram], $httpStatus);
    }

    /**
     * @api {post} /api/bill_programs/list Get Bill Programs List
     * @apiName GetBillProgramsList
     * @apiGroup AdminBillPrograms
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     */

    public function list_bill_programs(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;

        $validatorData = $request->all();
        $validatorRules = [
            'dir' => 'in:asc,desc',
            'order' => 'in:id,from,to,percent,created_at,updated_at',
            'offset' => 'integer',
            'limit' => 'integer',
        ];

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }

        if (empty($errors)) {
            $query = BillPrograms::select('*');
            $count = $query->count();
            $order = $request->order ?: 'bill_programs.id';
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
     * @api {get} /api/bill_programs/get/:id Get Bill Program
     * @apiName GetBillProgram
     * @apiGroup AdminBillPrograms
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function get_bill_program($id)
    {
        $errors = [];
        $httpStatus = 200;
        $bill = null;

        $validator = Validator::make(['id' => $id], ['id' => 'required|exists:bill_programs,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }

        if (empty($errors)) {
            $bill = BillPrograms::where('id', '=', $id)->first();
        }
        return response()->json(['errors' => $errors, 'data' => $bill], $httpStatus);
    }

    /**
     * @api {get} /api/bill_programs/delete/:id Delete Bill Program
     * @apiName DeleteBillProgram
     * @apiGroup AdminBillPrograms
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function delete_bill_program($id)
    {
        $errors = [];
        $httpStatus = 200;
        $validator = Validator::make(['id' => $id], ['id' => 'required|exists:bill_programs,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $billProgram = BillPrograms::where('id', '=', $id)->first();
            if ($billProgram->file) @unlink(Storage::path("images/{$billProgram->file}"));
            BillPrograms::where('id', '=', $id)->delete();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {post} /api/outlets/create Create Outlet
     * @apiName CreateOutlet
     * @apiGroup AdminOutlets
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} name
     * @apiParam {string} phone
     * @apiParam {string} lon
     * @apiParam {string} lat
     * @apiParam {string} from
     * @apiParam {string} to
     */

    /**
     * @api {post} /api/outlets/edit/:id Edit Outlet
     * @apiName EditOutlet
     * @apiGroup AdminOutlets
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [name]
     * @apiParam {string} [phone]
     * @apiParam {string} [lon]
     * @apiParam {string} [lat]
     * @apiParam {string} [from]
     * @apiParam {string} [to]
     */

    public function edit_outlet(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $outlet = null;
        $validatorData = $request->all();
        if ($request->phone) $validatorData['phone'] = str_replace(array("(", ")", " ", "-"), "", $request->phone);
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validatorRules = [
            'phone' => (!$id ? 'required|' : '') . 'unique:outlets,phone,' . $id,
            'lon' => (!$id ? 'required|' : '') . 'regex:/^\d+(\.\d+)?$/',
            'lat' => (!$id ? 'required|' : '') . 'regex:/^\d+(\.\d+)?$/',
            'from' => (!$id ? 'required|' : '') . 'regex:/^\d{2}:\d{2}$/',
            'to' => (!$id ? 'required|' : '') . 'regex:/^\d{2}:\d{2}$/',
        ];

        if (!$id) $validatorRules['name'] = 'required';
        else $validatorRules['id'] = 'exists:outlets,id';

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $outlet = $id ? Outlet::where('id', '=', $id)->first() : new Outlet;
            if ($request->name) $outlet->name = $request->name;
            if ($request->phone) $outlet->phone = str_replace(array("(", ")", " ", "-"), "", $request->phone);
            if ($request->lon) $outlet->lon = $request->lon;
            if ($request->lat) $outlet->lat = $request->lat;
            if ($request->from) $outlet->from = $request->from;
            if ($request->to) $outlet->to = $request->to;
            if($request->lon && $request->lat && ($address = CommonActions::geocode($request->lon, $request->lat))) {
                $outlet->address = $address['address'];
                $outlet->city_name = $address['city'];
                $outlet->street_name = $address['street'];
                $outlet->house_name = $address['house'];
            }
            $outlet->save();
        }
        return response()->json(['errors' => $errors, 'data' => $outlet], $httpStatus);
    }

    /**
     * @api {post} /api/outlets/list Get Outlets List
     * @apiName GetOutletsList
     * @apiGroup AdminOutlets
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     */

    /**
     * @api {get} /api/outlets/get/:id Get Outlet
     * @apiName GetOutlet
     * @apiGroup AdminOutlets
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_outlets(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;

        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        if (!$id) {
            $validatorRules = [
                'dir' => 'in:asc,desc',
                'order' => 'in:id,name,phone,address,city_name,street_name,house_name,lon,lat,created_at,updated_at',
                'offset' => 'integer',
                'limit' => 'integer',
            ];
        } else {
            $validatorRules = ['id' => 'exists:outlets,id'];
        }
        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }

        if (empty($errors)) {
            $count = 0;
            $query = Outlet::select('*');
            if ($id) $query->where('id', '=', $id);
            else {
                $count = $query->count();
                $order = $request->order ?: 'outlets.id';
                $dir = $request->dir ?: 'asc';
                $offset = $request->offset;
                $limit = $request->limit;

                $query->orderBy($order, $dir);
                if ($limit) {
                    $query->limit($limit);
                    if ($offset) $query->offset($offset);
                }
            }
            $list = $query->get();
            DataHelper::collectOutletStatInfo($list);
            if ($id) $data = $list[0];
            else $data = ['count' => $count, 'list' => $list];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/outlets/delete/:id Delete Outlet
     * @apiName DeleteOutlet
     * @apiGroup AdminOutlets
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function delete_outlet($id)
    {
        $errors = [];
        $httpStatus = 200;
        $validator = Validator::make(['id' => $id], ['id' => 'exists:outlets,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            //Sales::where('outlet_id', '=', $id)->delete();
            Outlet::where('id', '=', $id)->delete();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {post} /api/outlets/send_to_nearest/:id Send To Nearest
     * @apiName SendToNearest
     * @apiGroup AdminOutlets
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} radius
     * @apiParam {string} title
     * @apiParam {string} body
     * @apiParam {object[]} [fields]
     */

    public function send_to_nearest(Request $request, $id)
    {
        $errors = [];
        $httpStatus = 200;
        $validatorData = $request->all();
        $validatorData['id'] = $id;
        Validator::extend('check_field', function($attribute, $value, $parameters, $validator) {
            $fieldId = @intval($value['field_id']);
            if (empty($fieldId) || !array_key_exists('value', $value))
                return false;
            return Fields::where('id', $fieldId)->exists();
        });
        $validator = Validator::make($validatorData, [
            'id' => 'exists:outlets,id',
            'radius' => 'required|integer',
            'title' => 'required',
            'body' => 'required',
            'fields' => 'nullable|array',
            'fields.*' => 'check_field',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $outlet = Outlet::where('id', '=', $id)->first();
            $q = Users::select('users.*', 'devices.expo_token'/*, 'fields.name', 'fields_users.value'*/)
                ->where([['active', '=', 1], ['archived', '=', 0]])
                ->join('devices', 'devices.user_id', '=', 'users.id')
                ->whereNotNull('lat')
                ->whereNotNull('lon')
                ->where('devices.disabled', '=', 0);
            if (is_array($request->fields) && count($request->fields) > 0) {
                $q->join('fields_users', 'fields_users.user_id', '=', 'users.id')
                   /*->join('fields', 'fields.id', '=', 'fields_users.field_id')*/;
                $fieldId = $request->fields[0]['field_id'];
                $fieldValue = $request->fields[0]['value'];
                $q->where([['fields_users.field_id', '=', $fieldId], ['fields_users.value', '=', $fieldValue]]);
            }
            $users = $q->get();

            $tokens = [];
            foreach ($users as $user) {
                $distance = ceil(CommonActions::calculateDistance($outlet->lat, $outlet->lon, $user->lat, $user->lon));
                if ($distance <= $request->radius) $tokens[$user->id] = $user->expo_token;
            }
            if (!empty($tokens)) {
                $title = $request->title;
                $body = $request->body;
                $expo = Expo::normalSetup();
                $channelName = 'channel_' . time();
                foreach ($tokens as $token)
                    $expo->subscribe($channelName, $token);
                $expo->notify([$channelName], ['title' => $title, 'body' => $body, 'sound' => 'default', 'ttl' => 30]);
            }
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {post} /api/fields/create Create Field
     * @apiName CreateField
     * @apiGroup AdminFields
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} name
     * @apiParam {boolean} [is_user_editable]
     */

    /**
     * @api {post} /api/fields/edit/:id Edit Field
     * @apiName EditField
     * @apiGroup AdminFields
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [name]
     * @apiParam {boolean} [is_user_editable]
     */

    public function edit_field(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $field = null;
        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validatorRules = ['is_user_editable' => 'nullable|in:0,1,true,false'];
        $validatorRules['name'] = (!$id ? 'required|' : '') . 'unique:fields,name,' . $id;
        if ($id) $validatorRules['id'] = 'exists:fields,id';

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $field = $id ? Fields::where('id', '=', $id)->first() : new Fields;
            if ($request->name) $field->name = $request->name;
            if (isset($request->is_user_editable)) {
                $isEditable = intval($request->is_user_editable === 'true' ||
                    $request->is_user_editable === true ||
                    intval($request->is_user_editable) === 1);
                $field->is_user_editable = $isEditable;
            }
            $field->save();

            if (!$id) {
                $fieldId = $field->id;
                foreach (Users::all() as $user) {
                    $fieldsUser = new FieldsUsers;
                    $fieldsUser->user_id = $user->id;
                    $fieldsUser->field_id = $fieldId;
                    $fieldsUser->save();
                }
            }
        }
        return response()->json(['errors' => $errors, 'data' => $field], $httpStatus);
    }

    /**
     * @api {post} /api/fields/list Get Fields List
     * @apiName GetFieldsList
     * @apiGroup AdminFields
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     */

    /**
     * @api {get} /api/fields/get/:id Get Field
     * @apiName GetField
     * @apiGroup AdminFields
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_fields(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;

        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        if (!$id) {
            $validatorRules = [
                'dir' => 'in:asc,desc',
                'order' => 'in:id,name,created_at,updated_at',
                'offset' => 'integer',
                'limit' => 'integer',
            ];
        } else {
            $validatorRules = ['id' => 'exists:fields,id'];
        }
        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }

        if (empty($errors)) {
            $count = 0;
            $query = Fields::select('*');
            if ($id) $query->where('id', '=', $id);
            else {
                $count = $query->count();
                $order = $request->order ?: 'fields.id';
                $dir = $request->dir ?: 'asc';
                $offset = $request->offset;
                $limit = $request->limit;
                $query->orderBy($order, $dir);
                if ($limit) {
                    $query->limit($limit);
                    if ($offset) $query->offset($offset);
                }
            }
            $list = $query->get();
            if ($id) $data = $list[0];
            else $data = ['count' => $count, 'list' => $list];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/fields/delete/:id Delete Field
     * @apiName DeleteField
     * @apiGroup AdminFields
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function delete_field($id)
    {
        $errors = [];
        $httpStatus = 200;
        $validator = Validator::make(['id' => $id], ['id' => 'exists:fields,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            FieldsUsers::where('field_id', '=', $id)->delete();
            Fields::where('id', '=', $id)->delete();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {post} /api/categories/create Create Category
     * @apiName CreateCategory
     * @apiGroup AdminCategories
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} name
     * @apiParam {integer} file_content
     */

    /**
     * @api {post} /api/categories/edit/:id Edit Category
     * @apiName EditCategory
     * @apiGroup AdminCategories
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [name]
     * @apiParam {integer} [file_content]
     */

    public function edit_category(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $category = null;

        Validator::extend('is_root', function($attribute, $value, $parameters, $validator) {
            return Categories::where([['id', '=', $value], ['parent_id', '=', 0]])->exists();
        });

        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validatorRules = [];
        if(!$id) {
            $validatorRules['name'] = 'required';
            $validatorRules['file_content'] = 'required';
        }
        else $validatorRules['id'] = 'is_root';

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $category = $id ? Categories::where('id', '=', $id)->first() : new Categories;
            $category->parent_id = 0;
            if (isset($request->name)) $category->name = $request->name;
            if (isset($request->file_content)) {
                if ($id) @unlink(Storage::path("images/{$category->file}"));
                $fileName = null;
                $finfo = finfo_open();
                $decoded = base64_decode($request->file_content);
                $mimeType = finfo_buffer($finfo, $decoded, FILEINFO_MIME_TYPE);
                switch ($mimeType) {
                    case "image/png":
                        $fileName = uniqid() . ".png";
                        break;
                    case "image/jpeg":
                        $fileName = uniqid() . ".jpeg";
                        break;
                    case "image/tiff":
                        $fileName = uniqid() . ".tiff";
                        break;
                    case "image/gif":
                        $fileName = uniqid() . ".gif";
                        break;
                }
                finfo_close($finfo);
                if ($fileName)
                    Storage::disk('local')->put("images/$fileName", $decoded);
                else {
                    $fileName = uniqid() . ".jpeg";
                    Storage::disk('local')->put("images/$fileName", '');
                    $path = Storage::path("images/$fileName");
                    $imageTmp = imagecreatefromstring(base64_decode($request->file_content));
                    imagejpeg($imageTmp, $path);
                    imagedestroy($imageTmp);
                }
                $category->file = $fileName;
            }
            $category->save();
        }
        return response()->json(['errors' => $errors, 'data' => $category], $httpStatus);
    }

    /**
     * @api {post} /api/categories/sub_create Create Sub Category
     * @apiName CreateSubCategory
     * @apiGroup AdminCategories
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} name
     * @apiParam {integer} parent_id
     */

    /**
     * @api {post} /api/categories/sub_edit/:id Edit Sub Category
     * @apiName EditSubCategory
     * @apiGroup AdminCategories
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [name]
     * @apiParam {integer} [parent_id]
     */

    public function edit_subcategory(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $category = null;

        Validator::extend('is_root', function($attribute, $value, $parameters, $validator) {
            return Categories::where([['id', '=', $value], ['parent_id', '=', 0]])->exists();
        });
        Validator::extend('is_child', function($attribute, $value, $parameters, $validator) {
            return Categories::where([['id', '=', $value], ['parent_id', '<>', 0]])->exists();
        });
        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validatorRules = [];
        if(!$id) $validatorRules['name'] = 'required';
        else $validatorRules['id'] = 'is_child';
        $validatorRules['parent_id'] = (!$id ? 'required|' : '') . 'is_root';

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $category = $id ? Categories::where('id', '=', $id)->first() : new Categories;
            if (isset($request->parent_id)) $category->parent_id = $request->parent_id;
            if (isset($request->name)) $category->name = $request->name;
            $category->save();
        }
        return response()->json(['errors' => $errors, 'data' => $category], $httpStatus);
    }

    /**
     * @api {get} /api/categories/list Get Categories List
     * @apiName GetCategoriesList
     * @apiGroup AdminCategories
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_categories()
    {
        $errors = [];
        $httpStatus = 200;

        $data = Categories::where('parent_id', '=', 0)
            ->where('name', '<>', Categories::DEFAULT_NAME)
            ->get();
        $parentIds = array_column($data->toArray(), 'id');
        $subCategoriesMap = [];
        if (!empty($parentIds)) {
            $subCategories = Categories::whereIn('parent_id', $parentIds)->get();
            foreach ($subCategories as $category) {
                if (!isset($subCategoriesMap[$category['parent_id']])) $subCategoriesMap[$category['parent_id']] = [];
                $subCategoriesMap[$category['parent_id']][] = $category;
            }
        }
        foreach ($data as &$item) {
            $item->sub_categories = @$subCategoriesMap[$item['id']];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/categories/delete/:id Delete Category
     * @apiName DeleteCategory
     * @apiGroup AdminCategories
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function delete_category($id)
    {
        $errors = [];
        $httpStatus = 200;
        $validator = Validator::make(['id' => $id], ['id' => 'exists:categories,id,deleted_at,NULL']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $query = Categories::where('id', '=', $id);
            @unlink(Storage::path("images/{$query->first()->file}"));
            $query->delete();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {post} /api/products/create Create Product
     * @apiName CreateProduct
     * @apiGroup AdminProducts
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} category_id
     * @apiParam {string} code
     * @apiParam {string} name
     * @apiParam {string} description
     * @apiParam {integer} price
     * @apiParam {integer} file_content
     * @apiParam {integer} [is_hit]
     * @apiParam {integer} [is_novelty]
     * @apiParam {integer} is_by_weight
     * @apiParam {integer} [visible]
     */

    /**
     * @api {post} /api/products/edit/:id Edit Product
     * @apiName EditProduct
     * @apiGroup AdminProducts
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} [category_id]
     * @apiParam {string} [code]
     * @apiParam {string} [name]
     * @apiParam {string} [description]
     * @apiParam {integer} [price]
     * @apiParam {integer} [file_content]
     * @apiParam {integer=0,1} [is_hit]
     * @apiParam {integer=0,1} [is_novelty]
     * @apiParam {integer=0,1} [is_by_weight]
     * @apiParam {integer=0,1} [visible]
     */

    public function edit_product(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $product = null;

       /* Validator::extend('is_child', function($attribute, $value, $parameters, $validator) {
            return Categories::where([['id', '=', $value], ['parent_id', '<>', 0]])->exists();
        });*/

        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validatorRules = [];
        if(!$id) {
            $validatorRules['name'] = 'required';
            $validatorRules['description'] = 'required';
            $validatorRules['file_content'] = 'required';
        } else
            $validatorRules['id'] = 'exists:products,id';
       // $validatorRules['category_id'] = (!$id ? 'required|' : '') . 'is_child';
        $validatorRules['category_id'] = (!$id ? 'required|' : '') . 'exists:categories,id,deleted_at,NULL';
       // $validatorRules['outlet_id'] = (!$id ? 'required|' : '') . 'exists:outlets,id';
        $validatorRules['code'] = 'unique:products,code,' . $id;
        $validatorRules['price'] = 'integer';
        $validatorRules['is_hit'] = 'nullable|in:0,1,true,false';
        $validatorRules['is_novelty'] = 'nullable|in:0,1,true,false';
        $validatorRules['is_by_weight'] = 'nullable|in:0,1,true,false';
        $validatorRules['visible'] = 'nullable|in:0,1,true,false';

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $product = $id ? Product::where('id', '=', $id)->first() : new Product;
         //   if (isset($request->outlet_id)) $product->outlet_id = $request->outlet_id;
            if (isset($request->category_id)) $product->category_id = $request->category_id;
            if (isset($request->code)) $product->code = $request->code;
            if (isset($request->name)) $product->name = $request->name;
            if (isset($request->description)) $product->description = $request->description;
            if (isset($request->price)) $product->price = $request->price;
            if (isset($request->is_hit)) {
                $isHit = intval($request->is_hit === 'true' ||
                    $request->is_hit === true ||
                    intval($request->is_hit) === 1);
                $product->is_hit = $isHit;
            }
            if (isset($request->is_novelty)) {
                $isNovelty = intval($request->is_novelty === 'true' ||
                    $request->is_novelty === true ||
                    intval($request->is_novelty) === 1);
                $product->is_novelty = $isNovelty;
            }
            if (isset($request->is_by_weight)) {
                $isByWeight = intval($request->is_by_weight === 'true' ||
                    $request->is_by_weight === true ||
                    intval($request->is_by_weight) === 1);
                $product->is_by_weight = $isByWeight;
            }
            if (isset($request->visible)) {
                $visible = intval($request->visible === 'true' ||
                    $request->visible === true ||
                    intval($request->visible) === 1);
                $product->visible = $visible;
            }
            if (isset($request->file_content)) {
                if ($id) @unlink(Storage::path("images/{$product->file}"));
                $fileName = uniqid() . ".jpeg";
                Storage::disk('local')->put("images/$fileName", '');
                $path = Storage::path("images/$fileName");
                $imageTmp = imagecreatefromstring(base64_decode($request->file_content));
                imagejpeg($imageTmp, $path);
                imagedestroy($imageTmp);
                $product->file = $fileName;
            }
            $product->save();
            $parentCatId = @Categories::where('id', $request->category_id ?: $product->category_id)->first()->parent_id;
            $product->parent_category_id = intval($parentCatId);
        }
        return response()->json(['errors' => $errors, 'data' => $product], $httpStatus);
    }

    /**
     * @api {post} /api/products/list Get Products List
     * @apiName GetProductsList
     * @apiGroup AdminProducts
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     * @apiParam {integer} [category_id]
     * @apiParam {integer=0,1} [hide_deleted]
     */

    public function list_products(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        Validator::extend('check_categories', function($attribute, $value, $parameters, $validator) {
            return $value == -1 || Categories::where('id', $value)->exists();
        });
        $validatorRules = [
            'dir' => 'in:asc,desc',
            'order' => 'in:id,category_id,name,description,file,price,created_at,updated_at',
            'offset' => 'integer',
            'limit' => 'integer',
           // 'outlet_id' => 'exists:outlets,id',
            'category_id' => 'check_categories',
            'hide_deleted' => 'in:0,1',
        ];
        $validator = Validator::make($request->all(), $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $products = Product::select('products.*', /*'outlets.name as outlet_name',*/
                'categories.name as category_name',
                DB::raw('categories.deleted_at is not null as category_deleted'),
                'parent_categories.id as parent_category_id',
                'parent_categories.name as parent_category_name'
                )
               // ->leftJoin('outlets', 'outlets.id', '=', 'products.outlet_id')
                ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
                ->leftJoin('categories as parent_categories', 'parent_categories.id', '=', 'categories.parent_id');
            if ($request->hide_deleted == 1)
                $products->where('archived', '=', 0);
            if (isset($request->category_id)) {
                if (Categories::where('id', '=', $request->category_id)->value('parent_id') === 0) {
                    $categories = Categories::where('parent_id', '=', $request->category_id)->get()->toArray();
                    $categoriesIds = array_column($categories, 'id');
                    $categoriesIds[] = $request->category_id;
                    if(!empty($categoriesIds))
                        $products->whereIn('category_id', $categoriesIds);
                } else {
                    if ($request->category_id == -1)
                        $products->whereNotNull('categories.deleted_at');
                    else
                        $products->where('category_id', '=', $request->category_id);
                }
            }
           /* if (isset($request->outlet_id)) {
                $products->where('outlet_id', '=', $request->outlet_id);
            }*/

            $count = $products->count();

            $order = $request->order ?: 'products.id';
            $dir = $request->dir ?: 'asc';
            $offset = $request->offset;
            $limit = $request->limit;

            $products->orderBy($order, $dir);
            if ($limit) {
                $products->limit($limit);
                if ($offset) $products->offset($offset);
            }

            $data = ['count' => $count, 'data' => $products->get()];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/products/get/:id Get Product
     * @apiName GetProduct
     * @apiGroup AdminProducts
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function get_product($id)
    {
        $errors = [];
        $httpStatus = 200;
        $product = null;
        $validator = Validator::make(['id' => $id], ['id' => 'exists:products,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $product = Product::select('products.*', /*'outlets.name as outlet_name',*/ 'categories.name as category_name')
                //->leftJoin('outlets', 'outlets.id', '=', 'products.outlet_id')
                ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
                ->where('products.id', '=', $id)->first();
        }
        return response()->json(['errors' => $errors, 'data' => $product], $httpStatus);
    }

    /**
     * @api {get} /api/products/delete/:id Delete Product
     * @apiName DeleteProduct
     * @apiGroup AdminProducts
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function delete_product($id)
    {
        $errors = [];
        $httpStatus = 200;
        Validator::extend('archived_validate', function($attribute, $value, $parameters, $validator) {
            return Product::where([['archived', '=', 0], ['id', '=', $value]])->exists();
        });
        $validator = Validator::make(['id' => $id], ['id' => 'exists:products,id|archived_validate']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
         //   $query = Product::where('id', '=', $id);
         //   @unlink(Storage::path("images/{$query->first()->file}"));
         //   $query->delete();
            Product::where('id', '=', $id)->update(['archived' => 1]);
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {patch} /api/products/switch_visibility/:id Switch Visibility
     * @apiName SwitchVisibility
     * @apiGroup AdminProducts
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function switch_product_visibility($id)
    {
        $errors = [];
        $httpStatus = 200;
        $validator = Validator::make(['id' => $id], ['id' => 'exists:products,id,archived,0']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $product = Product::where('id', '=', $id)->first();
            $product->visible = !$product->visible;
            $product->save();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {patch} /api/products/set_position Set positions
     * @apiName SetPositions
     * @apiGroup AdminProducts
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer[]} ids_list
     */

    public function set_position(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $validator = Validator::make($request->all(), [
            'ids_list' => 'required|array',
            'ids_list.*' => 'exists:products,id'
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $idsList = array_reverse($request->ids_list);
            for ($i=0; $i<count($idsList); $i++) {
                $product = Product::where('id', $idsList[$i])->first();
                $product->position = $i;
                $product->save();
            }
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {post} /api/orders/create Create Order
     * @apiName CreateOrder
     * @apiGroup AdminOrders
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} user_id
     * @apiParam {integer} outlet_id
     * @apiParam {object[]} products
     */

    /**
     * @api {post} /api/orders/edit/:id Edit Order
     * @apiName EditOrder
     * @apiGroup AdminOrders
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     *  @apiParam {integer} [outlet_id]
     * @apiParam {object[]} [products]
     */

    public function edit_order(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $sale = null;
        Validator::extend('check_product', function($attribute, $value, $parameters, $validator) {
            if (!isset($value['count']) || !is_integer($value['count']) || $value['count'] === 0)
                return false;
            return Product::where('id', '=', $value['product_id'])->exists();
        });
        Validator::extend('check_user', function($attribute, $value, $parameters, $validator) {
            return Users::where([['id', '=', $value], ['type', '=', 1]])->exists();
        });

        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validatorRules = [];
        if (!$id) $validatorRules['user_id'] = 'required|check_user';
        $validatorRules['outlet_id'] = (!$id ? 'required|' : '') . 'exists:outlets,id';
        $validatorRules['products'] = (!$id ? 'required|' : '') . 'array';
        $validatorRules['products.*'] = 'check_product';

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $sale = $id ? Sales::where('id', '=', $id)->first() : new Sales;
            if (isset($request->outlet_id)) $sale->outlet_id = $request->outlet_id;
            if (!$id) {
                $sale->user_id = $request->user_id;
                $sale->dt = date("Y-m-d H:i:s");
            }
            if (!empty($request->products)) {
                $productsIds = array_column($request->products, 'product_id');
                $productsMap = [];
                foreach (Product::whereIn('id', $productsIds)->get() as $item) {
                    $productsMap[$item->id] = $item->price;
                }
                $amount = 0;
                foreach ($request->products as $product) {
                    $amount += $productsMap[$product['product_id']] * $product['count'];
                }
                $sale->amount = $sale->amount_now = $amount;
                $sale->save();
                if ($id) Baskets::where('sale_id', '=', $id)->delete();
                foreach ($request->products as $product) {
                    $basket = new Baskets;
                    $basket->sale_id = $sale->id;
                    $basket->product_id = $product['product_id'];
                    $basket->amount = $productsMap[$product['product_id']];
                    $basket->count = $product['count'];
                    $basket->save();
                }
            }
            $sale->save();
        }
        return response()->json(['errors' => $errors, 'data' => $sale], $httpStatus);
    }

    /**
     * @api {post} /api/orders/list Get Orders List
     * @apiName GetOrdersList
     * @apiGroup AdminOrders
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     * @apiParam {integer} [user_id] user id
     * @apiParam {integer=0,4,5,6,7} [status]
     */

    /**
     * @api {get} /api/orders/get/:id Get Order
     * @apiName GetOrder
     * @apiGroup AdminOrders
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_orders(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;

        if (!$id) {
            $validatorRules = [
                'dir' => 'in:asc,desc',
                'order' => 'in:id,dt,status,amount,amount_now,created_at,updated_at',
                'offset' => 'integer',
                'limit' => 'integer',
                'user_id' => 'integer|exists:users,id',
				'status' => 'in:' . Sales::STATUS_COMPLETED . ',' .
                    Sales::STATUS_PRE_ORDER . ',' .
                    Sales::STATUS_CANCELED_BY_OUTLET . ',' .
                    Sales::STATUS_CANCELED_BY_CLIENT . ',' .
                    Sales::STATUS_CANCELED_BY_ADMIN
            ];
        } else {
            $validatorRules = ['id' => 'exists:sales,id'];
        }
        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $count = 0;
            $sales = Sales::select('sales.*', 'users.id as user_id', 'users.phone as users_phone',
                'outlets.id as outlet_id', 'outlets.name as outlet_name', 'outlets.address as outlets_address',
                'cards.id as card_id', 'cards.number as card_number',
                'users.first_name as user_first_name', 'users.second_name as user_second_name')
                ->leftJoin('users', 'users.id', '=', 'sales.user_id')
				->leftJoin('outlets', 'outlets.id', '=', 'sales.outlet_id')
                ->leftJoin('cards', 'cards.id', '=', 'sales.card_id');
            $status = $request->status;
			if (isset($status))
                $sales->where('sales.status', '=', $request->status);
            if (!$id) {
                if ($request->user_id) $sales->where('sales.user_id', '=', $request->user_id);

                $count = $sales->count();

                $order = $request->order ?: 'sales.id';
                $dir = $request->dir ?: 'asc';
                $offset = $request->offset;
                $limit = $request->limit;

                $sales->orderBy($order, $dir);
                if ($limit) {
                    $sales->limit($limit);
                    if ($offset) $sales->offset($offset);
                }
            } else $sales->where('sales.id', '=', $id);

            $list = $sales->get()->toArray();
            $salesIds = array_column($list, 'id');
            $basketsMap = [];
			$baskets = Baskets::select('baskets.*', 'products.name as product_name', 'products.is_by_weight as product_is_by_weight')
				->leftJoin('products', 'products.id', '=', 'baskets.product_id')
				->whereIn('sale_id', $salesIds)->get();
            foreach ($baskets as $basket) {
                if(!isset($basketsMap[$basket->sale_id])) $basketsMap[$basket->sale_id] = [];
                $basketsMap[$basket->sale_id][] = $basket->toArray();
            }

            $bonusMap = [];
            $bonuses = BonusHistory::select('*')->whereIn('sale_id', $salesIds)->get();
            foreach ($bonuses as $bonus) {
                if(!isset($bonusMap[$bonus->sale_id])) $bonusMap[$bonus->sale_id] = [];
                $bonusMap[$bonus->sale_id] = $bonus->toArray();
            }
            foreach ($list as &$item) {
                $item['bonus'] = @$bonusMap[$item['id']];
                $item['basket'] = @$basketsMap[$item['id']];
            }
            $data = $id ? $list[0] : ['count' => $count, 'data' => $list];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/orders/delete/:id Delete Order
     * @apiName DeleteOrder
     * @apiGroup AdminOrders
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function delete_order($id)
    {
        $errors = [];
        $httpStatus = 200;
        $validator = Validator::make(['id' => $id], ['id' => 'exists:sales,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            Baskets::where('sale_id', '=', $id)->delete();
            Sales::where('id', '=', $id)->delete();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {get} /api/orders/cancel/:id Cancel Order
     * @apiName CancelOrder
     * @apiGroup AdminOrders
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function cancel_order($id)
    {
        $errors = [];
        $httpStatus = 200;
        Validator::extend('check_sale', function($attribute, $value, $parameters, $validator) {
            return @Sales::where('id', '=', $value)
                ->whereIn('status', [Sales::STATUS_PRE_ORDER, Sales::STATUS_COMPLETED])
                ->exists();
        });
        $validatorData = ['sale_id' => $id];
        $validator = Validator::make($validatorData, ['sale_id' => 'exists:sales,id|check_sale']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            if ($baskets = Baskets::where('sale_id', '=', $id)->whereNotNull('coupon_id')->get()) {
                foreach ($baskets as $basket) {
                    $coupon = Coupons::where('id', '=', $basket->coupon_id)->first();
                    $coupon->count += $basket->count;
                    $coupon->save();
                }
            }
            $sale = Sales::where('id', '=', $id)->first();
            $sale->status = Sales::STATUS_CANCELED_BY_ADMIN;
            $sale->save();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {get} /api/orders/delete_basket/:id Delete Basket
     * @apiName DeleteBasket
     * @apiGroup AdminOrders
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function delete_basket($id)
    {
        $errors = [];
        $httpStatus = 200;
        $validator = Validator::make(['id' => $id], ['id' => 'exists:baskets,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $saleId = Baskets::where('id', '=', $id)->value('sale_id');
            Baskets::where('id', '=', $id)->delete();
            $amount = 0;
            foreach (Baskets::where('sale_id', '=', $saleId)->get() as $item) {
                $amount += $item->amount * $item->count;
            }
            $sale = Sales::where('id', '=', $saleId)->first();
            $sale->amount = $sale->amount_now = $amount;
            $sale->save();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {post} /api/orders/edit_basket/:id Edit Basket
     * @apiName EditBasket
     * @apiGroup AdminOrders
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} count
     */

    public function edit_basket(Request $request, $id)
    {
        $errors = [];
        $httpStatus = 200;

        $validatorData = array_merge($request->all(), ['id' => $id]);
        $validator = Validator::make($validatorData,
            ['id' => 'exists:baskets,id', 'count' => 'required|integer']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $basket = Baskets::where('id', '=', $id)->first();
            $basket->count = $request->count;
            $basket->save();

            $saleId = $basket->sale_id;
            $sale = Sales::where('id', '=', $saleId)->first();
            $amount = 0;
            foreach (Baskets::where('sale_id', '=', $saleId)->get() as $item) {
                $amount += $item->amount * $item->count;
            }
            $sale->amount = $sale->amount_now = $amount;
            $sale->save();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {post} /api/orders/add_basket/:sale_id Add Basket
     * @apiName AddBasket
     * @apiGroup AdminOrders
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} count
     * @apiParam {integer} product_id
     */

    public function add_basket(Request $request, $saleId)
    {
        $errors = [];
        $httpStatus = 200;
        $basket = null;

        $validatorData = array_merge($request->all(), ['sale_id' => $saleId]);
        $validator = Validator::make($validatorData,
            [
                'sale_id' => 'exists:sales,id',
                'count' => 'required|integer',
                'product_id' => 'required|exists:products,id'
            ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $basket = new Baskets;
            $basket->sale_id = $saleId;
            $basket->product_id = $request->product_id;
            $basket->count = $request->count;
            $basket->amount = Product::where('id', '=', $request->product_id)->first()->price;
            $basket->save();

            $sale = Sales::where('id', '=', $saleId)->first();
            $amount = 0;
            foreach (Baskets::where('sale_id', '=', $saleId)->get() as $item) {
                $amount += $item->amount * $item->count;
            }
            $sale->amount = $sale->amount_now = $amount;
            $sale->save();
        }
        return response()->json(['errors' => $errors, 'data' => $basket], $httpStatus);
    }

    /**
     * @api {post} /api/news/create Create News
     * @apiName CreateNews
     * @apiGroup AdminNews
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} name
     * @apiParam {string} description
     * @apiParam {integer} file_content
     * @apiParam {boolean} [sms]
     * @apiParam {boolean} [push]
     */

    /**
     * @api {post} /api/news/edit/:id Edit News
     * @apiName EditNews
     * @apiGroup AdminNews
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [name]
     * @apiParam {string} [description]
     * @apiParam {integer} [file_content]
     */

    public function edit_news(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $news = null;

        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validatorRules = [];
        if(!$id) {
            $validatorRules['name'] = 'required';
            $validatorRules['description'] = 'required';
            $validatorRules['file_content'] = 'required';
        } else
            $validatorRules['id'] = 'exists:news,id';

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $news = $id ? News::where('id', '=', $id)->first() : new News;
            if (isset($request->name)) $news->name = $request->name;
            if (isset($request->description)) $news->description = $request->description;
            if (isset($request->file_content)) {
                if ($id) @unlink(Storage::path("images/{$news->file}"));
                $fileName = uniqid() . ".jpeg";
                Storage::disk('local')->put("images/$fileName", '');
                $path = Storage::path("images/$fileName");
                $imageTmp = imagecreatefromstring(base64_decode($request->file_content));
                imagejpeg($imageTmp, $path);
                imagedestroy($imageTmp);
                $news->file = $fileName;
            }
            $news->save();

            if (!$id) {
                $sms = $request->sms;
                $push = $request->push;
                if (!empty($sms) || !empty($push)) {
                    $title = __('messages.im_new_news_title', ['news_name' => $news->name]);
                    $body = __('messages.im_new_news_body', ['news_name' => $news->name]);
                    if (!empty($sms)) {
                        $phones = [];
                        $users = Users::select('phone')
                            ->where([['active', '=', 1], ['archived', '=', 0], ['type', '=', Users::TYPE_USER]])
                            ->get();
                        foreach ($users as $user) {
                            $phone = str_replace(array(' ', '(', ')', '-', '+'), "", $user->phone);
                            if (strpos($phone, '38071') === 0 || strpos($phone, '071') === 0 || strpos($phone, '71') === 0) {
                                foreach(['/^38071[0-9]{7}$/', '/^071[0-9]{7}$/', '/^71[0-9]{7}$/'] as $pattern) {
                                    if (preg_match($pattern, $phone)) $phones[] = $phone;
                                }
                            } else $phones[] = $phone;
                        }
                        if (!empty($phones)) CommonActions::sendSms($phones, $body);
                    }
                    if (!empty($push)) {
                        $devices = Devices::select('expo_token')->where('disabled', '=', 0)->get()->toArray();
                        $tokens = array_column($devices, 'expo_token');
                        if (!empty($tokens)) {
                            $expo = Expo::normalSetup();
                            $channelName = 'channel_' . time();
                            foreach ($tokens as $token)
                                $expo->subscribe($channelName, $token);
                            $expo->notify([$channelName], ['title' => $title, 'body' => $body, 'sound' => 'default', 'ttl' => 3600]);
                        }
                    }
                }
            }
        }
        return response()->json(['errors' => $errors, 'data' => $news], $httpStatus);
    }

    /**
     * @api {post} /api/news/list Get News List
     * @apiName GetNewsList
     * @apiGroup AdminNews
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     */

    /**
     * @api {get} /api/news/get/:id Get News
     * @apiName GetNews
     * @apiGroup AdminNews
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_news(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        if($id) {
            $validator = Validator::make(['id' => $id], ['id' => 'exists:news,id']);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $httpStatus = 400;
            }
        }
        if (empty($errors)) {
            $count = 0;
            $query = News::select('*');
            if ($id) $query->where('id', '=', $id);
            else {
                $count = $query->count();
                $order = $request->order ?: 'news.id';
                $dir = $request->dir ?: 'asc';
                $offset = $request->offset;
                $limit = $request->limit;

                $query->orderBy($order, $dir);
                if ($limit) {
                    $query->limit($limit);
                    if ($offset) $query->offset($offset);
                }
            }
            $list = $query->get()->toArray();
            if ($id) $data = $list[0];
            else $data = ['count' => $count, 'list' => $list];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/news/delete/:id Delete News
     * @apiName DeleteNews
     * @apiGroup AdminNews
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function delete_news($id)
    {
        $errors = [];
        $httpStatus = 200;
        $validator = Validator::make(['id' => $id], ['id' => 'exists:news,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            News::where('id', '=', $id)->delete();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {post} /api/stocks/create Create Stock
     * @apiName CreateStock
     * @apiGroup AdminStocks
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} name
     * @apiParam {string} description
     * @apiParam {integer} file_content
     * @apiParam {boolean} [sms]
     * @apiParam {boolean} [push]
     */

    /**
     * @api {post} /api/stocks/edit/:id Edit News
     * @apiName EditStock
     * @apiGroup AdminStocks
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [name]
     * @apiParam {string} [description]
     * @apiParam {integer} [file_content]
     */

    public function edit_stock(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $stock = null;

        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validatorRules = [];
        if(!$id) {
            $validatorRules['name'] = 'required';
            $validatorRules['description'] = 'required';
            $validatorRules['file_content'] = 'required';
        } else
            $validatorRules['id'] = 'exists:stocks,id';

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $stock = $id ? Stocks::where('id', '=', $id)->first() : new Stocks;
            if (isset($request->name)) $stock->name = $request->name;
            if (isset($request->description)) $stock->description = $request->description;
            if (isset($request->file_content)) {
                if ($id) @unlink(Storage::path("images/{$stock->file}"));
                $fileName = uniqid() . ".jpeg";
                Storage::disk('local')->put("images/$fileName", '');
                $path = Storage::path("images/$fileName");
                $imageTmp = imagecreatefromstring(base64_decode($request->file_content));
                imagejpeg($imageTmp, $path);
                imagedestroy($imageTmp);
                $stock->file = $fileName;
            }
            $stock->save();

            if (!$id) {
                $sms = $request->sms;
                $push = $request->push;
                if (!empty($sms) || !empty($push)) {
                    $title = __('messages.im_new_stock_title', ['stock_name' => $stock->name]);
                    $body = __('messages.im_new_stock_body', ['stock_name' => $stock->name]);
                    if (!empty($sms)) {
                        $phones = [];
                        $users = Users::select('phone')
                            ->where([['active', '=', 1], ['archived', '=', 0], ['type', '=', Users::TYPE_USER]])->get();
                        foreach ($users as $user) {
                            $phone = str_replace(array(' ', '(', ')', '-', '+'), "", $user->phone);
                            if (strpos($phone, '38071') === 0 || strpos($phone, '071') === 0 || strpos($phone, '71') === 0) {
                                foreach(['/^38071[0-9]{7}$/', '/^071[0-9]{7}$/', '/^71[0-9]{7}$/'] as $pattern)
                                    if (preg_match($pattern, $phone)) $phones[] = $phone;
                            } else $phones[] = $phone;
                        }
                        if (!empty($phones)) CommonActions::sendSms($phones, $body);
                    }
                    if (!empty($push)) {
                        $devices = Devices::select('expo_token')->where('disabled', '=', 0)->get()->toArray();
                        $tokens = array_column($devices, 'expo_token');
                        if (!empty($tokens)) {
                            $expo = Expo::normalSetup();
                            $channelName = 'channel_' . time();
                            foreach ($tokens as $token)
                                $expo->subscribe($channelName, $token);
                            $expo->notify([$channelName], ['title' => $title, 'body' => $body, 'sound' => 'default', 'ttl' => 3600]);
                        }
                    }
                }
            }
        }
        return response()->json(['errors' => $errors, 'data' => $stock], $httpStatus);
    }

    /**
     * @api {post} /api/stocks/list Get Stocks List
     * @apiName GetStocksList
     * @apiGroup AdminStocks
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     */

    /**
     * @api {get} /api/stocks/get/:id Get Stock
     * @apiName GetStock
     * @apiGroup AdminStocks
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_stocks(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        if($id) {
            $validator = Validator::make(['id' => $id], ['id' => 'exists:stocks,id']);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $httpStatus = 400;
            }
        }
        if (empty($errors)) {
            $count = 0;
            $query = Stocks::select('*');
            if ($id) $query->where('id', '=', $id);
            else {
                $count = $query->count();
                $order = $request->order ?: 'stocks.id';
                $dir = $request->dir ?: 'asc';
                $offset = $request->offset;
                $limit = $request->limit;

                $query->orderBy($order, $dir);
                if ($limit) {
                    $query->limit($limit);
                    if ($offset) $query->offset($offset);
                }
            }
            $list = $query->get()->toArray();
            if ($id) $data = $list[0];
            else $data = ['count' => $count, 'list' => $list];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/stocks/delete/:id Delete Stock
     * @apiName DeleteStock
     * @apiGroup AdminStocks
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function delete_stock($id)
    {
        $errors = [];
        $httpStatus = 200;
        $validator = Validator::make(['id' => $id], ['id' => 'exists:stocks,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            Stocks::where('id', '=', $id)->delete();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {post} /api/devices/list Get Devices
     * @apiName GetDevices
     * @apiGroup AdminDevices
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     */

    public function devices_list(Request $request)
    {
        $query = Devices::select('*');
        $count = $query->count();
        $order = $request->order ?: 'devices.id';
        $dir = $request->dir ?: 'asc';
        $offset = $request->offset;
        $limit = $request->limit;

        $query->orderBy($order, $dir);
        if ($limit) {
            $query->limit($limit);
            if ($offset) $query->offset($offset);
        }

        $list = $query->get()->toArray();
        $data = ['count' => $count, 'list' => $list];
        return response()->json(['errors' => [], 'data' => $data]);
    }

    /**
     * @api {post} /api/devices/send_pushes Send Pushes
     * @apiName SendPushes
     * @apiGroup AdminDevices
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string=all,birthday} scope
     * @apiParam {string} title
     * @apiParam {string} body
     * @apiParam {object[]} [fields]
     */

    public function send_pushes(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $recipients = null;
        Validator::extend('check_field', function($attribute, $value, $parameters, $validator) {
            $fieldId = @intval($value['field_id']);
            if (empty($fieldId) || !array_key_exists('value', $value))
                return false;
            return Fields::where('id', $fieldId)->exists();
        });
        $validator = Validator::make($request->all(), [
            'devices_ids' => 'array',
            'devices_ids.*' => 'exists:devices,id',
            'title' => 'required',
            'body' => 'required',
            'scope' => 'required|in:all,birthday',
            'fields' => 'nullable|array',
            'fields.*' => 'check_field',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $devices = Devices::select('devices.*')->where('devices.disabled', '=', 0);
            $isFieldsExists = is_array($request->fields) && count($request->fields) > 0;
            if ($request->scope == 'birthday' || $isFieldsExists) {
                $devices->join('users', 'users.id', '=', 'devices.user_id');
                if ($isFieldsExists) {
                    $devices->join('fields_users', 'fields_users.user_id', '=', 'users.id');
                    $fieldId = $request->fields[0]['field_id'];
                    $fieldValue = $request->fields[0]['value'];
                    $devices->where([
                        ['fields_users.field_id', '=', $fieldId],
                        ['fields_users.value', '=', $fieldValue]
                    ]);
                }
                if ($request->scope == 'birthday') {
                    $devices->select('devices.*', 'users.first_name', 'users.second_name');
                    $devices->where(DB::raw("DATE_FORMAT(users.birthday,CONCAT(YEAR(NOW()), '-%m-%d'))"), '=', DB::raw("DATE_ADD('" . date('Y-m-d') . "', INTERVAL 1 DAY)"));
                }
            }
            $devices = $devices->get();
            if ($devices->count() > 0) {
                if ($request->scope == 'birthday') {
                    foreach ($devices as $device) {
                        $title = Str::replace(self::REPLACED_FIRST_NAME, $device->first_name, $request->title);
                        $title = Str::replace(self::REPLACED_SECOND_NAME, $device->second_name, $title);

                        $body = Str::replace(self::REPLACED_FIRST_NAME, $device->first_name, $request->body);
                        $body = Str::replace(self::REPLACED_SECOND_NAME, $device->second_name, $body);

                        $device->notify(new WelcomeNotification($title, $body));
                    }
                } else {
                    $expo = Expo::normalSetup();
                    $channelName = 'channel_' . time();

                    foreach ($devices as $device)
                        $expo->subscribe($channelName, $device->expo_token);
                    $expo->notify([$channelName], ['title' => $request->title, 'body' => $request->body, 'sound' => 'default', 'ttl' => 3600]);
                }
                $recipients = $devices;
            }
        }
        return response()->json(['errors' => $errors, 'data' => $recipients], $httpStatus);
    }

    /**
     * @api {post} /api/coupons/create Create Coupon
     * @apiName CreateCoupon
     * @apiGroup AdminCoupons
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} user_id
     * @apiParam {integer} product_id
     * @apiParam {integer} count
     * @apiParam {string} date_end
     */

    /**
     * @api {post} /api/coupons/edit/:id Edit Coupon
     * @apiName EditCoupon
     * @apiGroup AdminCoupons
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} [count]
     * @apiParam {string} [date_end]
     */

    public function edit_coupon(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $coupon = null;
        $data = null;
        Validator::extend('check_archived', function($attribute, $value, $parameters, $validator) {
            return Product::where([['id', '=', $value], ['archived', '=', 0]])->exists();
        });
        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validatorRules = [
            'id' => 'exists:coupons,id',
            'count' => (!$id ? 'required|' : '') . 'integer|min:1'
        ];
        if (!$id) {
            $validatorRules['user_id'] = 'required|exists:users,id';
            $validatorRules['product_id'] = 'required|exists:products,id|check_archived';
        }
        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $coupon = $id ? Coupons::where('id', '=', $id)->first() : new Coupons;
            if (!$id) {
                $coupon->user_id = $request->user_id;
                $coupon->product_id = $request->product_id;
            }
            $coupon->count = $coupon->init_count = $request->count;
            if ($request->date_end)
                $coupon->date_end = date('Y-m-d H:i:s', strtotime($request->date_end));
            $coupon->save();

            if (!$id) {
               // $productName = Product::where('id', '=', $request->product_id)->first()->name;
                $phone = Users::where('id', '=', $request->user_id)->first()->phone;
                CommonActions::sendSms([$phone], trans('messages.new_coupon_body'));
                $device = Devices::where('user_id', '=', $request->user_id)->first();
                if ($device)
                    $device->notify(new WelcomeNotification(trans('messages.new_coupon_title'), trans('messages.new_coupon_body')));
            }
            $data = Coupons::select('coupons.id', 'coupons.count', 'users.id as user_id', 'users.first_name',
                'users.second_name', 'users.phone', 'products.id as product_id', 'products.name')
                ->join('users', 'users.id', '=', 'coupons.user_id')
                ->join('products', 'products.id', '=', 'coupons.product_id')
                ->where('coupons.id', '=', $coupon->id)->first();
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {post} /api/coupons/list Get Coupons
     * @apiName GetCoupons
     * @apiGroup AdminCoupons
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     */

    /**
     * @api {get} /api/coupons/get/:id Get Coupon
     * @apiName GetCoupon
     * @apiGroup AdminCoupons
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_coupons(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        if($id) {
            $validator = Validator::make(['id' => $id], ['id' => 'exists:coupons,id']);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $httpStatus = 400;
            }
        }
        if (empty($errors)) {
            $count = 0;
            $query = Coupons::select('coupons.*', 'products.name as product_name', 'users.first_name', 'users.second_name', 'users.phone')
                ->join('products', 'products.id', '=', 'coupons.product_id')
                ->join('users', 'users.id', '=', 'coupons.user_id');
            if ($id) {
                $query->where('coupons.id', '=', $id);
            } else {
                $query->where('coupons.count', '>', 0);
                $count = $query->count();
                $order = $request->order ?: 'coupons.id';
                $dir = $request->dir ?: 'asc';
                $offset = $request->offset;
                $limit = $request->limit;
                $query->orderBy($order, $dir);
                if ($limit) {
                    $query->limit($limit);
                    if ($offset) $query->offset($offset);
                }
            }
            $list = $query->get()->toArray();
            if ($id) $data = $list[0];
            else $data = ['count' => $count, 'list' => $list];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/coupons/delete/:id Delete Coupon
     * @apiName DeleteCoupon
     * @apiGroup AdminCoupons
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function delete_coupon($id)
    {
        $errors = [];
        $httpStatus = 200;

        Validator::extend('check_in_use', function($attribute, $value, $parameters, $validator) {
            return !Baskets::where('coupon_id', '=', $value)->exists();
        });

        $validator = Validator::make(['id' => $id], ['id' => 'exists:coupons,id|check_in_use']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            Coupons::where('id', '=', $id)->delete();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {get} /api/reviews/moderate/:id Set/Unset Visibility Review
     * @apiName VisibilityReview
     * @apiGroup AdminReviews
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function moderate_review($id)
    {
        $errors = [];
        $httpStatus = 200;
        $review = null;
        $validator = Validator::make(['id' => $id], ['id' => 'required|exists:reviews,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $review = Reviews::where('id', '=', $id)->first();
            $review->is_hidden = !$review->is_hidden;
            $review->save();
        }
        return response()->json(['errors' => $errors, 'data' => $review], $httpStatus);
    }

    /**
     * @api {get} /api/news/moderate/:id Set/Unset Visibility News
     * @apiName VisibilityNews
     * @apiGroup AdminNews
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function moderate_news($id)
    {
        $errors = [];
        $httpStatus = 200;
        $item = null;
        $validator = Validator::make(['id' => $id], ['id' => 'required|exists:news,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $item = News::where('id', '=', $id)->first();
            $item->is_hidden = !$item->is_hidden;
            $item->save();
        }
        return response()->json(['errors' => $errors, 'data' => $item], $httpStatus);
    }

    /**
     * @api {get} /api/reviews/list Get Reviews List
     * @apiName GetReviewsList
     * @apiGroup AdminReviews
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     * @apiParam {string=product,outlet} [type]
     */

    /**
     * @api {get} /api/reviews/get/:id Get Review
     * @apiName GetReview
     * @apiGroup AdminReviews
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_reviews(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validator = Validator::make($validatorData, [
            'id' => 'exists:reviews,id',
            'type' => 'nullable|in:product,outlet',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $count = 0;
            $query = Reviews::select('reviews.id', 'reviews.message', 'reviews.is_hidden', 'reviews.type', 'reviews.rating', 'reviews.object_id',
                'users.first_name as user_first_name', 'users.second_name as user_second_name')
                ->join('users', 'users.id', '=', 'reviews.user_id');
            if ($id) $query->where('id', '=', $id);
            else {
                if ($request->type)
                    $query->where('reviews.type', '=', $request->type);
                $count = $query->count();
                $order = $request->order ?: 'reviews.id';
                $dir = $request->dir ?: 'asc';
                $offset = $request->offset;
                $limit = $request->limit;

                $query->orderBy($order, $dir);
                if ($limit) {
                    $query->limit($limit);
                    if ($offset) $query->offset($offset);
                }
            }
            $list = $query->get()->toArray();

            $productsMap = [];
            foreach (Product::all() as $product)
                $productsMap[$product['id']] = $product->toArray();
            $outletsMap = [];
            foreach (Outlet::all() as $outlet)
                $outletsMap[$outlet['id']] = $outlet->toArray();

            foreach ($list as &$item) {
                $item['product_name'] = $item['type'] == Reviews::TYPE_PRODUCT ? @$productsMap[$item['object_id']]['name'] : null;
                $item['outlet_name'] = $item['type'] == Reviews::TYPE_OUTLET ? @$outletsMap[$item['object_id']]['name'] : null;
            }

            if ($id) $data = $list[0];
            else $data = ['count' => $count, 'list' => $list];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/cards/history/:id Get Card History
     * @apiName GetCardHistory
     * @apiGroup AdminCards
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function card_history(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $validator = Validator::make(['id' => $id], ['id' => 'exists:cards,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $data = CardHistory::where('card_id', $id)->orderBy('created_at')->get();
            foreach ($data as &$item) {
                $item->data = json_decode($item->data);
            }
        }
        return response()->json(['errors' => [], 'data' => $data], $httpStatus);
    }

    /**
     * @api {patch} /api/cards/switch_status/:id Switch Status
     * @apiName SwitchStatus
     * @apiGroup AdminCards
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function switch_card_status($id)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $validator = Validator::make(['id' => $id], ['id' => 'exists:cards,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $card = Cards::where('id', $id)->first();
            $card->status = !$card->status;
            $card->save();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {patch} /api/cards/attach_user/:number Attach / Detach User To Card
     * @apiName AttachCardUser
     * @apiGroup AdminCards
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} [user_id]
     */

    public function card_attach_user(Request $request, $number = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;

        $messages = [
            'number.exists' => 'Указанная карта либо не существует либо привязана к другому пользователю.'
        ];
        $validatorData = array_merge($request->all(), ['number' => $number]);
        $validator = Validator::make($validatorData, [
            'number' => 'exists:cards,number' . ($request->user_id ? ',user_id,NULL' : ''),
            'user_id' => 'nullable|exists:users,id'
        ], $messages);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $card = Cards::where('number', $number)->first();
            $user = Users::where('id', $request->user_id)->first();
            $userId = $request->user_id ?: null;
            $userPhone = $request->user_id ? $user->phone : null;
            $card->user_id = $userId;
            $card->phone = $userPhone;
            Sales::where('card_id', '=', $card->id)->update(['user_id' => $userId]);
            $card->save();
            $data = [$card];
            DataHelper::collectCardsInfo($data);
            $data = $data[0];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {patch} /api/bills/edit_value/:id Edit Bill Value
     * @apiName EditBillValue
     * @apiGroup AdminCards
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} value
     */

    public function edit_bill_value(Request $request, $id)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $validatorData = array_merge($request->all(), ['id' => $id]);
        $validator = Validator::make($validatorData, [
            'id' => 'exists:bills,id',
            'value' => 'required|regex:/^\d+(\.\d+)?$/',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $bill = Bills::where('id', $id)->first();
            $bill->value = $request->value;
            $bill->save();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }
}
