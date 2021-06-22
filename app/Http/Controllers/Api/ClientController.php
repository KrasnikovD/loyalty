<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Baskets;
use App\Models\Cards;
use App\Models\Categories;
use App\Models\CommonActions;
use App\Models\DataHelper;
use App\Models\Favorites;
use App\Models\Fields;
use App\Models\FieldsUsers;
use App\Models\News;
use App\Models\Orders;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Reviews;
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
            $data->first_name = $user->first_name;
            $data->second_name = $user->second_name;
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
        $validatorData = $request->all();
        if ($id) $validatorData = array_merge($validatorData, ['id' => $id]);
        $validator = Validator::make($validatorData, [
            'id' => 'exists:reviews,id',
            'product_id' => 'exists:products,id',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $count = 0;
            $query = Reviews::select('id', 'message');
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
            $origins = '';
            $outletMap = [];
            foreach ($outlets as $outlet) {
                if($outlet['lat'] && $outlet['lon']) {
                    $origins .= "{$outlet['lat']},{$outlet['lon']}|";
                    $outletMap[$outlet['id']] = 0;
                }
            }
            $origins = trim($origins, "|");
            if ($origins && $request->lat && $request->lon) {
                $destinations = "{$request->lat},{$request->lon}";
                $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins={$origins}&destinations={$destinations}&key=AIzaSyAeTE_kYwFmy1MeEwQDfup0kWVwUUv2gyE";
                $result = @json_decode(file_get_contents($url));
                $rows = @$result->rows;
                if (is_array($rows)) {
                   $i = 0;
                   foreach ($outletMap as &$item) {
                       $item = $rows[$i++]->elements[0]->distance->value;
                   }
                }
            }
            foreach ($outlets as &$outlet) {
                $outlet['distance'] = 0;
                if (@$outletMap[$outlet['id']]) $outlet['distance'] = $outletMap[$outlet['id']];
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
}
