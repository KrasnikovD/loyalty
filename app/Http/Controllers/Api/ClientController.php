<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Baskets;
use App\Models\Categories;
use App\Models\CommonActions;
use App\Models\News;
use App\Models\Orders;
use App\Models\Product;
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
                'send_auth_sms'
            ]]);
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
            $user = Users::where([['type', '=', 1], ['phone', '=', $phone]])->first();
            if (empty($user)) {
                $user = new Users;
                $user->phone = $phone;
                $user->type = 1;
                $user->token = sha1(microtime() . 'salt' . time());
            }
            $user->code = mt_rand(10000,90000);
            $user->save();
            $data = CommonActions::sendSms($phone, $user->code);
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
    }

    /**
     * @api {post} /api/clients/login Login
     * @apiName Login
     * @apiGroup ClientAuth
     *
     * @apiParam {string} code
     */

    public function login(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $user = null;
        $validatorData = $request->all();
        $validator = Validator::make($validatorData, ['code' => 'required']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $user = Users::where([['type', '=', 1], ['code', '=', $request->code]])->first();
            if (empty($user)) {
                $errors['user'] = 'User not found';
                $httpStatus = 400;
            } else $user->token = md5($user->token);
        }
        return response()->json(['errors' => $errors, 'data' => $user], $httpStatus);
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
        $data = null;

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

            $data = ['count' => $products->count(), 'data' => $products->get()];
        }
        return response()->json(['errors' => $errors, 'data' => $data], $httpStatus);
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
        $validator = Validator::make(['id' => $id], ['id' => 'exists:products,id']);
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
     * @apiParam {string} address
     * @apiParam {object[]} products
     */

    public function edit_order(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $order = null;
        Validator::extend('check_product', function($attribute, $value, $parameters, $validator) {
            if (!isset($value['count']) || !is_integer($value['count']) || $value['count'] === 0)
                return false;
            return Product::where('id', '=', $value['product_id'])->exists();
        });

        $validatorRules = [];
        $validatorRules['address'] = 'required';
        $validatorRules['products'] = 'required|array';
        $validatorRules['products.*'] = 'check_product';

        $validator = Validator::make($request->all(), $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $order = new Orders;
            $order->address = $request->address;
            $order->user_id = Auth::user()->id;
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
        return response()->json(['errors' => $errors, 'data' => $order], $httpStatus);
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
            return Orders::where([['id', '=', $value], ['user_id', '=', $parameters[0]]])->exists();
        });

        if (!$id) {
            $validatorRules = [
                'dir' => 'in:asc,desc',
                'order' => 'in:id,address,status,amount,amount_now,created_at,updated_at',
                'offset' => 'integer',
                'limit' => 'integer',
            ];
        } else {
            $validatorRules = ['id' => "check_user:" . Auth::user()->id];
        }
        $validatorData = $request->all();
        $validatorData = array_merge($validatorData, ['user_id' => Auth::user()->id]);
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
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

            $orders->where('user_id', '=', Auth::user()->id);

            $list = $orders->get()->toArray();

            $ordersIds = array_column($list, 'id');
            $basketsMap = [];
            foreach (Baskets::whereIn('order_id', $ordersIds)->get() as $basket) {
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
}
