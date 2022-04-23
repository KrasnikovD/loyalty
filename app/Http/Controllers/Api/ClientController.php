<?php

namespace App\Http\Controllers\Api;

//use App\CustomClasses\Events\OnLogin\SixPercentBonus;
use App\Http\Controllers\Controller;
use App\Models\AnswerOptions;
use App\Models\Baskets;
use App\Models\BillPrograms;
use App\Models\Bills;
use App\Models\BillTypes;
use App\Models\BonusHistory;
use App\Models\BonusRules;
use App\Models\Cards;
use App\Models\Categories;
use App\Models\ClientAnswers;
use App\Models\CommonActions;
use App\Models\Coupons;
use App\Models\DataHelper;
use App\Models\Devices;
use App\Models\EventServices;
use App\Models\Favorites;
use App\Models\Fields;
use App\Models\FieldsUsers;
use App\Models\Files;
use App\Models\News;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Questions;
use App\Models\Reviews;
use App\Models\Sales;
use App\Models\Stocks;
use App\Models\Users;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
                'device_init',
                'list_outlets',
                'list_categories',
                'list_products'
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
            if (strpos($user->phone, '+7098') === false && strpos($user->phone, '+79207067770') === false) {
                $data = CommonActions::call($phone);
                $user->code = @$data->code;
            }
            $user->save();

            if ($newUser) {
                $cardExists = false;
                foreach (Cards::where('phone', '=', $phone)->get() as $card) {
                    $cardExists = true;
                    $card->user_id = $user->id;
                    $card->save();
                    Sales::where('card_id', '=', $card->id)->update(['user_id' => $card->user_id]);
                    CommonActions::cardHistoryLogBind($card, $user->id);
                }

                if (!$cardExists) {
                    $card = new Cards;
                    $card->user_id = $user->id;
                    $card->number = 'Z' . CommonActions::randomString(7, true);
                    $card->phone = $phone;
                    $card->save();

                    $billProgramId = $remainingAmount = null;
                    $programs = BillPrograms::orderBy('from', 'asc')->get();
                    if (isset($programs[0]) && $programs[0]->from == 0) {
                        $billProgramId = $programs[0]->id;
                        $remainingAmount = isset($programs[1]) ? $programs[1]->from : $programs[0]->to;
                    }

                    $bill = new Bills;
                    $bill->card_id = $card->id;
                    $bill->bill_type_id = BillTypes::where('name', '=', BillTypes::TYPE_DEFAULT)->value('id');
                    $bill->bill_program_id = $billProgramId;
                    $bill->remaining_amount = $remainingAmount;
                    $bill->save();

                    CommonActions::cardHistoryLogEditOrCreate($card, true, $user->id);
                }

                foreach (Fields::all() as $field) {
                    $fieldsUser = new FieldsUsers;
                    $fieldsUser->field_id = $field->id;
                    $fieldsUser->user_id = $user->id;
                    $fieldsUser->save();
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
     * @apiParam {string} [phone]
     * @apiParam {string} [expo_token]
     */

    public function login(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $phone = null;
        $validatorData = ['code' => $request->code];
        if ($request->phone) {
            $phone = str_replace(array("(", ")", " ", "-"), "", $request->phone);
            $validatorData['phone'] = $phone;
        }
        $validator = Validator::make($validatorData, ['code' => 'required', 'phone' => 'nullable|exists:users,phone']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $code = substr($request->code, 0, 4);
            $localeKey = null;
            $query = Users::where([['type', '=', Users::TYPE_USER], ['code', '=', $code]]);
            if (!empty($phone))
                $query->where([['phone', '=', $phone]]);
            $user = $query->first();
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
                        Devices::where([['expo_token', '<>', $request->expo_token], ['user_id', '=', $user->id]])->delete();
                        Devices::where('expo_token', '=', $request->expo_token)->update(['user_id' => $user->id]);
                    }
                    $data = [$user];
                    EventServices::onLogin(['user_id' => $user->id]);
                    DataHelper::collectUsersInfo($data);
                    $data = $data[0];
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
     * @apiParam {string} [expo_token]
     */

    public function check_auth(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;
        $validatorData = $request->all();
        $validator = Validator::make($validatorData, [
            'code' => 'required',
            'phone' => 'required',
        ]);
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
                if (!$localeKey) {
                    if (!empty($request->expo_token)) {
                        Devices::where([['expo_token', '<>', $request->expo_token], ['user_id', '=', $user->id]])->delete();
                        Devices::where('expo_token', '=', $request->expo_token)->update(['user_id' => $user->id]);
                    }
                    $data = [$user];
                    DataHelper::collectUsersInfo($data);
                    $data = $data[0];
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
            $products->where('visible', '=', 1);
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

            $order = $request->order ?: 'products.position';
            $dir = $request->dir ?: 'desc';
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
            $filesMap = [];
            if ($productsIds) {
                $reviews = Reviews::select('reviews.*', 'users.first_name as user_first_name', 'users.second_name as user_second_name')
                    ->where('reviews.type', '=', Reviews::TYPE_PRODUCT)
                    ->whereIn('reviews.object_id', $productsIds)
                    ->leftJoin('users', 'users.id', '=', 'reviews.user_id')
                    ->get();
                foreach ($reviews as $item) {
                    if(!isset($reviewsMap[$item['object_id']])) $reviewsMap[$item['object_id']] = [];
                    $reviewsMap[$item['object_id']][] = $item;
                }

                $files = Files::whereIn('parent_item_id', $productsIds)->get();
                foreach ($files as $file) {
                    if (!isset($filesMap[$file['parent_item_id']]))
                        $filesMap[$file['parent_item_id']] = [];
                    $filesMap[$file['parent_item_id']][] = $file->toArray();
                }
            }

            $favoritesIds = null;
            if (Auth::id()) {
                $favorites = Favorites::select('product_id')
                    ->where('user_id', '=', Auth::user()->id)->get()->toArray();
                $favoritesIds = array_column($favorites, 'product_id');
            }

            foreach ($list as &$item) {
                $item['is_favorite'] = @intval(in_array($item['id'], $favoritesIds));
                $item['reviews_list'] = @$reviewsMap[$item['id']];
                $item['images'] = @$filesMap[$item['id']];
                $item['file'] = @$filesMap[$item['id']][0]['name'];
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
        $validator = Validator::make(['id' => $id], ['id' => 'exists:products,id,visible,1|check_archived']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $product = Product::select('products.*', /*'outlets.name as outlet_name',*/ 'categories.name as category_name')
                /*->leftJoin('outlets', 'outlets.id', '=', 'products.outlet_id')*/
                ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
                ->where('products.id', '=', $id)->first();

            $files = Files::where('parent_item_id', '=', $product->id)->get();
            $product['images'] = $files;
            $product['file'] = @$files[0]['name'];
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
                if ($coupon = Coupons::where([
                    ['id', '=', $value['coupon_id']],
                    ['user_id', '=', $userId],
                    ['date_end', '>=', DB::raw('cast(now() as date)')]
                ])->first()) {
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
                $query->where('is_hidden', 0);
                $count = $query->count();
                $order = $request->order ?: 'news.created_at';
                $dir = $request->dir ?: 'desc';
                $offset = $request->offset;
                $limit = $request->limit;

                $query->orderBy($order, $dir);
                if ($limit) {
                    $query->limit($limit);
                    if ($offset) $query->offset($offset);
                }
            }
            $list = $query->get()->toArray();
            DataHelper::collectQuestionsInfo($list, Auth::user()->id);
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
     * @apiParam {integer} object_id
     * @apiParam {string=product,outlet} type
     * @apiParam {integer} [rating]
     */

    public function edit_review(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $data = null;

        Validator::extend('check_object', function($attribute, $value, $parameters, $validator) {
            $class = $parameters[0] == Reviews::TYPE_PRODUCT ? 'App\Models\Product' : 'App\Models\Outlet';
            return $class::where('id', '=', $value)->exists();
        });

        $validatorData = $request->all();
        $validatorRules = [
            'type' => 'required|in:product,outlet',
            'object_id' => 'required|check_object:' . $request->type,
            'message' => 'required',
            'rating' => 'nullable|integer|min:1|max:5',
        ];
        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $review = new Reviews;
            $review->message = $request->message;
            $review->object_id = $request->object_id;
            $review->type = $request->type;
            if ($request->rating)
                $review->rating = $request->rating;
            $review->user_id = Auth::user()->id;
            $review->save();

            $user = Users::where('id', '=', Auth::user()->id)->first();
            $data = new \stdClass;
            $data->message = $review->message;
            $data->id = $review->id;
            $data->object_id = $review->object_id;
            $data->user_id = $review->user_id;
            $data->created_at = $review->created_at;
            $data->type = $review->type;
            $data->rating = $review->rating;
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
     * @apiParam {string} [order] order field name
     * @apiParam {string} [dir] order direction
     * @apiParam {integer} [offset] start row number, used only when limit is set
     * @apiParam {integer} [limit] row count
     * @apiParam {string=product,outlet} [type]
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
        $validator = Validator::make($validatorData, ['id' => 'exists:reviews,id|check_hidden']);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $count = 0;
            $query = Reviews::select('reviews.id', 'reviews.message', 'reviews.object_id', 'reviews.rating', 'reviews.type');
            $query->where('is_hidden', '=', 0);
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
                ->whereIn('object_id', $productsIds)
                ->where('reviews.type', Reviews::TYPE_PRODUCT)
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
            'lon' => 'regex:/^[-]?\d+(\.\d+)?$/',
            'lat' => 'regex:/^[-]?\d+(\.\d+)?$/',
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
        $card = Cards::where('number', '=', $number)->first();
        if (!$card)
            $errors['number'] = __('messages.error_text_bind_card_non_exist');
        if (!empty($card->user_id))
            $errors['number'] = __('messages.error_text_bind_card_busy');
        if (!empty($errors)) {
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $card = Cards::where('number', '=', $number)->first();
            $card->user_id = Auth::user()->id;
            $card->phone = Auth::user()->phone;
            $card->save();
            Sales::where('card_id', '=', $card->id)->update(['user_id' => $card->user_id]);
            CommonActions::cardHistoryLogBind($card);
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
            return Cards::where([['is_main', '=', 0], ['user_id', '=', $parameters[0]], ['id', '=', $value]])->exists();
        });
        $validator = Validator::make(['id' => $id],
            ['id' => 'required|exists:cards,id|check_owner:' . Auth::user()->id]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $query = Cards::where([['user_id', '=', Auth::user()->id], ['is_main', '=', 1]]);
            $prevId = @$query->first()->id;
            $query->update(['is_main' => 0]);
            Cards::where('id', '=', $id)->update(['is_main' => 1]);
            foreach (Cards::whereIn('id', [$prevId, $id])->get() as $card) {
                CommonActions::cardHistoryLogEditOrCreate($card, false);
            }
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
        $user = [Auth::user()];
        DataHelper::collectUsersInfo($user);
        return response()->json(['errors' => [], 'data' => $user[0]], 200);
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
     * @apiParam {datetime} [birthday]
     * @apiParam {object[]} [fields]
     */

    public function edit_profile(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        $user = null;
        Validator::extend('field_validation', function($attribute, $value, $parameters, $validator) {
            $fieldName = @$value['name'];
            if (empty($fieldName) || !array_key_exists('value', $value))
                return false;
            return Fields::where([['name', '=', $fieldName], ['is_user_editable', '=', 1]])->exists();
        });

        $validatorRules =  [
            'fields' => 'nullable|array',
            'fields.*' => 'field_validation',
            'birthday' => 'nullable|date',
        ];
        $validator = Validator::make($request->all(), $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $user = Users::where('id', '=', Auth::user()->id)->first();
            if ($request->first_name) $user->first_name = $request->first_name;
            if ($request->second_name) $user->second_name = $request->second_name;
            if ($request->password) $user->password = md5($request->password);
            if ($request->birthday) $user->birthday = date("Y-m-d H:i:s", strtotime($request->birthday));
            $user->save();

            if (!empty($request->fields)) {
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
                    if ($keyExists) {
                        $fieldsUser = FieldsUsers::where([
                            ['field_id', $field->id],
                            ['user_id', $user->id]])->first();
                        if ($fieldsUser) {
                            $fieldsUser->value = $fieldValue;
                            $fieldsUser->save();
                        }
                    }
                }
            }
        }
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
            'id' => 'exists:cards,id,deleted_at,NULL',
            'number' => [
                !$id ? 'required' : 'nullable',
                "unique:cards,number," . $id,
                "regex:/^z\d{7}$/"
            ]
        ];

        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            $card = $id ? Cards::where('id', '=', $id)->first() : new Cards;
            if (!$id) {
                $card->user_id = Auth::user()->id;
                $card->phone = Auth::user()->phone;
            }
            $card->number = $request->number;
            $card->save();
            if (!$id) {
                $billProgramId = $remainingAmount = null;
                $programs = BillPrograms::orderBy('from', 'asc')->get();
                if (isset($programs[0]) && $programs[0]->from == 0) {
                    $billProgramId = $programs[0]->id;
                    $remainingAmount = isset($programs[1]) ? $programs[1]->from : $programs[0]->to;
                }

                $bill = new Bills;
                $bill->card_id = $card->id;
                $bill->bill_type_id = BillTypes::where('name', '=', BillTypes::TYPE_DEFAULT)->value('id');
                $bill->bill_program_id = $billProgramId;
                $bill->remaining_amount = $remainingAmount;
                $bill->save();
            }
            CommonActions::cardHistoryLogEditOrCreate($card, !$id);
        }
        return response()->json(['errors' => $errors, 'data' => $card], $httpStatus);
    }

    /**
     * @api {post} /api/clients/send_support_email Email Send
     * @apiName EmailSend
     * @apiGroup ClientEmail
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {string} name
     * @apiParam {string} text
     */

    public function send_support_email(Request $request)
    {
        $user = Auth::user();
        $messageText = 'Имя: ' . $user->first_name . "\n";
        $messageText .= 'Фамилия: ' . $user->second_name . "\n";
        $messageText .= 'Отчество: ' . $user->third_name . "\n";
        $messageText .= 'Телефон: ' . $user->phone . "\n\n";
        foreach ($request->all() as $item) $messageText .= $item . "\n";
        Mail::raw($messageText, function ($message) {
            $message->to('fb@cheskylev.ru')
                ->subject('Support email');
        });
    }

    /**
     * @api {post} /api/clients/answer Answer
     * @apiName Answer
     * @apiGroup ClientAnswer
     *
     * @apiHeader {string} Authorization Basic current user token
     *
     * @apiParam {object[]} answer
     */

    public function answer(Request $request)
    {
        $errors = [];
        $httpStatus = 200;
        Validator::extend('answer_validate', function($attribute, $value, $parameters, $validator) {
            if (!isset($value['question_id']) || !isset($value['value']))
                return false;
            if(!Questions::where('id', '=', $value['question_id'])->exists())
                return false;
            if(isset($value['answers_option_id']) && !AnswerOptions::where('id', '=', $value['answers_option_id'])->exists())
                return false;
            return true;
        });
        $validatorData = $request->all();
        $validatorRules = [
            'answer' => 'array',
            'answer.*' => 'required|answer_validate',
        ];
        $validator = Validator::make($validatorData, $validatorRules);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $httpStatus = 400;
        }
        if (empty($errors)) {
            foreach ($request->answer as $item) {
                if (is_array($item['value'])) {
                    foreach ($item['value'] as $value) {
                        $answer = new ClientAnswers;
                        $answer->question_id = $item['question_id'];
                        $answer->answer_option_id = $value['answers_option_id'];
                        $answer->value = $value['value'];
                        $answer->client_id = Auth::user()->id;
                        $answer->save();
                    }
                } else {
                    $answer = new ClientAnswers;
                    $answer->question_id = $item['question_id'];
                    $answer->value = $item['value'];
                    $answer->client_id = Auth::user()->id;
                   $answer->save();
                }
            }
            $questionId = $request->answer[0]['question_id'];
            $ruleId = Questions::join('news', 'news.id', '=', 'questions.news_id')
                ->where('questions.id', '=', $questionId)->value('bonus_rule_id');
            $rule = BonusRules::where('id', $ruleId)->first();

            $billProgramId = $remainingAmount = null;
            $programs = BillPrograms::orderBy('from', 'asc')->get();
            if (isset($programs[0]) && $programs[0]->from == 0) {
                $billProgramId = $programs[0]->id;
                $remainingAmount = isset($programs[1]) ? $programs[1]->from : $programs[0]->to;
            }
            $cards = Cards::where('user_id', Auth::user()->id)->get();
            foreach ($cards as $card) {
                $bill = new Bills;
                $bill->card_id = $card->id;
                $bill->bill_type_id = BillTypes::where('name', BillTypes::TYPE_BONUS)->value('id');
                $bill->bill_program_id = $billProgramId;
                $bill->remaining_amount = $remainingAmount;
                $bill->value = $rule->value;
                $bill->rule_id = $rule->id;
                $bill->end_dt = date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $rule->duration . ' days'));
                $bill->rule_name = $rule->name;
                $bill->save();
                CommonActions::cardHistoryLogAddBonusByRule($card, $bill);

                $title = __('messages.im_bill_by_bonus_rule_added_title');
                $body = __('messages.im_bill_by_bonus_rule_added_body', ['end_date' => date('d.m.y', strtotime($bill->end_dt))]);
                $device = Devices::where('user_id', '=', $card->user_id)->first();
                if ($device)
                    $device->notify(new WelcomeNotification($title, $body));
            }
        }
        return response()->json(['errors' => $errors, 'data' => null], $httpStatus);
    }

}
