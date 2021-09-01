<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Baskets;
use App\Models\BillPrograms;
use App\Models\Bills;
use App\Models\BillTypes;
use App\Models\BonusHistory;
use App\Models\Cards;
use App\Models\Categories;
use App\Models\CommonActions;
use App\Models\Coupons;
use App\Models\DataHelper;
use App\Models\Product;
use App\Models\Sales;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\ArrayToXml\ArrayToXml;

class OutletController extends Controller
{
    /**
     * @api {post} /api/outlets/sales/create Create Sale
     * @apiName CreateSale
     * @apiGroup OutletSales
     *
     * @apiParam {integer} outlet_id
     * @apiParam {string} card_number
     * @apiParam {string} debited
     * @apiParam {object[]} products
     * @apiParam {string=xml,json} [out_format]
     */

    /**
     * @api {post} /api/outlets/sales/edit/:sale_id Edit Sale
     * @apiName EditSale
     * @apiGroup OutletSales
     *
     * @apiParam {string} card_number
     * @apiParam {string} debited
     * @apiParam {object[]} [products]
     * @apiParam {string=xml,json} [out_format]
     */

    public function edit_sale(Request $request, $saleId = null)
    {
        $errors = [];
        $httpStatus = 200;
        $sale = null;
        Validator::extend('check_product', function($attribute, $value, $parameters, $validator) {
            $count = @floatval($value['count']);
            if (empty($value['coupon_id']) && empty($count))
                return false;
            if (empty($value['coupon_id']) && empty($value['product_id']))
                return false;
            if (isset($value['coupon_id']) && isset($value['product_id']))
                return false;
            if (!empty($value['coupon_id'])) {
                $productId = Coupons::where('id', '=', $value['coupon_id'])->value('product_id');
                if (!Product::where([['id', '=', $productId], ['archived', '=', 0]])->exists())
                    return false;
                $saleId = $parameters[1];
                $userId = Cards::where('number', '=', $parameters[0])->value('user_id');
                if (empty($userId)) return false;
                $validateData = [
                    ['id', '=', $value['coupon_id']],
                    ['user_id', '=', $userId],
                    ['date_end', '>=', DB::raw('cast(now() as date)')]
                ];
                if (empty($saleId)) $validateData[] = ['count', '>', 0];
                else {
                    if (!Baskets::where([['coupon_id', '=', $value['coupon_id']],
                        ['sale_id', '=', $saleId]])->exists())
                        $validateData[] = ['count', '>', 0];
                }
                return Coupons::where($validateData)->exists();
            }
            return true;
            /*else {
                return Product::where([['code', '=', $value['product_id']], ['archived', '=', 0]])->exists();
            }*/
        });
        Validator::extend('check_sale', function($attribute, $value, $parameters, $validator) {
            if ($parameters[0]) {
                if (Baskets::where('sale_id', '=', $value)
                    ->join('products', 'products.id', '=', 'baskets.product_id')
                    ->where('products.archived', '=', 1)->exists())
                    return false;
            }
            return @Sales::where('id', '=', $value)->first()->status == Sales::STATUS_PRE_ORDER;
        });
        Validator::extend('check_debited', function($attribute, $value, $parameters, $validator) {
            $cardInfo = Cards::select('bills.*')
                ->join('bills', 'bills.card_id', '=', 'cards.id')
                ->join('bill_types', 'bill_types.id', '=', 'bills.bill_type_id')
                ->where([
                    ['number', '=', $parameters[0]],
                    ['bill_types.name', '=', BillTypes::TYPE_DEFAULT]])->first();
            return $cardInfo->value >= $value;
        });
        $validatorData = $request->all();
        if ($saleId) $validatorData = array_merge($validatorData, ['sale_id' => $saleId]);
        $validator = Validator::make($validatorData,
            [
                'card_number' => 'required|exists:cards,number',
                'outlet_id' => (!$saleId ? 'required|' : '') . 'exists:outlets,id',
                'sale_id' => 'exists:sales,id|check_sale:' . empty($request->products),
                'products' => (!$saleId ? 'required|' : '') . 'array',
                'products.*' => 'check_product:' . $request->card_number .',' . $saleId,
                'out_format' => 'in:xml,json',
                'debited' => 'regex:/^\d+(\.\d{1,2})?$/|check_debited:' . $request->card_number,
            ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $debited = $request->debited ?: 0;
            $cardInfo = Cards::select('cards.id', 'user_id', 'bills.id as bill_id')
                ->join('bills', 'bills.card_id', '=', 'cards.id')
                ->join('bill_types', 'bill_types.id', '=', 'bills.bill_type_id')
                ->where([
                    ['number', '=', $request->card_number],
                    ['bill_types.name', '=', BillTypes::TYPE_DEFAULT]])->first();

            $birthdayStockValue = CommonActions::getBirthdayStockInfo($cardInfo->user_id, $saleId, $request->products);

            $currentAmount = Sales::where([['bill_id', '=', $cardInfo->bill_id], ['status', '=', Sales::STATUS_COMPLETED]])->sum('amount');

            $sale = $saleId ? Sales::where('id', '=', $saleId)->first() : new Sales;
            if (isset($request->outlet_id)) $sale->outlet_id = $request->outlet_id;
            $sale->user_id = $cardInfo->user_id;
            $sale->card_id = $cardInfo->id;
            $sale->bill_id = $cardInfo->bill_id;
            if (!$saleId) $sale->dt = date('Y-m-d H:i:s');
            $sale->status = Sales::STATUS_COMPLETED;

            if (!empty($request->products)) {
                $amount = 0;
                /*$existedCouponsIds = [];
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
                }*/
                $sale->save();
                $productsMap = [];
                if ($saleId) {
                //    foreach (Product::whereIn('code', array_column($existedBasketProductMap, 'product_id'))->get() as $item)
                //        $priceMap[$item->code] = $item->price;
                } else {
                    foreach (Product::whereIn('code', array_column($request->products, 'product_id'))->get() as $item)
                        $productsMap[$item->code] = $item;
                }

                foreach ($request->products as $product) {
                //    if (isset($product['coupon_id']) && in_array($product['coupon_id'], $existedCouponsIds))
                //        continue;
                //    if (isset($product['product_id']) && $product['count'] == @$existedBasketProductMap[$product['product_id']]->count)
                //        continue;
                    $basket = null;
                    if (isset($product['product_id'])) {
                       // $basket = @$existedBasketProductMap[$product['product_id']];
                        if (isset($basket)) {
                        /*    if ($product['count'] < $basket->count) {
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
                            }*/
                        } else {
                            if (!Product::where('code', $product['product_id'])->exists()) {
                                $newProduct = new Product;
                                $newProduct->code = $product['product_id'];
                                $newProduct->price = $product['amount'];
                                $newProduct->category_id = Categories::where('name', Categories::DEFAULT_NAME)->first()->id;
                                $newProduct->save();
                                $productsMap[$product['product_id']] = $newProduct;
                            }

                            $basket = new Baskets;
                            $basket->count = $product['count'];
                            $basket->real_amount = $productsMap[$product['product_id']]->price;
                            $basket->amount = $product['amount'];
                            $amount += $product['count'] * $product['amount'];
                            //$amount += $product['count'] * $priceMap[$product['product_id']]->price;
                        }
                        $basket->product_id = $productsMap[$product['product_id']]->id;
                    } else {
                        $coupon = Coupons::where('id', '=', $product['coupon_id'])->first();
                        $couponCount = $coupon->count;
                        $coupon->count = 0;
                        $coupon->save();

                        $basket = new Baskets;
                        $basket->product_id = $coupon->product_id;
                        $basket->amount = $basket->real_amount = 0;
                        $basket->coupon_id = $coupon->id;
                        $basket->count = $couponCount;
                    }
                    if (!is_null($basket)) {
                        $basket->sale_id = $sale->id;
                        $basket->save();
                    }
                }
                //if (($amount - $debited) < 0) $debited = $amount;
                $amount -= $debited;
                $sale->amount = $sale->amount_now = $amount;
            }
           // $currentAmount = 10000;
            $historyEntry = null;
            $billPrograms = BillPrograms::orderBy('to', 'desc')->get();
            if ($billPrograms) {
                $bill = Bills::where('id', '=', $saleId ? $sale->bill_id : $cardInfo->bill_id)->first();
                $program = BillPrograms::where('id', $bill->bill_program_id)->first();
                $maxProgram = $billPrograms[0];
                if ($currentAmount >= $maxProgram->to)
                    $program = $maxProgram;
                $tempProgram = null;
                foreach ($billPrograms as $row) {
                    if ($currentAmount >= $row->from && $currentAmount <= $row->to) {
                        $tempProgram = $row;
                        break;
                    }
                }
                if ($tempProgram && $tempProgram->from > $program->from) {
                    $program = $tempProgram;
                }
                $currentFrom = 0;
                $currentTo = 0;
                $added = 0;
                if ($program) {
                    $added = $birthdayStockValue ?: $program->percent * 0.01 * $sale->amount;
                    if ($debited) $added = 0;
                    $currentFrom = $program->from;
                    $currentTo = $program->to;
                    $sale->bill_program_id = $program->id;
                    $bill->bill_program_id = $program->id;
                    $bill->value = floatval($bill->value - $debited) + $added;
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
                    $historyEntry->added = $added;
                    $historyEntry->dt = date('Y-m-d H:i:s');
                    if ($debited) $historyEntry->debited = $debited;
                    $historyEntry->save();
                    CommonActions::sendSalePush($cardInfo->user_id, $added, $debited, $sale->outlet_id);
                }
            }
            if ($debited) $sale->debited = $debited;
            $sale->save();
            CommonActions::cardHistoryLogSale($sale, $historyEntry);
        }
        if ($request->out_format == 'json')
            return response()->json(['errors' => $errors, 'data' => $sale], $httpStatus);
        else
            return response(ArrayToXml::convert(['errors' => $errors, 'data' => $sale], [], true, 'UTF-8'), $httpStatus)
                ->header('Content-Type', 'text/xml');
    }

    /**
     * @api {get} /api/outlets/sales/cancel/:sale_id Cancel Sale
     * @apiName CancelSale
     * @apiGroup OutletSales
     *
     * @apiParam {string=xml,json} [out_format]
     */

    public function cancel_sale(Request $request, $saleId)
    {
        $errors = [];
        $httpStatus = 200;
        Validator::extend('check_sale', function($attribute, $value, $parameters, $validator) {
            return @Sales::where('id', '=', $value)
                ->whereIn('status', [Sales::STATUS_PRE_ORDER, Sales::STATUS_COMPLETED])
                ->exists();
        });

        $validator = Validator::make(array_merge($request->all(), ['sale_id' => $saleId]), [
                'sale_id' => 'exists:sales,id|check_sale',
                'out_format' => 'in:xml,json',
            ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            if ($baskets = Baskets::where('sale_id', '=', $saleId)->whereNotNull('coupon_id')->get()) {
                foreach ($baskets as $basket) {
                    $coupon = Coupons::where('id', '=', $basket->coupon_id)->first();
                    $coupon->count += $basket->count;
                    $coupon->save();
                }
            }

            $sale = Sales::where('id', '=', $saleId)->first();
            $sale->status = Sales::STATUS_CANCELED_BY_OUTLET;
            $sale->save();
        }
        if ($request->out_format == 'json')
            return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
        else
            return response(ArrayToXml::convert(['errors' => $errors, 'data' => null], [], true, 'UTF-8'), $httpStatus)
                ->header('Content-Type', 'text/xml');
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
     * @apiParam {string=xml,json} [out_format]
     */

    public function list_users(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $validatorRules = [
            'dir' => 'in:asc,desc',
            'order' => 'in:id,status,amount,amount_now,created_at,updated_at',
            'offset' => 'integer',
            'limit' => 'integer',
            'out_format' => 'in:xml,json',
        ];
        $validator = Validator::make($request->all(), $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $order = $request->order ?: 'users.id';
            $dir = $request->dir ?: 'desc';
            $offset = $request->offset;
            $limit = $request->limit;
            $like = $request->like;

            $query = Users::select('id', 'first_name', 'second_name', 'phone', 'birthday')
                ->where([['archived', '=', 0], ['active', '=', 1], ['type', '=', Users::TYPE_USER]]);
            if ($like)
                $query->orWhere('phone', 'like', '%' . str_replace(array("(", ")", " ", "-"), "", $like) . '%');
            $count = $query->count();
            $query->orderBy($order, $dir);
            if ($limit) {
                $query->limit($limit);
                if ($offset) $query->offset($offset);
            }

            $list = $query->get()->toArray();
            DataHelper::collectUsersInfo($list);
            $data = ['count' => $count, 'list' => $list];
        }
        if ($request->out_format == 'json')
            return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
        else
            return response(ArrayToXml::convert(['errors' => $errors, 'data' => $data], [], true, 'UTF-8'), $httpStatus)
                ->header('Content-Type', 'text/xml');
    }

    /**
     * @api {post} /api/outlets/users/find_by_phone Find User By Phone
     * @apiName FindUserByPhone
     * @apiGroup OutletSales
     *
     * @apiParam {string} phone
     * @apiParam {string=xml,json} [out_format]
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

        $validator = Validator::make(array_merge($request->all(), ['phone' => $phone]), [
                'phone' => "required|phone_exists:$phone",
                'out_format' => 'in:xml,json'
            ]);
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
        if ($request->out_format == 'json')
            return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
        else
            return response(ArrayToXml::convert(['errors' => $errors, 'data' => $data], [], true, 'UTF-8'), $httpStatus)
                ->header('Content-Type', 'text/xml');
    }

    /**
     * @api {get} /api/outlets/sales/get/:sale_id Get Sale
     * @apiName GetSale
     * @apiGroup OutletSales
     *
     * @apiParam {string=xml,json} [out_format]
     */

    public function get_sale(Request $request, $saleId)
    {
        $errors = [];
        $httpStatus = 200;
        $sale = null;
        $validator = Validator::make(array_merge($request->all(), ['sale_id' => $saleId]),
            [
                'sale_id' => 'exists:sales,id',
                'out_format' => 'in:xml,json'
            ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $sale = Sales::select('sales.*', 'users.id as user_id', 'users.phone as users_phone',
                'outlets.id as outlet_id', 'outlets.name as outlet_name',
                'cards.id as card_id', 'cards.number as card_number',
                'users.first_name as user_first_name', 'users.second_name as user_second_name')
                ->leftJoin('users', 'users.id', '=', 'sales.user_id')
                ->leftJoin('outlets', 'outlets.id', '=', 'sales.outlet_id')
                ->leftJoin('cards', 'cards.id', '=', 'sales.card_id')
                ->where('sales.id', '=', $saleId)->first()->toArray();
        }
        if ($request->out_format == 'json')
            return response()->json(['errors' => $errors, 'data' => $sale], $httpStatus);
        else
            return response(ArrayToXml::convert(['errors' => $errors, 'data' => $sale], [], true, 'UTF-8'), $httpStatus)
                ->header('Content-Type', 'text/xml');
    }

    /**
     * @api {get} /api/outlets/card/get/:card_number Get Card
     * @apiName GetCard
     * @apiGroup OutletCards
     *
     * @apiParam {string=xml,json} [out_format]
     */

    public function get_card(Request $request, $cardNumber)
    {
        $errors = [];
        $httpStatus = 200;
        $card = null;
        $validatorRules = [
           // 'card_number' => 'exists:cards,number',
            'card_number' => 'required',
            'out_format' => 'in:xml,json'
        ];
        $validator = Validator::make(
            array_merge($request->all(), ['card_number' => $cardNumber]),
            $validatorRules
        );
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            if (!Cards::where('number', '=', $cardNumber)->exists()) {
                $card = new Cards;
                $card->number = $cardNumber;
                $card->save();
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
                CommonActions::cardHistoryLogEditOrCreate($card, true);
            }
            $card = Cards::select('cards.*', 'users.first_name as user_first_name', 'users.second_name as user_second_name', 'users.phone as user_phone')
                ->leftJoin('users', 'users.id', '=', 'cards.user_id')
                ->where('number', '=', $cardNumber)->get()->toArray();
            DataHelper::collectCardsInfo($card);
        }
        if ($request->out_format == 'json')
            return response()->json(['errors' => $errors, 'data' => $card], $httpStatus);
        else
            return response(ArrayToXml::convert(['errors' => $errors, 'data' => $card], [], true, 'UTF-8'), $httpStatus)
                ->header('Content-Type', 'text/xml');
    }

    /**
     * @api {get} /api/outlets/coupon/get/:id Get Coupon
     * @apiName GetCoupon
     * @apiGroup OutletCoupons
     *
     * @apiParam {string=xml,json} [out_format]
     */

    public function get_coupon(Request $request, $id)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $validatorRules = [
            'id' => 'exists:coupons,id',
            'out_format' => 'in:xml,json'
        ];
        $validator = Validator::make(
            array_merge($request->all(), ['id' => $id]),
            $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $data = [];
            $coupon = Coupons::where('id', $id)->first();
            $data['count'] = $coupon->count;
            $data['product'] = Product::where('id', $coupon->product_id)->first();
        }
        if ($request->out_format == 'json')
            return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
        else
            return response(ArrayToXml::convert(['errors' => $errors, 'data' => $data], [], true, 'UTF-8'), $httpStatus)
                ->header('Content-Type', 'text/xml');
    }
}
