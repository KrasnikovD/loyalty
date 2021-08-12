<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::post('/admin/login', 'App\Http\Controllers\Api\AdminController@login');

Route::post('/bill_types/create', 'App\Http\Controllers\Api\AdminController@edit_bill_type');
Route::post('/bill_types/edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_bill_type');
Route::post('/bill_types/list', 'App\Http\Controllers\Api\AdminController@list_bill_types');
Route::get('/bill_types/get/{id}', 'App\Http\Controllers\Api\AdminController@list_bill_types');
Route::get('/bill_types/delete/{id}', 'App\Http\Controllers\Api\AdminController@delete_bill_type');

Route::post('/users/create', 'App\Http\Controllers\Api\AdminController@edit_user');
Route::post('/users/edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_user');
Route::post('/users/list', 'App\Http\Controllers\Api\AdminController@list_users');
Route::get('/users/get/{id}', 'App\Http\Controllers\Api\AdminController@list_users');
Route::get('/users/delete/{id}', 'App\Http\Controllers\Api\AdminController@delete_user');

Route::post('/cards/create', 'App\Http\Controllers\Api\AdminController@edit_card');
Route::post('/cards/edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_card');
Route::post('/cards/list', 'App\Http\Controllers\Api\AdminController@list_cards');
Route::get('/cards/get/{id}', 'App\Http\Controllers\Api\AdminController@list_cards');
Route::get('/cards/delete/{id}', 'App\Http\Controllers\Api\AdminController@delete_card');
Route::get('/cards/history/{id}', 'App\Http\Controllers\Api\AdminController@card_history');
Route::patch('/cards/switch_status/{id}', 'App\Http\Controllers\Api\AdminController@switch_card_status');
Route::patch('/cards/attach_user/{number?}', 'App\Http\Controllers\Api\AdminController@card_attach_user');

Route::post('/bill_programs/create', 'App\Http\Controllers\Api\AdminController@edit_bill_program');
Route::post('/bill_programs/edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_bill_program');
Route::post('/bill_programs/list', 'App\Http\Controllers\Api\AdminController@list_bill_programs');
Route::get('/bill_programs/get/{id}', 'App\Http\Controllers\Api\AdminController@get_bill_program');
Route::get('/bill_programs/delete/{id}', 'App\Http\Controllers\Api\AdminController@delete_bill_program');

Route::post('/outlets/create', 'App\Http\Controllers\Api\AdminController@edit_outlet');
Route::post('/outlets/edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_outlet');
Route::post('/outlets/list', 'App\Http\Controllers\Api\AdminController@list_outlets');
Route::get('/outlets/get/{id}', 'App\Http\Controllers\Api\AdminController@list_outlets');
Route::get('/outlets/delete/{id}', 'App\Http\Controllers\Api\AdminController@delete_outlet');
Route::post('/outlets/send_to_nearest/{id}', 'App\Http\Controllers\Api\AdminController@send_to_nearest');

Route::post('/fields/create', 'App\Http\Controllers\Api\AdminController@edit_field');
Route::post('/fields/edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_field');
Route::post('/fields/list', 'App\Http\Controllers\Api\AdminController@list_fields');
Route::get('/fields/get/{id}', 'App\Http\Controllers\Api\AdminController@list_fields');
Route::get('/fields/delete/{id}', 'App\Http\Controllers\Api\AdminController@delete_field');

//Route::post('/sales/create', 'App\Http\Controllers\Api\AdminController@edit_sale');
//Route::post('/sales/edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_sale');
//Route::post('/sales/list', 'App\Http\Controllers\Api\AdminController@list_sales');
//Route::get('/sales/get/{id}', 'App\Http\Controllers\Api\AdminController@list_sales');
//Route::get('/sales/delete/{id}', 'App\Http\Controllers\Api\AdminController@delete_sale');

Route::post('/categories/create', 'App\Http\Controllers\Api\AdminController@edit_category');
Route::post('/categories/edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_category');
Route::post('/categories/sub_create', 'App\Http\Controllers\Api\AdminController@edit_subcategory');
Route::post('/categories/sub_edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_subcategory');
Route::get('/categories/list', 'App\Http\Controllers\Api\AdminController@list_categories');
Route::get('/categories/delete/{id}', 'App\Http\Controllers\Api\AdminController@delete_category');

Route::post('/products/create', 'App\Http\Controllers\Api\AdminController@edit_product');
Route::post('/products/edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_product');
Route::post('/products/list', 'App\Http\Controllers\Api\AdminController@list_products');
Route::get('/products/get/{id}', 'App\Http\Controllers\Api\AdminController@get_product');
Route::get('/products/delete/{id}', 'App\Http\Controllers\Api\AdminController@delete_product');
Route::patch('/products/switch_visibility/{id}', 'App\Http\Controllers\Api\AdminController@switch_product_visibility');


Route::post('/orders/create', 'App\Http\Controllers\Api\AdminController@edit_order');
Route::post('/orders/edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_order');
Route::post('/orders/list', 'App\Http\Controllers\Api\AdminController@list_orders');
Route::get('/orders/get/{id}', 'App\Http\Controllers\Api\AdminController@list_orders');
Route::get('/orders/delete/{id}', 'App\Http\Controllers\Api\AdminController@delete_order');
Route::get('/orders/cancel/{id}', 'App\Http\Controllers\Api\AdminController@cancel_order');

Route::get('/orders/delete_basket/{id}', 'App\Http\Controllers\Api\AdminController@delete_basket');
Route::post('/orders/edit_basket/{id}', 'App\Http\Controllers\Api\AdminController@edit_basket');
Route::post('/orders/add_basket/{sale_id}', 'App\Http\Controllers\Api\AdminController@add_basket');

Route::post('/news/create', 'App\Http\Controllers\Api\AdminController@edit_news');
Route::post('/news/edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_news');
Route::post('/news/list', 'App\Http\Controllers\Api\AdminController@list_news');
Route::get('/news/get/{id}', 'App\Http\Controllers\Api\AdminController@list_news');
Route::get('/news/delete/{id}', 'App\Http\Controllers\Api\AdminController@delete_news');

Route::post('/stocks/create', 'App\Http\Controllers\Api\AdminController@edit_stock');
Route::post('/stocks/edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_stock');
Route::post('/stocks/list', 'App\Http\Controllers\Api\AdminController@list_stocks');
Route::get('/stocks/get/{id}', 'App\Http\Controllers\Api\AdminController@list_stocks');
Route::get('/stocks/delete/{id}', 'App\Http\Controllers\Api\AdminController@delete_stock');

Route::post('/devices/list', 'App\Http\Controllers\Api\AdminController@devices_list');
Route::post('/devices/send_pushes', 'App\Http\Controllers\Api\AdminController@send_pushes');

Route::post('/coupons/create', 'App\Http\Controllers\Api\AdminController@edit_coupon');
Route::post('/coupons/edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_coupon');
Route::post('/coupons/list', 'App\Http\Controllers\Api\AdminController@list_coupons');
Route::get('/coupons/get/{id}', 'App\Http\Controllers\Api\AdminController@list_coupons');
Route::get('/coupons/delete/{id}', 'App\Http\Controllers\Api\AdminController@delete_coupon');

Route::get('/reviews/moderate/{id}', 'App\Http\Controllers\Api\AdminController@moderate_review');
Route::get('/news/moderate/{id}', 'App\Http\Controllers\Api\AdminController@moderate_news');
Route::get('/reviews/list', 'App\Http\Controllers\Api\AdminController@list_reviews');

Route::patch('/bills/edit_value/{id}', 'App\Http\Controllers\Api\AdminController@edit_bill_value');

Route::get('/statistic/sales', 'App\Http\Controllers\Api\StatController@sales');
Route::get('/statistic/product_rates', 'App\Http\Controllers\Api\StatController@product_rates');

Route::get('/translations/rate_store', 'App\Http\Controllers\Api\TranslationController@get_rate_store');
Route::patch('/translations/rate_store', 'App\Http\Controllers\Api\TranslationController@update_rate_store');

/****** CLIENTS ******/

Route::post('/clients/sms', 'App\Http\Controllers\Api\ClientController@send_auth_sms');
Route::post('/clients/login', 'App\Http\Controllers\Api\ClientController@login');
Route::post('/clients/check_auth', 'App\Http\Controllers\Api\ClientController@check_auth');

Route::post('/clients/cards/create', 'App\Http\Controllers\Api\ClientController@edit_card');
Route::post('/clients/cards/edit/{id}', 'App\Http\Controllers\Api\ClientController@edit_card');
Route::get('/clients/cards/bind_card/{number}', 'App\Http\Controllers\Api\ClientController@bind_card');
Route::get('/clients/cards/set_main/{id}', 'App\Http\Controllers\Api\ClientController@set_main');

Route::get('/clients/categories/list', 'App\Http\Controllers\Api\ClientController@list_categories');

Route::post('/clients/products/list', 'App\Http\Controllers\Api\ClientController@list_products');
Route::get('/clients/products/get/{id}', 'App\Http\Controllers\Api\ClientController@get_product');

Route::post('/clients/orders/create', 'App\Http\Controllers\Api\ClientController@edit_order');
Route::post('/clients/orders/list', 'App\Http\Controllers\Api\ClientController@list_order');
Route::get('/clients/orders/get/{id}', 'App\Http\Controllers\Api\ClientController@list_order');
Route::post('/clients/orders/cancel/{id}', 'App\Http\Controllers\Api\ClientController@cancel_order');

Route::post('/clients/news/list', 'App\Http\Controllers\Api\ClientController@list_news');
Route::get('/clients/news/get/{id}', 'App\Http\Controllers\Api\ClientController@list_news');

Route::post('/clients/reviews/create', 'App\Http\Controllers\Api\ClientController@edit_review');
Route::post('/clients/reviews/edit/{id}', 'App\Http\Controllers\Api\ClientController@edit_review');
Route::post('/clients/reviews/list', 'App\Http\Controllers\Api\ClientController@list_reviews');
Route::get('/clients/reviews/get/{id}', 'App\Http\Controllers\Api\ClientController@list_reviews');
Route::get('/clients/reviews/delete/{id}', 'App\Http\Controllers\Api\ClientController@delete_review');

Route::post('/clients/favorites/add', 'App\Http\Controllers\Api\ClientController@add_favorites');
Route::post('/clients/favorites/list', 'App\Http\Controllers\Api\ClientController@list_favorites');
Route::get('/clients/favorites/delete/{product_id}', 'App\Http\Controllers\Api\ClientController@delete_favorites');

Route::post('/clients/outlets/list', 'App\Http\Controllers\Api\ClientController@list_outlets');

Route::post('/clients/stocks/list', 'App\Http\Controllers\Api\ClientController@list_stocks');
Route::get('/clients/stocks/get/{id}', 'App\Http\Controllers\Api\ClientController@list_stocks');

Route::post('/clients/cards/list', 'App\Http\Controllers\Api\ClientController@list_cards');

Route::get('/clients/profile', 'App\Http\Controllers\Api\ClientController@profile');
Route::post('/clients/profile/edit', 'App\Http\Controllers\Api\ClientController@edit_profile');

Route::post('/clients/device_init', 'App\Http\Controllers\Api\ClientController@device_init');

Route::post('/clients/bill_programs/list', 'App\Http\Controllers\Api\ClientController@list_bill_programs');

Route::post('/clients/coupons/list', 'App\Http\Controllers\Api\ClientController@list_coupons');
Route::get('/clients/coupons/get/{id}', 'App\Http\Controllers\Api\ClientController@list_coupons');

Route::post('/clients/bonus_history/get/{bill_id}', 'App\Http\Controllers\Api\ClientController@bonus_history');

Route::post('/clients/set_location', 'App\Http\Controllers\Api\ClientController@set_location');

Route::post('/clients/send_support_email', 'App\Http\Controllers\Api\ClientController@send_support_email');

/****** OUTLETS ******/

Route::post('/outlets/sales/create', 'App\Http\Controllers\Api\OutletController@edit_sale');
Route::post('/outlets/sales/edit/{sale_id}', 'App\Http\Controllers\Api\OutletController@edit_sale');
Route::get('/outlets/sales/cancel/{sale_id}', 'App\Http\Controllers\Api\OutletController@cancel_sale');
Route::post('/outlets/users/list', 'App\Http\Controllers\Api\OutletController@list_users');
Route::post('/outlets/users/find_by_phone', 'App\Http\Controllers\Api\OutletController@find_user_by_phone');

Route::get('/outlets/sales/get/{sale_id}', 'App\Http\Controllers\Api\OutletController@get_sale');
Route::get('/outlets/card/get/{card_number}', 'App\Http\Controllers\Api\OutletController@get_card');

Route::get('/outlets/coupon/get/{id}', 'App\Http\Controllers\Api\OutletController@get_coupon');

/****** TEST ******/

Route::post('/test', 'App\Http\Controllers\Api\TestController@test');
