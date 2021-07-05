<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Baskets;
use App\Models\BillPrograms;
use App\Models\Bills;
use App\Models\BillTypes;
use App\Models\Cards;
use App\Models\Categories;
use App\Models\CommonActions;
use App\Models\DataHelper;
use App\Models\News;
use App\Models\Orders;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Sales;
use App\Models\Stocks;
use App\Models\Users;
use App\Models\Fields;
use App\Models\FieldsUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
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
            $user = Users::where([['type', '=', 0], ['phone', '=', $phone], ['password', '=', md5($request->password)]])->first();
            if (empty($user)) {
                $errors['user'] = 'User not found';
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
     * @apiParam {string} password
     * @apiParam {string} phone
     * @apiParam {integer=0,1,2} type
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
     * @apiParam {string} [password]
     * @apiParam {string} [phone]
     * @apiParam {integer=0,1,2} [type]
     */

    public function edit_user(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $user = null;
        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validatorRules = [];
        if(!$id) {
            $validatorRules['first_name'] = 'required';
            $validatorRules['second_name'] = 'required';
            $validatorRules['password'] = 'required';
            $validatorRules['phone'] = 'required';
        } else {
            $validatorRules['id'] = 'exists:users,id';
        }
        $validatorRules['type'] = (!$id ? 'required|' : '') . 'in:0,1';

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            if ($request->phone) $request->phone = str_replace(array("(", ")", " ", "-"), "", $request->phone);
            $user = $id ? Users::where('id', '=', $id)->first() : new Users;
            foreach (['first_name', 'second_name', 'phone', 'type'] as $field) {
                if (isset($request->{$field})) $user->{$field} = $request->{$field};
            }
            if ($request->password) $user->password = md5($request->password);
            if (!$id) $user->token = sha1(microtime() . 'salt' . time());
            $user->save();

            if(!$id && ($request->type == 1)) {
                $userId = $user->id;
                foreach (Fields::all() as $field) {
                    $fieldsUser = new FieldsUsers;
                    $fieldsUser->field_id = $field->id;
                    $fieldsUser->user_id = $userId;
                    $fieldsUser->save();
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
            $query = Users::select('*');
            if ($id) $query->where('id', '=', $id);
            else {
                $count = $query->count();
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
            $list = $query->get()->toArray();
            DataHelper::collectUsersInfo($list);
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
        $validator = Validator::make(['id' => $id], ['id' => 'exists:users,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $cards = DB::table('cards')->select('cards.id', 'bills.id as bills_id', 'bill_programs.id as bill_programs_id')
                ->leftJoin('bills', 'bills.card_id', '=', 'cards.id')
                ->leftJoin('bill_programs', 'bill_programs.bill_id', '=', 'bills.id')->get()->toArray();
            $cardsIds = array_unique(array_column($cards, 'id'));
            $billsIds = array_unique(array_column($cards, 'bills_id'));
            $billsIds = array_filter($billsIds, function($value) {return !is_null($value) && $value !== '';});
            Bills::whereIn('id', $billsIds)->delete();
            Cards::whereIn('id', $cardsIds)->delete();
            FieldsUsers::where('user_id', '=', $id)->delete();
            Users::where('id', '=', $id)->delete();
        }
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
     * @apiParam {integer} user_id
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
     */

    public function edit_card(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $card = null;
        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validatorRules = [];
        if(!$id) $validatorRules['number'] = 'required';
        else $validatorRules['id'] = 'exists:cards,id';
        $validatorRules['user_id'] = ($id ? 'required|' : '') . 'exists:users,id';

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $card = $id ? Cards::where('id', '=', $id)->first() : new Cards;
            if ($request->user_id) $card->user_id = $request->user_id;
            if ($request->number) $card->number = $request->number;
            $card->save();

            if (!$id) {
                $cardId = $card->id;
                foreach (BillTypes::all() as $billType) {
                    $bill = new Bills;
                    $bill->card_id = $cardId;
                    $bill->bill_type_id = $billType->id;
                    $bill->save();
                }
            }
        }
        return response()->json(['errors' => $errors, 'data' => $card], $httpStatus);
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
        $validator = Validator::make(['id' => $id], ['id' => 'exists:cards,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $bills = Bills::where('card_id', '=', $id);
            $bills->delete();
            Cards::where('id', '=', $id)->delete();
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

    /**
     * @api {post} /api/bill_programs/list/:bill_id Get Bill Programs List for Bill
     * @apiName GetBillProgramsListForBill
     * @apiGroup AdminBillPrograms
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     */

    public function list_bill_programs(Request $request, $billId = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;

        $validatorData = $request->all();
        if ($billId) $validatorData = array_merge($validatorData, ['bill_id' => $billId]);
        if (!$billId) {
            $validatorRules = [
                'dir' => 'in:asc,desc',
                'order' => 'in:id,bill_id,from,to,percent,created_at,updated_at',
                'offset' => 'integer',
                'limit' => 'integer',
            ];
        } else {
            $validatorRules = ['bill_id' => 'exists:bills,id'];
        }
        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }

        if (empty($errors)) {
            $query = BillPrograms::select('*');
            if ($billId) $query->where('bill_id', '=', $billId);

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
        if (empty($errors)) BillPrograms::where('id', '=', $id)->delete();
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
            'phone' => (!$id ? 'required|' : '') . 'unique:outlets',
            'lon' => (!$id ? 'required|' : '') . 'regex:/^\d+(\.\d+)?$/',
            'lat' => (!$id ? 'required|' : '') . 'regex:/^\d+(\.\d+)?$/',
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
     * @api {post} /api/fields/create Create Field
     * @apiName CreateField
     * @apiGroup AdminFields
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} name
     */

    /**
     * @api {post} /api/fields/edit/:id Edit Field
     * @apiName EditField
     * @apiGroup AdminFields
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [name]
     */

    public function edit_field(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $field = null;
        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validatorRules = [];
        if(!$id) {
            $validatorRules['name'] = 'required';
        } else $validatorRules['id'] = 'exists:fields,id';

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $field = $id ? Fields::where('id', '=', $id)->first() : new Fields;
            if ($request->name) $field->name = $request->name;
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
     * @api {post} /api/sales/list Get Sales List
     * @apiName GetSalesList
     * @apiGroup AdminSales
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     */

    /**
     * @api {get} /api/sales/get/:id Get Sale
     * @apiName GetSale
     * @apiGroup AdminSales
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_sales(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;

        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        if (!$id) {
            $validatorRules = [
                'dir' => 'in:asc,desc',
                'order' => 'in:id,user_id,outlet_id,bill_id,card_id,outlet_name,dt,amount,created_at,updated_at',
                'offset' => 'integer',
                'limit' => 'integer',
            ];
        } else {
            $validatorRules = ['id' => 'exists:sales,id'];
        }
        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }

        if (empty($errors)) {
            $count = 0;
            $query = Sales::select('sales.*',
                'users.id as user_id', 'users.phone as users_phone',
                'outlets.id as outlet_id', 'outlets.name as outlet_name',
                'cards.id as card_id', 'cards.number as card_number')
                ->join('users', 'users.id', '=', 'sales.user_id')
                ->join('outlets', 'outlets.id', '=', 'sales.outlet_id')
                ->join('cards', 'cards.id', '=', 'sales.card_id');

            if ($id) $query->where('sales.id', '=', $id);
            else {
                $count = $query->count();
                $order = $request->order ?: 'sales.id';
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
                $fileName = uniqid() . ".jpeg";
                Storage::disk('local')->put("images/$fileName", '');
                $path = Storage::path("images/$fileName");
                $imageTmp = imagecreatefromstring(base64_decode($request->file_content));
                imagejpeg($imageTmp, $path);
                imagedestroy($imageTmp);
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

        $data = Categories::where('parent_id', '=', 0)->get();
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
        $validator = Validator::make(['id' => $id], ['id' => 'exists:categories,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $query = Categories::where('parent_id', '=', $id);
            $subCategoriesIds = array_column($query->get()->toArray(), 'id');
            $productsQuery = Product::whereIn('category_id', $subCategoriesIds);
            foreach ($productsQuery->get() as $item) {
                @unlink(Storage::path("images/{$item->file}"));
            }
            Product::whereIn('category_id', $subCategoriesIds)->delete();
            $query->delete();
            Categories::where('id', '=', $id)->delete();
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
     * @apiParam {string} name
     * @apiParam {string} description
     * @apiParam {integer} price
     * @apiParam {integer} file_content
     * @apiParam {integer} [is_hit]
     * @apiParam {integer} [is_novelty]
     */

    /**
     * @api {post} /api/products/edit/:id Edit Product
     * @apiName EditProduct
     * @apiGroup AdminProducts
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} [category_id]
     * @apiParam {string} [name]
     * @apiParam {string} [description]
     * @apiParam {integer} [price]
     * @apiParam {integer} [file_content]
     * @apiParam {integer} [is_hit]
     * @apiParam {integer} [is_novelty]
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
        $validatorRules['category_id'] = (!$id ? 'required|' : '') . 'exists:categories,id';
       // $validatorRules['outlet_id'] = (!$id ? 'required|' : '') . 'exists:outlets,id';
        $validatorRules['price'] = 'integer';
        $validatorRules['is_hit'] = 'in:0,1,true,false';
        $validatorRules['is_novelty'] = 'in:0,1,true,false';

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $product = $id ? Product::where('id', '=', $id)->first() : new Product;
         //   if (isset($request->outlet_id)) $product->outlet_id = $request->outlet_id;
            if (isset($request->category_id)) $product->category_id = $request->category_id;
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
     */

    public function list_products(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;

        $validatorRules = [
            'dir' => 'in:asc,desc',
            'order' => 'in:id,category_id,name,description,file,price,created_at,updated_at',
            'offset' => 'integer',
            'limit' => 'integer',
           // 'outlet_id' => 'exists:outlets,id',
            'category_id' => 'exists:categories,id',
        ];
        $validator = Validator::make($request->all(), $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $products = Product::select('products.*', /*'outlets.name as outlet_name',*/ 'categories.name as category_name')
               // ->leftJoin('outlets', 'outlets.id', '=', 'products.outlet_id')
                ->leftJoin('categories', 'categories.id', '=', 'products.category_id');
            if (isset($request->category_id)) {
                if(Categories::where('id', '=', $request->category_id)->value('parent_id') == 0) {
                    $categories = Categories::where('parent_id', '=', $request->category_id)->get()->toArray();
                    $categoriesIds = array_column($categories, 'id');
                    if(!empty($categoriesIds)) {
                        $products->whereIn('category_id', $categoriesIds);
                    }
                } else {
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
        $validator = Validator::make(['id' => $id], ['id' => 'exists:products,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $query = Product::where('id', '=', $id);
            @unlink(Storage::path("images/{$query->first()->file}"));
            $query->delete();
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
     * @apiParam {string} address
     * @apiParam {object[]} products
     */

    /**
     * @api {post} /api/orders/create/:id Edit Order
     * @apiName EditOrder
     * @apiGroup AdminOrders
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} [address]
     */

    public function edit_order(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $order = null;
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
        if (!$id) {
            $validatorRules['address'] = 'required';
            $validatorRules['products'] = 'required|array';
            $validatorRules['products.*'] = 'check_product';
            $validatorRules['user_id'] = 'required|check_user';
        }

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $order = $id ? Orders::where('id', '=', $id)->first() : new Orders;
            if (isset($request->address)) $order->address = $request->address;
            if (!$id) {
                $order->user_id = $request->user_id;
                $productsIds = array_column($request->products, 'product_id');
                $productsMap = [];
                foreach (Product::whereIn('id', $productsIds)->get() as $item) {
                    $productsMap[$item->id] = $item->price;
                }
                $amount = 0;
                foreach ($request->products as $product) {
                    $amount += $productsMap[$product['product_id']] * $product['count'];
                }
                $order->amount = $order->amount_now = $amount;
                $order->status = 0;
                $order->save();
                foreach ($request->products as $product) {
                    $basket = new Baskets;
                    $basket->order_id = $order->id;
                    $basket->product_id = $product['product_id'];
                    $basket->amount = $productsMap[$product['product_id']];
                    $basket->count = $product['count'];
                    $basket->save();
                }
            }
        }
        return response()->json(['errors' => $errors, 'data' => $order], $httpStatus);
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
                'order' => 'in:id,address,status,amount,amount_now,created_at,updated_at',
                'offset' => 'integer',
                'limit' => 'integer',
            ];
        } else {
            $validatorRules = ['id' => 'exists:orders,id'];
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
            $orders = Orders::select('orders.*', 'users.first_name as user_first_name', 'users.second_name as user_second_name')
                ->leftJoin('users', 'users.id', '=', 'orders.user_id');
            if (!$id) {
                $count = $orders->count();

                $order = $request->order ?: 'orders.id';
                $dir = $request->dir ?: 'asc';
                $offset = $request->offset;
                $limit = $request->limit;

                $orders->orderBy($order, $dir);
                if ($limit) {
                    $orders->limit($limit);
                    if ($offset) $orders->offset($offset);
                }
            } else $orders->where('orders.id', '=', $id);

            $list = $orders->get()->toArray();
            $ordersIds = array_column($list, 'id');
            $basketsMap = [];
			$baskets = Baskets::select('baskets.*', 'products.name as product_name')
				->leftJoin('products', 'products.id', '=', 'baskets.product_id')
				->whereIn('order_id', $ordersIds)->get();
            foreach ($baskets as $basket) {
                if(!isset($basketsMap[$basket->order_id])) $basketsMap[$basket->order_id] = [];
                $basketsMap[$basket->order_id][] = $basket->toArray();
            }
            foreach ($list as &$item) {
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
        $validator = Validator::make(['id' => $id], ['id' => 'exists:orders,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            Baskets::where('order_id', '=', $id)->delete();
            Orders::where('id', '=', $id)->delete();
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
            $orderId = Baskets::where('id', '=', $id)->value('order_id');
            Baskets::where('id', '=', $id)->delete();
            $amount = 0;
            foreach (Baskets::where('order_id', '=', $orderId)->get() as $item) {
                $amount += $item->amount * $item->count;
            }
            $order = Orders::where('id', '=', $orderId)->first();
            $order->amount = $order->amount_now = $amount;
            $order->save();
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

            $orderId = $basket->order_id;
            $order = Orders::where('id', '=', $orderId)->first();
            $amount = 0;
            foreach (Baskets::where('order_id', '=', $orderId)->get() as $item) {
                $amount += $item->amount * $item->count;
            }
            $order->amount = $order->amount_now = $amount;
            $order->save();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {post} /api/orders/add_basket/:order_id Add Basket
     * @apiName AddBasket
     * @apiGroup AdminOrders
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} count
     * @apiParam {integer} product_id
     */

    public function add_basket(Request $request, $orderId)
    {
        $errors = [];
        $httpStatus = 200;

        $validatorData = array_merge($request->all(), ['order_id' => $orderId]);
        $validator = Validator::make($validatorData,
            ['order_id' => 'exists:orders,id', 'count' => 'required|integer', 'product_id' => 'required|exists:products,id']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $basket = new Baskets;
            $basket->order_id = $orderId;
            $basket->product_id = $request->product_id;
            $basket->count = $request->count;
            $basket->amount = Product::where('id', '=', $request->product_id)->first()->price;
            $basket->save();

            $order = Orders::where('id', '=', $orderId)->first();
            $amount = 0;
            foreach (Baskets::where('order_id', '=', $orderId)->get() as $item) {
                $amount += $item->amount * $item->count;
            }
            $order->amount = $order->amount_now = $amount;
            $order->save();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
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
}
