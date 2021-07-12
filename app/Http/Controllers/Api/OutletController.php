<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Baskets;
use App\Models\BillPrograms;
use App\Models\Bills;
use App\Models\BillTypes;
use App\Models\BonusHistory;
use App\Models\Cards;
use App\Models\Coupons;
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
     * @apiParam {string} card_number
     * @apiParam {object[]} products
     */

    /**
     * @api {post} /api/outlets/sales/edit/:sale_id Edit Sale
     * @apiName EditSale
     * @apiGroup OutletSales
     *
     * @apiParam {string} card_number
     * @apiParam {object[]} [products]
     */

    public function edit_sale(Request $request, $saleId = null)
    {
        $errors = [];
        $httpStatus = 200;
        $sale = null;
        Validator::extend('check_product', function($attribute, $value, $parameters, $validator) {
            if (isset($value['count']) && (!is_integer($value['count']) || $value['count'] === 0))
                return false;
            if (empty($value['coupon_id']) && empty($value['product_id']))
                return false;
            if (isset($value['coupon_id']) && isset($value['product_id']))
                return false;
            if (!empty($value['coupon_id'])) {
                $saleId = $parameters[1];
                $userId = Cards::where('number', '=', $parameters[0])->value('user_id');
                $validateData = [['id', '=', $value['coupon_id']], ['user_id', '=', $userId]];
                if (empty($saleId)) $validateData[] = ['count', '>', 0];
                else {
                    if (!Baskets::where([['coupon_id', '=', $value['coupon_id']],
                        ['sale_id', '=', $saleId]])->exists())
                        $validateData[] = ['count', '>', 0];
                }
                return Coupons::where($validateData)->exists();
            } else
                return Product::where('id', '=', $value['product_id'])->exists();
        });
        Validator::extend('check_sale', function($attribute, $value, $parameters, $validator) {
            return @Sales::where('id', '=', $value)->first()->status == Sales::STATUS_PRE_ORDER;
        });
        $validatorData = $request->all();
        if ($saleId) $validatorData = array_merge($validatorData, ['sale_id' => $saleId]);
        $validator = Validator::make($validatorData,
            [
                'card_number' => 'required|exists:cards,number',
                'outlet_id' => (!$saleId ? 'required|' : '') . 'exists:outlets,id',
                'sale_id' => 'exists:sales,id|check_sale',
                'products' => (!$saleId ? 'required|' : '') . 'array',
                'products.*' => 'check_product:' . $request->card_number .',' . $saleId,
            ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $cardInfo = Cards::select('cards.id', 'user_id', 'bills.id as bill_id')
                ->join('bills', 'bills.card_id', '=', 'cards.id')
                ->join('bill_types', 'bill_types.id', '=', 'bills.bill_type_id')
                ->where([['number', '=', $request->card_number], ['bill_types.name', '=', BillTypes::TYPE_DEFAULT]])->first();

            $currentAmount = Sales::where([['bill_id', '=', $cardInfo->bill_id], ['status', '=', Sales::STATUS_COMPLETED]])->sum('amount');

            $sale = $saleId ? Sales::where('id', '=', $saleId)->first() : new Sales;
            if(isset($request->outlet_id)) $sale->outlet_id = $request->outlet_id;
            $sale->user_id = $cardInfo->user_id;
            $sale->card_id = $cardInfo->id;
            $sale->bill_id = $cardInfo->bill_id;
            if (!$saleId) $sale->dt = date('Y-m-d H:i:s');
            $sale->status = Sales::STATUS_COMPLETED;

            if (!empty($request->products)) {
                $amount = 0;
                $existedCouponsIds = [];
                $existedBasketProductMap = [];
                if (!empty($saleId)) {
                    $amount = $sale->amount;
                    $savedBasket = Baskets::where('sale_id', '=', $saleId)->get();
                    $inputCouponsIds = array_column($request->products, 'coupon_id');
                    $inputProductsIds = array_column($request->products, 'product_id');
                    $couponsBasketsToDelete = [];
                    $productsBasketsToDelete = [];
                    foreach ($savedBasket as $item) {
                        if ($item->coupon_id) {
                            if (in_array($item->coupon_id, $inputCouponsIds))
                                $existedCouponsIds[] = $item->coupon_id;
                            else {
                                $couponsBasketsToDelete[] = $item->id;
                                $coupon = Coupons::where('id', '=', $item->coupon_id)->first();
                                $coupon->count += $item->count;
                                $coupon->save();
                            }
                        } else {
                            if (in_array($item->product_id, $inputProductsIds))
                                $existedBasketProductMap[$item->product_id] = $item;
                            else {
                                $productsBasketsToDelete[] = $item->id;
                                $amount -= $item->amount * $item->count;
                            }
                        }
                    }
                    $idsToDelete = array_merge($couponsBasketsToDelete, $productsBasketsToDelete);
                    if (!empty($idsToDelete)) Baskets::whereIn('id', $idsToDelete)->delete();
                }
                $sale->save();
                $priceMap = [];
                if ($saleId) {
                    foreach (Product::whereIn('id', array_column($existedBasketProductMap, 'product_id'))->get() as $item)
                        $priceMap[$item->id] = $item->price;
                } else {
                    foreach (Product::whereIn('id', array_column($request->products, 'product_id'))->get() as $item)
                        $priceMap[$item->id] = $item->price;
                }

                foreach ($request->products as $product) {
                    if (isset($product['coupon_id']) && in_array($product['coupon_id'], $existedCouponsIds))
                        continue;
                    if (isset($product['product_id']) && $product['count'] == @$existedBasketProductMap[$product['product_id']]->count)
                        continue;
                    $basket = null;
                    if (isset($product['product_id'])) {
                        $basket = @$existedBasketProductMap[$product['product_id']];
                        if (isset($basket)) {
                            if ($product['count'] < $basket->count) {
                                $amount -= ($basket->count - $product['count']) * $basket->amount;
                                $basket->count = $product['count'];
                            } else {
                                $amount += ($product['count'] - $basket->count) * $priceMap[$product['product_id']];
                                if ($priceMap[$product['product_id']] !== $basket->amount) {
                                    $oldCount = $basket->count;
                                    $basket = new Baskets;
                                    $basket->count = $product['count'] - $oldCount;
                                    $basket->amount = $priceMap[$product['product_id']];
                                } else
                                    $basket->count = $product['count'];
                            }
                        } else {
                            $basket = new Baskets;
                            $basket->count = $product['count'];
                            $basket->amount = $priceMap[$product['product_id']];
                            $amount += $product['count'] * $priceMap[$product['product_id']];
                        }
                        $basket->product_id = $product['product_id'];
                    } else {
                        $basket = new Baskets;
                        $coupon = Coupons::where('id', '=', $product['coupon_id'])->first();
                        $couponCount = $coupon->count;
                        $coupon->count = 0;
                        $coupon->save();
                        $basket->product_id = $coupon->product_id;
                        $basket->amount = 0;
                        $basket->coupon_id = $coupon->id;
                        $basket->count = $couponCount;
                    }
                    if (!is_null($basket)) {
                        $basket->sale_id = $sale->id;
                        $basket->save();
                    }
                }
                $sale->amount = $sale->amount_now = $amount;
            }

            $billPrograms = BillPrograms::orderBy('to', 'desc')->get();
            if ($billPrograms) {
                $program = null;
                $maxProgram = $billPrograms[0];
                if ($currentAmount >= $maxProgram->to)
                    $program = $maxProgram;
                foreach ($billPrograms as $row) {
                    if ($currentAmount >= $row->from && $currentAmount <= $row->to) {
                        $program = $row;
                        break;
                    }
                }
                $bill = Bills::where('id', '=', $saleId ? $sale->bill_id : $cardInfo->bill_id)->first();
                $currentFrom = 0;
                $currentTo = 0;
                if ($program) {
                    $currentFrom = $program->from;
                    $currentTo = $program->to;
                    $sale->bill_program_id = $program->id;
                    $bill->bill_program_id = $program->id;
                    $bill->value = floatval($bill->value) + $program->percent * 0.01 * $sale->amount;
                }
                $nextFrom = BillPrograms::where('from', '>', $currentFrom)->min('from');
                if (!$nextFrom) $nextFrom = $currentTo + 1;
                $bill->remaining_amount = ($currentAmount > $maxProgram->to) ? 0 : $nextFrom - $currentAmount;
                $bill->save();

                if ($program) {
                    $historyEntry = new BonusHistory;
                    $historyEntry->bill_program_id = $program->id;
                    $historyEntry->bill_id = $bill->id;
                    $historyEntry->sale_id = $sale->id;
                    $historyEntry->accumulated = floatval($bill->value);
                    $historyEntry->added = $program->percent * 0.01 * $sale->amount;
                    $historyEntry->dt = date('Y-m-d H:i:s');
                    $historyEntry->save();
                }
            }
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

        $query = Users::select('id', 'first_name', 'second_name', 'phone', 'birthday')
            ->where([['archived', '=', 0], ['active', '=', 1], ['type', '=', Users::TYPE_USER]]);
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
            $query = Users::select('id', 'first_name', 'second_name', 'phone', 'birthday')
                ->where([['archived', '=', 0], ['active', '=', 1], ['type', '=', Users::TYPE_USER]]);
            $query->where('phone', '=', $phone);
            $data = $query->get()->toArray();
            DataHelper::collectUsersInfo($data);
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }
}
