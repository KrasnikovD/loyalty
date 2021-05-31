<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BillPrograms;
use App\Models\Bills;
use App\Models\BillTypes;
use App\Models\Cards;
use App\Models\CommonActions;
use App\Models\Outlet;
use App\Models\Sales;
use App\Models\Users;
use App\Models\Fields;
use App\Models\FieldsUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        return response()->json(array('errors' => $errors, 'data' => $user), $httpStatus);
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
        return response()->json(array('errors' => $errors, 'data' => $billType), $httpStatus);
    }

    /**
     * @api {get} /api/bill_types/list Get Bill Type List
     * @apiName GetBillTypeList
     * @apiGroup AdminBillTypes
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    /**
     * @api {get} /api/bill_types/get/:id Get Bill Type
     * @apiName GetBillType
     * @apiGroup AdminBillTypes
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_bill_types($id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        if($id) {
            Validator::extend('not_default', function($attribute, $value, $parameters, $validator) {
                $billType = BillTypes::where([['name', '=', 'default'],['id', '=', $value]])->first();
                return empty($billType);
            });

            $validator = Validator::make(['id' => $id], ['id' => 'exists:bill_types,id|not_default']);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $httpStatus = 400;
            }
        }
        if (empty($errors)) {
            $query = BillTypes::where('name', '<>', 'default');
            if ($id) $query->where('id', '=', $id);
            $data = $query->get();
        }
        return response()->json(array('errors' => $errors, 'data' => $data), $httpStatus);
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
        return response()->json(array('errors' => $errors, 'data' => null), $httpStatus);
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

            if(!$id) {
                $userId = $user->id;
                foreach (Fields::all() as $field) {
                    $fieldsUser = new FieldsUsers;
                    $fieldsUser->field_id = $field->id;
                    $fieldsUser->user_id = $userId;
                    $fieldsUser->save();
                }
            }
        }
        return response()->json(array('errors' => $errors, 'data' => $user), $httpStatus);
    }

    /**
     * @api {get} /api/users/list Get Users List
     * @apiName GetUsersList
     * @apiGroup AdminUsers
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    /**
     * @api {get} /api/users/get/:id Get User
     * @apiName GetUser
     * @apiGroup AdminUsers
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_users($id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        if($id) {
            $validator = Validator::make(['id' => $id], ['id' => 'exists:users,id']);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $httpStatus = 400;
            }
        }
        if (empty($errors)) {
            $query = Users::select('*');
            if ($id) $query->where('id', '=', $id);
            $data = $query->get();
            $usersIds = array_column($data->toArray(), 'id');

            $cardList = Cards::select('id', 'number', 'user_id')->whereIn('user_id', $usersIds)->get();
            $cardsIds = array_column($cardList->toArray(), 'id');
            $billsList = Bills::join('bill_types', 'bills.bill_type_id', '=', 'bill_types.id')
                ->select('bills.id', 'bills.value', 'bills.card_id', 'bill_types.name')
                ->whereIn('bills.card_id', $cardsIds)->get();
            $billsIds = array_column($billsList->toArray(), 'id');

            $billsProgramsList = BillPrograms::select('id', 'bill_id', 'from', 'to', 'percent')
                ->whereIn('bill_id', $billsIds)->get();
            $billsProgramsMap = [];
            foreach ($billsProgramsList as $billsProgram) {
                if(!isset($billsProgramsMap[$billsProgram['bill_id']])) $billsProgramsMap[$billsProgram['bill_id']] = [];
                $billsProgramsMap[$billsProgram['bill_id']][] = $billsProgram->toArray();
            }

            foreach ($billsList as &$billItem) {
                $billItem->programs = @$billsProgramsMap[$billItem->id];
            }

            $billsMap = [];
            foreach ($billsList as $bill) {
                if(!isset($billsMap[$bill['card_id']])) $billsMap[$bill['card_id']] = [];
                $billsMap[$bill['card_id']][] = $bill->toArray();
            }

            foreach ($cardList as &$cardItem) {
                $cardItem->bills = @$billsMap[$cardItem->id];
            }

            $cardMap = [];
            foreach ($cardList as $card) {
                if(!isset($cardMap[$card['user_id']])) $cardMap[$card['user_id']] = [];
                $cardMap[$card['user_id']][] = $card->toArray();
            }

            foreach ($data as &$item) {
                $item->cardList = @$cardMap[$item->id];
            }

        }
        return response()->json(array('errors' => $errors, 'data' => $data), $httpStatus);
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
            Users::where('id', '=', $id)->delete();
        }
        return response()->json(array('errors' => $errors, 'data' => null), $httpStatus);
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
        return response()->json(array('errors' => $errors, 'data' => $card), $httpStatus);
    }

    /**
     * @api {get} /api/cards/list Get Cards List
     * @apiName GetCardsList
     * @apiGroup AdminCards
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    /**
     * @api {get} /api/cards/get/:id Get Card
     * @apiName GetCard
     * @apiGroup AdminCards
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_cards($id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        if($id) {
            $validator = Validator::make(['id' => $id], ['id' => 'exists:cards,id']);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $httpStatus = 400;
            }
        }
        if (empty($errors)) {
            $query = Cards::select('*');
            if ($id) $query->where('id', '=', $id);
            $data = $query->get();
        }
        return response()->json(array('errors' => $errors, 'data' => $data), $httpStatus);
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
            $ids = array_column($bills->get()->toArray(), 'id');
            BillPrograms::whereIn('bill_id', $ids)->delete();
            $bills->delete();
            Cards::where('id', '=', $id)->delete();
        }
        return response()->json(array('errors' => $errors, 'data' => null), $httpStatus);
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
     * @apiParam {integer} bill_id
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
     * @apiParam {integer} [bill_id]
     */

    public function edit_bill_program(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $billProgram = null;
        $billId = @$request->bill_id;
        $from = @$request->from;
        $to = @$request->to;
        if($id) {
            $bill = BillPrograms::where('id', '=', $id)->first();
            if (!isset($billId)) $billId = $bill->bill_id;
            if (!isset($from)) $from = $bill->from;
            if (!isset($to)) $to = $bill->to;
        }
        Validator::extend('from_to_valid', function($attribute, $value, $parameters, $validator) {
            if (!empty($parameters[0]) && (!empty($parameters[1]) || $parameters[1] === '0') && !empty($parameters[2])) {
                list($billId, $from, $to) = $parameters;
                if ($from >= $to) return false;
                $q = BillPrograms::where('bill_id', '=', $billId);
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
        Validator::extend('is_default', function($attribute, $value, $parameters, $validator) {
            $name = Bills::join('bill_types', 'bills.bill_type_id', '=', 'bill_types.id')
                ->where('bills.id', '=', $value)->first()->name;
            return $name == 'default';
        });
        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        if (isset($from) && isset($to)) $validatorData = array_merge($validatorData, ['section' => $id]);
        $validator = Validator::make($validatorData,
            [
                'from' => (!$id ? 'required|' : '') . "integer",
                'to' => (!$id ? 'required|' : '') . "integer",
                'percent' => (!$id ? 'required|' : '') . 'integer|max:100',
                'bill_id' => (!$id ? 'required|' : '') . 'exists:bills,id|is_default',
                'id' => 'exists:bill_programs,id',
                'section' => "from_to_valid:{$billId},{$from},{$to}"
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
            if(isset($request->bill_id)) $billProgram->bill_id = $request->bill_id;
            $billProgram->save();
        }
        return response()->json(array('errors' => $errors, 'data' => $billProgram), $httpStatus);
    }

    /**
     * @api {get} /api/bill_programs/list Get Bill Programs List
     * @apiName GetBillProgramsList
     * @apiGroup AdminBillPrograms
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    /**
     * @api {get} /api/bill_programs/list/:bill_id Get Bill Programs List for Bill
     * @apiName GetBillProgramsListForBill
     * @apiGroup AdminBillPrograms
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_bill_programs($billId = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;

        if ($billId) {
            $validator = Validator::make(['bill_id' => $billId], ['bill_id' => 'exists:bills,id']);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $httpStatus = 400;
            }
        }
        if (empty($errors)) {
            $query = BillPrograms::select('*');
            if ($billId) $query->where('bill_id', '=', $billId);
            $data = $query->get();
        }
        return response()->json(array('errors' => $errors, 'data' => $data), $httpStatus);
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
        return response()->json(array('errors' => $errors, 'data' => $bill), $httpStatus);
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
        return response()->json(array('errors' => $errors, 'data' => null), $httpStatus);
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
     * @apiParam {string} address
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
     * @apiParam {string} [address]
     */

    public function edit_outlet(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $outlet = null;
        $validatorData = $request->all();
        if ($request->phone) $validatorData['phone'] = str_replace(array("(", ")", " ", "-"), "", $request->phone);
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validatorRules = [];
        if(!$id) {
            $validatorRules['name'] = 'required';
            $validatorRules['phone'] = 'required|unique:outlets';
            $validatorRules['address'] = 'required';
        } else $validatorRules['id'] = 'exists:outlets,id';

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $outlet = $id ? Outlet::where('id', '=', $id)->first() : new Outlet;
            if ($request->name) $outlet->name = $request->name;
            if ($request->phone) $outlet->phone = str_replace(array("(", ")", " ", "-"), "", $request->phone);
            if ($request->address) $outlet->address = $request->address;
            $outlet->save();
        }
        return response()->json(array('errors' => $errors, 'data' => $outlet), $httpStatus);
    }

    /**
     * @api {get} /api/outlets/list Get Outlets List
     * @apiName GetOutletsList
     * @apiGroup AdminOutlets
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    /**
     * @api {get} /api/outlets/get/:id Get Outlet
     * @apiName GetOutlet
     * @apiGroup AdminOutlets
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_outlets($id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        if($id) {
            $validator = Validator::make(['id' => $id], ['id' => 'exists:outlets,id']);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $httpStatus = 400;
            }
        }
        if (empty($errors)) {
            $query = Outlet::select('*');
            if ($id) $query->where('id', '=', $id);
            $data = $query->get();
        }
        return response()->json(array('errors' => $errors, 'data' => $data), $httpStatus);
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
        return response()->json(array('errors' => $errors, 'data' => null), $httpStatus);
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
        return response()->json(array('errors' => $errors, 'data' => $field), $httpStatus);
    }

    /**
     * @api {get} /api/fields/list Get Fields List
     * @apiName GetFieldsList
     * @apiGroup AdminFields
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    /**
     * @api {get} /api/fields/get/:id Get Field
     * @apiName GetField
     * @apiGroup AdminFields
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_fields($id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        if($id) {
            $validator = Validator::make(['id' => $id], ['id' => 'exists:fields,id']);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $httpStatus = 400;
            }
        }
        if (empty($errors)) {
            $query = Fields::select('*');
            if ($id) $query->where('id', '=', $id);
            $data = $query->get();
        }
        return response()->json(array('errors' => $errors, 'data' => $data), $httpStatus);
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
        return response()->json(array('errors' => $errors, 'data' => null), $httpStatus);
    }

    /**
     * @api {get} /api/sales/list Get Sales List
     * @apiName GetSalesList
     * @apiGroup AdminSales
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    /**
     * @api {get} /api/sales/get/:id Get Sale
     * @apiName GetSale
     * @apiGroup AdminSales
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_sales($id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        if($id) {
            $validator = Validator::make(['id' => $id], ['id' => 'exists:sales,id']);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $httpStatus = 400;
            }
        }
        if (empty($errors)) {
            $query = Sales::select('sales.*',
                'users.id as user_id', 'users.phone as users_phone',
                'outlets.id as outlet_id', 'outlets.name as outlet_name',
                'cards.id as card_id', 'cards.number as card_number')
                ->join('users', 'users.id', '=', 'sales.user_id')
                ->join('outlets', 'outlets.id', '=', 'sales.outlet_id')
                ->join('cards', 'cards.id', '=', 'sales.card_id');

            if ($id) $query->where('sales.id', '=', $id);
            $data = $query->get();
        }
        return response()->json(array('errors' => $errors, 'data' => $data), $httpStatus);
    }

    /*public function edit_sale(Request $request, $id = null)
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
    }*/
}
