<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Baskets;
use App\Models\BillPrograms;
use App\Models\Bills;
use App\Models\DataHelper;
use App\Models\Product;
use App\Models\Sales;
use App\Models\Users;
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
     * @apiParam {object[]} products
     */

    /**
     * @api {post} /api/outlets/sales/edit/:sale_id Edit Sale
     * @apiName EditSale
     * @apiGroup OutletSales
     *
     * @apiParam {integer} [outlet_id]
     * @apiParam {integer} bill_id
     * @apiParam {object[]} [products]
     */

    public function edit_sale(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $sale = null;
        Validator::extend('check_product', function($attribute, $value, $parameters, $validator) {
            if (!isset($value['count']) || !is_integer($value['count']) || $value['count'] === 0)
                return false;
            return Product::where('id', '=', $value['product_id'])->exists();
        });
        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validator = Validator::make($validatorData,
            [
                'outlet_id' => (!$id ? 'required|' : '') . 'exists:outlets,id',
                'bill_id' => (!$id ? 'required|' : '') . 'exists:bills,id',
                'user_id' => (!$id ? 'required|' : '') . 'exists:users,id',
                'id' => 'exists:sales,id',
                'products' => (!$id ? 'required|' : '') . 'array',
                'products.*' => 'check_product'
            ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $currentAmount = Sales::where('bill_id', '=', $request->bill_id)->sum('amount');

            $sale = $id ? Sales::where('id', '=', $id)->first() : new Sales;
            if(isset($request->user_id)) $sale->user_id = $request->user_id;
            if(isset($request->outlet_id)) $sale->outlet_id = $request->outlet_id;
            if(isset($request->bill_id)) $sale->card_id = Bills::where('id', '=', $request->bill_id)->first()->card_id;
            if(isset($request->bill_id)) $sale->bill_id = $request->bill_id;
            if(isset($request->amount)) $sale->amount = $request->amount;
            if (!$id) $sale->dt = date('Y-m-d H:i:s');
            $sale->status = Sales::STATUS_COMPLETED;

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

            $program = null;
            foreach (BillPrograms::all() as $row) {
                if ($currentAmount >= $row->from && $currentAmount <= $row->to) {
                    $program = $row;
                    break;
                }
            }
            $bill = Bills::where('id', '=', $id ? $sale->bill_id : $request->bill_id)->first();
            $currentFrom = 0;
            if ($program) {
                $currentFrom = $program->from;
                $sale->bill_program_id = $program->id;
                $bill->bill_program_id = $program->id;
                $bill->value = floatval($bill->value) + $program->percent * 0.01 * $sale->amount;
            }
            $nextFrom = BillPrograms::where('from', '>', $currentFrom)->min('from');
            $bill->remaining_amount = $nextFrom - $currentAmount;
            $bill->save();
            $sale->save();
        }
        return response()->json(['errors' => $errors, 'data' => $sale], $httpStatus);
    }

    /**
     * @api {post} /api/outlets/users/list Get Users List
     * @apiName GetUsersList
     * @apiGroup OutletSales
     *
     * @apiParam {string} [like]
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     */

    public function list_users(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $order = $request->order ?: 'users.id';
        $dir = $request->dir ?: 'desc';
        $offset = $request->offset;
        $limit = $request->limit;
        $like = $request->like;

        $query = Users::select('*');
        $query->orderBy($order, $dir);
        if ($limit) {
            $query->limit($limit);
            if ($offset) $query->offset($offset);
        }
        if ($like) {
            $query->orWhere('phone', 'like', '%'.str_replace(array("(", ")", " ", "-"), "", $like).'%');
        }
        $data = $query->get()->toArray();
        DataHelper::collectUsersInfo($data);
        return response()->json([
            'errors' => $errors,
            'data' => [
                'count' => Users::count(),
                'list' => $data
            ]], $httpStatus);
    }

    /**
     * @api {post} /api/outlets/users/find_by_phone Find User By Phone
     * @apiName FindUserByPhone
     * @apiGroup OutletSales
     *
     * @apiParam {string} phone
     */

    public function find_user_by_phone(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $phone = str_replace(array("(", ")", " ", "-"), "", $request->phone);

        Validator::extend('phone_exists', function($attribute, $value, $parameters, $validator) {
            return Users::where([['type', '=', 1],['phone', '=', $value]])->exists();
        });

        $validator = Validator::make(['phone' => $phone], ['phone' => "required|phone_exists:$phone"]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }

        if (empty($errors)) {
            $query = Users::select('*');
            $query->where('phone', '=', $phone);
            $data = $query->get()->toArray();
            DataHelper::collectUsersInfo($data);
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }
}
