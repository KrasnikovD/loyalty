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
use App\Models\Devices;
use App\Models\Favorites;
use App\Models\News;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Reviews;
use App\Models\Sales;
use App\Models\Stocks;
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
                'check_auth',
                'send_auth_sms',
                'device_init'
            ]]);
    }

    /**
     * @api {post} /api/clients/device_init Device Init
     * @apiName DeviceInit
     * @apiGroup ClientInit
     *
     * @apiParam {string} expo_token
     */

    public function device_init(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $device = null;
        $validator = Validator::make($request->all(), ['expo_token' => 'required']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $device = Devices::where('expo_token', '=', $request->expo_token)->first();
            if (empty($device)) {
                $device = new Devices;
                $device->expo_token = $request->expo_token;
                $device->save();
            }
        }
        return response()->json(['errors' => $errors, 'data' => $device], $httpStatus);
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
            $user = Users::where([['type', '=', Users::TYPE_USER], ['phone', '=', $phone]])->first();
            $newUser = false;
            if (empty($user)) {
                $user = new Users;
                $user->phone = $phone;
                $user->type = Users::TYPE_USER;
                $user->token = sha1(microtime() . 'salt' . time());
                $newUser = true;
            }
            $user->code = mt_rand(10000,90000);
            $user->save();
            $data = CommonActions::sendSms([$phone], $user->code);
            if ($newUser) {
                $card = new Cards;
                $card->user_id = $user->id;
                $card->number = CommonActions::randomString();
                $card->save();
                foreach (BillTypes::all() as $billType) {
                    $bill = new Bills;
                    $bill->card_id = $card->id;
                    $bill->bill_type_id = $billType->id;
                    $bill->save();
                }
            }
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {post} /api/clients/login Login
     * @apiName Login
     * @apiGroup ClientAuth
     *
     * @apiParam {string} code
     * @apiParam {string} [expo_token]
     */

    public function login(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $validatorData = $request->all();
        $validator = Validator::make($validatorData, ['code' => 'required']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $localeKey = null;
            $user = Users::where([['type', '=', Users::TYPE_USER], ['code', '=', $request->code]])->first();
            if (empty($user)) {
                $localeKey = 'auth.failed';
                $data['auth_status'] = 1;
            } else {
                if (!$user->active) {
                    $localeKey = __('auth.blocked');
                    $data['auth_status'] = 2;
                }
                if ($user->archived) {
                    $localeKey = __('auth.deleted');
                    $data['auth_status'] = 3;
                }
                if (!$localeKey) {
                    $user->token = md5($user->token);
                    if (!empty($request->expo_token)) {
                        Devices::where([['expo_token', '<>', $request->expo_token], ['user_id', '=', $user->id]])->update(['disabled' => 1]);
                        Devices::where('expo_token', '=', $request->expo_token)->update(['user_id' => $user->id]);
                    }
                    $data = $user;
                    $data['auth_status'] = 0;
                }
            }
            if ($localeKey) {
                $httpStatus = 400;
                $errors['user'] = __($localeKey);
            }
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {post} /api/clients/check_auth Check Auth
     * @apiName CheckAuth
     * @apiGroup ClientAuth
     *
     * @apiParam {string} code
     * @apiParam {string} phone
     */

    public function check_auth(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $validatorData = $request->all();
        $validator = Validator::make($validatorData, ['code' => 'required', 'phone' => 'required']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $phone = str_replace(array("(", ")", " ", "-"), "", $request->phone);
            $localeKey = null;
            $user = Users::where([['type', '=', Users::TYPE_USER], ['code', '=', $request->code], ['phone', '=', $phone]])->first();
            if (empty($user)) {
                $localeKey = 'auth.failed';
                $data['auth_status'] = 1;
            } else {
                $data = $user->toArray();
                if (!$user->active) {
                    $localeKey = __('auth.blocked');
                    $data['auth_status'] = 2;
                }
                if ($user->archived) {
                    $localeKey = __('auth.deleted');
                    $data['auth_status'] = 3;
                }
                if (!$localeKey) $data['auth_status'] = 0;
            }
            if ($localeKey) {
                $httpStatus = 400;
                $errors['user'] = __($localeKey);
            }
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/clients/categories/list Get Categories List
     * @apiName GetCategoriesList
     * @apiGroup ClientCategories
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
     * @api {post} /api/clients/products/list Get Products List
     * @apiName GetProductsList
     * @apiGroup ClientProducts
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     * @apiParam {integer[]} [category_ids] Array of category ids
     * @apiParam {integer} [is_hit]
     * @apiParam {integer} [is_novelty]
     * @apiParam {integer} [min_price]
     * @apiParam {integer} [max_price]
     */

    public function list_products(Request $request)
    {
        $errors = [];
        $httpStatus = 200;

        $validatorRules = [
            'dir' => 'in:asc,desc',
            'order' => 'in:id,outlet_id,category_id,name,description,file,price,created_at,updated_at',
            'offset' => 'integer',
            'limit' => 'integer',
            //'outlet_id' => 'exists:outlets,id',
            'category_ids' => 'array',
            'category_ids.*' => 'exists:categories,id',
            'is_hit' => 'in:0,1,true,false',
            'is_novelty' => 'in:0,1,true,false',
            'min_price' => 'integer',
            'max_price' => 'integer',
        ];
        $validator = Validator::make($request->all(), $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $products = Product::select('products.*', /*'outlets.name as outlet_name',*/ 'categories.name as category_name')
                /*->leftJoin('outlets', 'outlets.id', '=', 'products.outlet_id')*/
                ->leftJoin('categories', 'categories.id', '=', 'products.category_id');
            $products->where('archived', '=', 0);
            $categoryIds = $request->category_ids;
            if ($categoryIds && count($categoryIds) > 0) {
                /*if(Categories::where('id', '=', $request->category_id)->value('parent_id') == 0) {
                    $categories = Categories::where('parent_id', '=', $request->category_id)->get()->toArray();
                    $categoriesIds = array_column($categories, 'id');
                    if(!empty($categoriesIds)) {
                        $products->whereIn('category_id', $categoriesIds);
                    }
                } else {
                    $products->where('category_id', '=', $request->category_id);
                }*/
                $products->whereIn('category_id', $request->category_ids);
            }
            if (isset($request->is_hit)) {
                $isHit = intval($request->is_hit === 'true' ||
                    $request->is_hit === true ||
                    intval($request->is_hit) === 1);
                $products->where('is_hit', '=', $isHit);
            }
            if (isset($request->is_novelty)) {
                $isNovelty = intval($request->is_novelty === 'true' ||
                    $request->is_novelty === true ||
                    intval($request->is_novelty) === 1);
                $products->where('is_novelty', '=', $isNovelty);
            }
            if (isset($request->max_price))
                $products->where('price', '<=', $request->max_price);
            if (isset($request->min_price))
                $products->where('price', '>=', $request->min_price);
            /*if (isset($request->outlet_id)) {
                $products->where('outlet_id', '=', $request->outlet_id);
            }*/

            $order = $request->order ?: 'products.id';
            $dir = $request->dir ?: 'asc';
            $offset = $request->offset;
            $limit = $request->limit;

            $products->orderBy($order, $dir);
            if ($limit) {
                $products->limit($limit);
                if ($offset) $products->offset($offset);
            }

            $count = $products->count();
            $list = $products->get()->toArray();
            $productsIds = array_column($list, 'id');
            $reviewsMap = [];
            if ($productsIds) {
                $reviews = Reviews::select('reviews.*', 'users.first_name as user_first_name', 'users.second_name as user_second_name')
                    ->whereIn('product_id', $productsIds)
                    ->leftJoin('users', 'users.id', '=', 'reviews.user_id')
                    ->get();
                foreach ($reviews as $item) {
                    if(!isset($reviewsMap[$item['product_id']])) $reviewsMap[$item['product_id']] = [];
                    $reviewsMap[$item['product_id']][] = $item;
                }
            }

            $favorites = Favorites::select('product_id')
                ->where('user_id', '=', Auth::user()->id)->get()->toArray();
            $favoritesIds = array_column($favorites, 'product_id');

            foreach ($list as &$item) {
                $item['is_favorite'] = intval(in_array($item['id'], $favoritesIds));
                $item['reviews_list'] = @$reviewsMap[$item['id']];
            }
        }
        return response()->json([
            'errors' => $errors,
            'data' => [
                'count' => $count,
                'data' => $list
            ]
        ], $httpStatus);
    }

    /**
     * @api {get} /api/clients/products/get/:id Get Product
     * @apiName GetProduct
     * @apiGroup ClientProducts
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function get_product($id)
    {
        $errors = [];
        $httpStatus = 200;
        $product = null;
        Validator::extend('check_archived', function($attribute, $value, $parameters, $validator) {
            return Product::where([['id', '=', $value], ['archived', '=', 0]])->exists();
        });
        $validator = Validator::make(['id' => $id], ['id' => 'exists:products,id|check_archived']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $product = Product::select('products.*', /*'outlets.name as outlet_name',*/ 'categories.name as category_name')
                /*->leftJoin('outlets', 'outlets.id', '=', 'products.outlet_id')*/
                ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
                ->where('products.id', '=', $id)->first();
        }
        return response()->json(['errors' => $errors, 'data' => $product], $httpStatus);
    }

    /**
     * @api {post} /api/clients/orders/create Create Order
     * @apiName CreateOrder
     * @apiGroup ClientOrders
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} outlet_id
     * @apiParam {object[]} products
     * @apiParam {string} comment
     */

    public function edit_order(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $sale = null;
        Validator::extend('check_product', function($attribute, $value, $parameters, $validator) {
            if (!isset($value['count']) || !is_integer($value['count']) || $value['count'] === 0)
                return false;
            if (empty($value['coupon_id']) && empty($value['product_id']))
                return false;
            if (!empty($value['coupon_id'])) {
                $userId = $parameters[0];
                if ($coupon = Coupons::where([['id', '=', $value['coupon_id']], ['user_id', '=', $userId]])->first()) {
                    if (!Product::where([['id', '=', $coupon->product_id], ['archived', '=', 0]])->exists())
                        return false;
                    return $value['count'] <= $coupon->count;
                }
                return false;
            } else
                return Product::where([['id', '=', $value['product_id']], ['archived', '=', 0]])->exists();
        });

        $userId = Auth::user()->id;
        $validatorRules = [];
        $validatorRules['outlet_id'] = 'required|exists:outlets,id';
        $validatorRules['products'] = 'required|array';
        $validatorRules['products.*'] = "check_product:{$userId}";

        $validator = Validator::make($request->all(), $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $sale = new Sales;
            $sale->outlet_id = $request->outlet_id;
            $sale->user_id = $userId;
            $sale->dt = date("Y-m-d H:i:s");
            $productsMap = [];
            foreach (Product::whereIn('id', array_column($request->products, 'product_id'))->get() as $item)
                $productsMap[$item->id] = $item->price;
            $amount = 0;
            foreach ($request->products as $product) {
                if (!empty($product['product_id']))
                    $amount += $productsMap[$product['product_id']] * $product['count'];
            }
            $sale->amount = $sale->amount_now = $amount;
            if (!empty($request->comment)) $sale->user_comment = $request->comment;
            $sale->save();
            foreach ($request->products as $product) {
                $basket = new Baskets;
                $basket->sale_id = $sale->id;
                if (!empty($product['product_id'])) {
                    $basket->product_id = $product['product_id'];
                    $basket->amount = $productsMap[$product['product_id']];
                } else {
                    $coupon = Coupons::where('id', '=', $product['coupon_id'])->first();
                    $coupon->count = $coupon->count - $product['count'];
                    $coupon->save();
                    $basket->product_id = $coupon->product_id;
                    $basket->amount = 0;
                    $basket->coupon_id = $coupon->id;
                }
                $basket->count = $product['count'];
                $basket->save();
            }
        }
        return response()->json(['errors' => $errors, 'data' => $sale], $httpStatus);
    }

    /**
     * @api {post} /api/clients/orders/list Get Orders List
     * @apiName GetOrdersList
     * @apiGroup ClientOrders
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     * @apiParam {integer=0,4,5,6,7} [status]
     */

    /**
     * @api {get} /api/clients/orders/get/:id Get Order
     * @apiName GetOrder
     * @apiGroup ClientOrders
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_order(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $count = 0;

        Validator::extend('check_user', function($attribute, $value, $parameters, $validator) {
            return Sales::where([['id', '=', $value], ['user_id', '=', $parameters[0]]])->exists();
        });

        if (!$id) {
            $validatorRules = [
                'dir' => 'in:asc,desc',
                'order' => 'in:id,status,amount,amount_now,created_at,updated_at',
                'offset' => 'integer',
                'limit' => 'integer',
                'status' => 'in:' . Sales::STATUS_COMPLETED . ',' .
                    Sales::STATUS_PRE_ORDER . ',' .
                    Sales::STATUS_CANCELED_BY_OUTLET . ',' .
                    Sales::STATUS_CANCELED_BY_CLIENT . ',' .
                    Sales::STATUS_CANCELED_BY_ADMIN
            ];
        } else $validatorRules = ['id' => "check_user:" . Auth::user()->id];

        $validatorData = $request->all();
        $validatorData = array_merge($validatorData, ['user_id' => Auth::user()->id]);
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $sales = Sales::select('sales.*', 'users.id as user_id', 'users.phone as users_phone',
                'outlets.id as outlet_id', 'outlets.name as outlet_name',
                'cards.id as card_id', 'cards.number as card_number',
                'users.first_name as user_first_name', 'users.second_name as user_second_name')
                ->leftJoin('users', 'users.id', '=', 'sales.user_id')
                ->leftJoin('outlets', 'outlets.id', '=', 'sales.outlet_id')
                ->leftJoin('cards', 'cards.id', '=', 'sales.card_id');
            $status = $request->status;
            if (isset($status))
                $sales->where('status', '=', $request->status);
            $sales->where('sales.user_id', '=', Auth::user()->id);
            if (!$id) {
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
            foreach (Baskets::whereIn('sale_id', $salesIds)->get() as $basket) {
                if(!isset($basketsMap[$basket->sale_id])) $basketsMap[$basket->sale_id] = [];
                $basketsMap[$basket->sale_id][] = $basket->toArray();
            }
            foreach ($list as &$item) {
                $item['basket'] = @$basketsMap[$item['id']];
            }
            $data = $id ? $list[0] : ['count' => $count, 'data' => $list];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/clients/orders/cancel/:id Cancel Order
     * @apiName CancelOrder
     * @apiGroup ClientOrders
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function cancel_order($id)
    {
        $errors = [];
        $httpStatus = 200;
        Validator::extend('check_sale', function($attribute, $value, $parameters, $validator) {
            return @Sales::where('id', '=', $value)
                ->whereIn('status', [Sales::STATUS_PRE_ORDER])
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
            $sale->status = Sales::STATUS_CANCELED_BY_CLIENT;
            $sale->save();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {post} /api/clients/news/list Get News List
     * @apiName GetNewsList
     * @apiGroup ClientNews
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     */

    /**
     * @api {get} /api/clients/news/get/:id Get News
     * @apiName GetNews
     * @apiGroup ClientNews
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
     * @api {post} /api/clients/reviews/create Create Review
     * @apiName CreateReview
     * @apiGroup ClientReviews
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} message
     * @apiParam {integer} product_id
     */

    /**
     * @api {post} /api/clients/reviews/edit/:id Edit Review
     * @apiName EditReview
     * @apiGroup ClientReviews
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [message]
     */

    public function edit_review(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;

        Validator::extend('is_creator', function($attribute, $value, $parameters, $validator) {
            return Reviews::where([['id', '=', $value], ['user_id', '=', $parameters[0]]])->exists();
        });

        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validatorRules = [
            'product_id' => (!$id ? 'required|' : '') . 'exists:products,id',
            'id' => "exists:reviews,id|is_creator:" . Auth::user()->id
        ];
        if(!$id) $validatorRules['message'] = 'required';
        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $review = $id ? Reviews::where('id', '=', $id)->first() : new Reviews;
            if (isset($request->message)) $review->message = $request->message;
            if (isset($request->product_id)) $review->product_id = $request->product_id;
            if (!$id) $review->user_id = Auth::user()->id;
            $review->save();

            $user = Users::where('id', '=', Auth::user()->id)->first();
            $data = new \stdClass;
            $data->message = $review->message;
            $data->id = $review->id;
            $data->product_id = $review->product_id;
            $data->user_id = $review->user_id;
            $data->created_at = $review->created_at;
            $data->user_first_name = $user->first_name;
            $data->user_second_name = $user->second_name;
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {post} /api/clients/reviews/list Get Reviews List
     * @apiName GetReviewsList
     * @apiGroup ClientReviews
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} [product_id]
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     */

    /**
     * @api {get} /api/clients/reviews/get/:id Get Review
     * @apiName GetReview
     * @apiGroup ClientReviews
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function list_reviews(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        Validator::extend('check_hidden', function($attribute, $value, $parameters, $validator) {
            return Reviews::where([['id', '=', $value], ['is_hidden', '=', 0]])->exists();
        });
        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validator = Validator::make($validatorData, [
            'id' => 'exists:reviews,id|check_hidden',
            'product_id' => 'exists:products,id',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $count = 0;
            $query = Reviews::select('id', 'message');
            $query->where('is_hidden', '=', 0);
            if ($id) $query->where('id', '=', $id);
            else {
                if ($request->product_id) $query->where('product_id', '=', $request->product_id);
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
            if ($id) $data = $list[0];
            else $data = ['count' => $count, 'list' => $list];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/clients/reviews/delete/:id Delete Review
     * @apiName DeleteReview
     * @apiGroup ClientReviews
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function delete_review($id)
    {
        $errors = [];
        $httpStatus = 200;

        Validator::extend('is_creator', function($attribute, $value, $parameters, $validator) {
            return Reviews::where([['id', '=', $value], ['user_id', '=', $parameters[0]]])->exists();
        });

        $validator = Validator::make(['id' => $id], ['id' => "is_creator:" . Auth::user()->id]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            Reviews::where('id', '=', $id)->delete();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {post} /api/clients/favorites/add Add Favorite
     * @apiName AddFavorite
     * @apiGroup ClientFavorites
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {integer} product_id
     */

    public function add_favorites(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $favorite = null;

        $validatorRules = ['product_id' => 'required|exists:products,id'];
        $validator = Validator::make($request->all(), $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $favorite = new Favorites;
            $favorite->product_id = $request->product_id;
            $favorite->user_id = Auth::user()->id;
            $favorite->save();
        }
        return response()->json(['errors' => $errors, 'data' => $favorite], $httpStatus);
    }

    /**
     * @api {post} /api/clients/favorites/list Get Favorite Products List
     * @apiName GetFavoriteProductsList
     * @apiGroup ClientFavorites
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     */

    public function list_favorites(Request $request)
    {
        $products = Product::select('products.*', 'categories.name as category_name')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id');
        $order = $request->order ?: 'products.id';
        $dir = $request->dir ?: 'asc';
        $offset = $request->offset;
        $limit = $request->limit;

        $products->orderBy($order, $dir);
        if ($limit) {
            $products->limit($limit);
            if ($offset) $products->offset($offset);
        }
        $favorites = Favorites::select('product_id')
            ->where('user_id', '=', Auth::user()->id)->get()->toArray();
        $favoritesIds = array_column($favorites, 'product_id');
        if (empty($favoritesIds)) $products->where('products.id', '=', 0);
        else $products->whereIn('products.id', $favoritesIds);

        $count = $products->count();
        $list = $products->get()->toArray();
        $productsIds = array_column($list, 'id');
        $reviewsMap = [];
        if ($productsIds) {
            $reviews = Reviews::select('reviews.*',
                'users.first_name as user_first_name',
                'users.second_name as user_second_name')
                ->whereIn('product_id', $productsIds)
                ->leftJoin('users', 'users.id', '=', 'reviews.user_id')
                ->get();
            foreach ($reviews as $item) {
                if(!isset($reviewsMap[$item['product_id']])) $reviewsMap[$item['product_id']] = [];
                $reviewsMap[$item['product_id']][] = $item;
            }
        }
        foreach ($list as &$item) {
            $item['is_favorite'] = 1;
            $item['reviews_list'] = @$reviewsMap[$item['id']];
        }

        return response()->json([
            'errors' => [],
            'data' => [
                'count' => $count,
                'data' => $list
            ]
        ], 200);
    }

    /**
     * @api {get} /api/clients/favorites/delete/:product_id Delete Favorite
     * @apiName DeleteFavorite
     * @apiGroup ClientFavorites
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function delete_favorites($productId)
    {
        $errors = [];
        $httpStatus = 200;

        Validator::extend('is_creator', function($attribute, $value, $parameters, $validator) {
            return Favorites::where([['product_id', '=', $value], ['user_id', '=', $parameters[0]]])->exists();
        });

        $validator = Validator::make(['product_id' => $productId], ['product_id' => "is_creator:" . Auth::user()->id]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            Favorites::where([['product_id', '=', $productId], ['user_id', '=', Auth::user()->id]])->delete();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {post} /api/clients/outlets/list Get Outlets List
     * @apiName GetOutletsList
     * @apiGroup ClientOutlets
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [lon]
     * @apiParam {string} [lat]
     */

    public function list_outlets(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $outlets = null;
        $validatorData = $request->all();
        $validatorRules = [
            'lon' => 'regex:/^\d+(\.\d+)?$/',
            'lat' => 'regex:/^\d+(\.\d+)?$/',
        ];
        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $outlets = Outlet::all()->toArray();
            foreach ($outlets as &$outlet) {
                if($outlet['lat'] && $outlet['lon'])
                    $outlet['distance'] = ceil(CommonActions::calculateDistance($outlet['lat'], $outlet['lon'], $request->lat, $request->lon));
            }
            usort($outlets, function ($first, $second) {
                return $first['distance'] > $second['distance'];
            });
        }
        return response()->json(['errors' => $errors, 'data' => $outlets], $httpStatus);
    }

    /**
     * @api {post} /api/clients/stocks/list Get Stocks List
     * @apiName GetStocksList
     * @apiGroup ClientStocks
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     */

    /**
     * @api {get} /api/clients/stocks/get/:id Get Stock
     * @apiName GetStock
     * @apiGroup ClientStocks
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
     * @api {post} /api/clients/cards/list Get Cards List
     * @apiName GetCardsList
     * @apiGroup ClientCards
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     */

    public function list_cards(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $validatorRules = [
            'dir' => 'in:asc,desc',
            'order' => 'in:id,user_id,number,created_at,updated_at',
            'offset' => 'integer',
            'limit' => 'integer',
        ];
        $validator = Validator::make($request->all(), $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $query = Cards::select('*');
            $query->where('user_id', '=', Auth::user()->id);

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
            $list = $query->get()->toArray();
            DataHelper::collectCardsInfo($list);
            $data = ['count' => $count, 'list' => $list];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {get} /api/clients/cards/bind_card/:number Bind Card
     * @apiName BindCard
     * @apiGroup ClientCards
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function bind_card($number)
    {
        $errors = [];
        $httpStatus = 200;
        Validator::extend('check_no_owner', function($attribute, $value, $parameters, $validator) {
            return Cards::where('number', '=', $value)
                ->whereNull('user_id')->exists();
        });
        $validator = Validator::make(['number' => $number], ['number' => 'required|exists:cards,number|check_no_owner']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $card = Cards::where('number', '=', $number)->first();
            $card->user_id = Auth::user()->id;
            $card->save();
            Sales::where('card_id', '=', $card->id)->update(['user_id' => $card->user_id]);
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {get} /api/clients/cards/set_main/:id Set Main
     * @apiName SetMain
     * @apiGroup ClientCards
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function set_main($id)
    {
        $errors = [];
        $httpStatus = 200;
        Validator::extend('check_owner', function($attribute, $value, $parameters, $validator) {
            return Cards::where([['user_id', '=', $parameters[0]], ['id', '=', $value]])->exists();
        });
        $validator = Validator::make(['id' => $id],
            ['id' => 'required|exists:cards,id|check_owner:' . Auth::user()->id]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            Cards::where('user_id', '=', Auth::user()->id)->update(['is_main' => 0]);
            Cards::where('id', '=', $id)->update(['is_main' => 1]);
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {get} /api/clients/profile Get Profile
     * @apiName GetProfile
     * @apiGroup ClientProfile
     *
     * @apiHeader {string} Authorization Basic current user token
     */

    public function profile()
    {
        $user = Auth::user();
        $data = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'second_name' => $user->second_name,
            'phone' => $user->phone,
        ];
        return response()->json(['errors' => [], 'data' => $data], 200);
    }

    /**
     * @api {post} /api/clients/profile/edit Edit Profile
     * @apiName EditProfile
     * @apiGroup ClientProfile
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [first_name]
     * @apiParam {string} [second_name]
     * @apiParam {string} [password]
     */

    public function edit_profile(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $user = null;

        $user = Users::where('id', '=', Auth::user()->id)->first();
        if ($request->first_name) $user->first_name = $request->first_name;
        if ($request->second_name) $user->second_name = $request->second_name;
        if ($request->password) $user->password = md5($request->password);
        $user->save();

        return response()->json(['errors' => $errors, 'data' => $user], $httpStatus);
    }

    /**
     * @api {post} /api/clients/bill_programs/list Get Bill Programs List
     * @apiName GetBillProgramsList
     * @apiGroup ClientBillPrograms
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
     * @api {post} /api/clients/coupons/list Get Coupons
     * @apiName GetCoupons
     * @apiGroup ClientCoupons
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     */

    /**
     * @api {get} /api/clients/coupons/get/:id Get Coupon
     * @apiName GetCoupon
     * @apiGroup ClientCoupons
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
            $query = Coupons::select('coupons.*',
                'products.name as product_name', 'products.file as product_file', 'products.price as product_price'/*,
                'users.first_name', 'users.second_name', 'users.phone'*/)
                ->join('products', 'products.id', '=', 'coupons.product_id')
                /*->join('users', 'users.id', '=', 'coupons.user_id')*/;
            if ($id) {
                $query->where('coupons.id', '=', $id);
            } else {
                $query->where([
                    ['products.archived', '=', 0],
                    ['coupons.count', '>', 0],
                    ['coupons.user_id', '=', Auth::user()->id]]);
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
     * @api {post} /api/clients/bonus_history/get/:bill_id Get Bonus History
     * @apiName GetBonusHistory
     * @apiGroup ClientBonusHistory
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     */

    public function bonus_history(Request $request, $billId)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        Validator::extend('is_owner', function($attribute, $value, $parameters, $validator) {
            return Bills::join('cards', 'bills.card_id', '=', 'cards.id')
                ->where([['bills.id', '=', $value], ['cards.user_id', '=', $parameters[0]]])->exists();
        });
        $validator = Validator::make(['bill_id' => $billId], ['bill_id' => 'exists:bills,id|is_owner:' . Auth::user()->id]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $query = BonusHistory::where('bill_id', '=', $billId);
            $count = $query->count();
            $order = $request->order ?: 'bonus_history.id';
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
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {post} /api/clients/set_location Set Location
     * @apiName SetLocation
     * @apiGroup ClientLocation
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} lon
     * @apiParam {string} lat
     */

    public function set_location(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $validatorData = $request->all();
        $validatorRules = [
            'lon' => 'regex:/^\d+(\.\d+)?$/',
            'lat' => 'regex:/^\d+(\.\d+)?$/',
        ];
        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $user = Users::where('id', '=', Auth::user()->id)->first();
            $user->lat = $request->lat;
            $user->lon = $request->lon;
            $user->save();
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

    /**
     * @api {post} /api/clients/cards/create Create Card
     * @apiName CreateCard
     * @apiGroup ClientCards
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} number
     */

    /**
     * @api {post} /api/clients/cards/edit/:id Edit Card
     * @apiName EditCard
     * @apiGroup ClientCards
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} number
     */

    public function edit_card(Request $request, $id = null)
    {
        $errors = [];
        $httpStatus = 200;
        $card = null;
        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validatorRules = [
            'number' => 'required',
            'id' => 'exists:cards,id'
        ];

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $card = $id ? Cards::where('id', '=', $id)->first() : new Cards;
            if (!$id) $card->user_id = Auth::user()->id;
            $card->number = $request->number;
            $card->save();
            if (!$id) {
                foreach (BillTypes::all() as $billType) {
                    $bill = new Bills;
                    $bill->card_id = $card->id;
                    $bill->bill_type_id = $billType->id;
                    $bill->save();
                }
            }
        }
        return response()->json(['errors' => $errors, 'data' => $card], $httpStatus);
    }
}
