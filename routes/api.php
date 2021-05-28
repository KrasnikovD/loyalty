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

Route::post('/bill_types/create', 'App\Http\Controllers\Api\AdminController@edit_bill_type');
Route::post('/bill_types/edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_bill_type');
Route::get('/bill_types/list', 'App\Http\Controllers\Api\AdminController@list_bill_types');
Route::get('/bill_types/get/{id}', 'App\Http\Controllers\Api\AdminController@list_bill_types');
Route::get('/bill_types/delete/{id}', 'App\Http\Controllers\Api\AdminController@delete_bill_type');

Route::post('/users/create', 'App\Http\Controllers\Api\AdminController@edit_user');
Route::post('/users/edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_user');
Route::get('/users/list', 'App\Http\Controllers\Api\AdminController@list_users');
Route::get('/users/get/{id}', 'App\Http\Controllers\Api\AdminController@list_users');
Route::get('/users/delete/{id}', 'App\Http\Controllers\Api\AdminController@delete_user');

Route::post('/cards/create', 'App\Http\Controllers\Api\AdminController@edit_card');
Route::post('/cards/edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_card');
Route::get('/cards/list', 'App\Http\Controllers\Api\AdminController@list_cards');
Route::get('/cards/get/{id}', 'App\Http\Controllers\Api\AdminController@list_cards');
Route::get('/cards/delete/{id}', 'App\Http\Controllers\Api\AdminController@delete_card');

Route::post('/bill_programs/create', 'App\Http\Controllers\Api\AdminController@edit_bill_program');
Route::post('/bill_programs/edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_bill_program');
Route::get('/bill_programs/list/{bill_id?}', 'App\Http\Controllers\Api\AdminController@list_bill_programs');
Route::get('/bill_programs/get/{id}', 'App\Http\Controllers\Api\AdminController@get_bill_program');
Route::get('/bill_programs/delete/{id}', 'App\Http\Controllers\Api\AdminController@delete_bill_program');

Route::post('/outlets/create', 'App\Http\Controllers\Api\AdminController@edit_outlet');
Route::post('/outlets/edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_outlet');
Route::get('/outlets/list', 'App\Http\Controllers\Api\AdminController@list_outlets');
Route::get('/outlets/get/{id}', 'App\Http\Controllers\Api\AdminController@list_outlets');
Route::get('/outlets/delete/{id}', 'App\Http\Controllers\Api\AdminController@delete_outlet');

Route::post('/fields/create', 'App\Http\Controllers\Api\AdminController@edit_field');
Route::post('/fields/edit/{id}', 'App\Http\Controllers\Api\AdminController@edit_field');
Route::get('/fields/list', 'App\Http\Controllers\Api\AdminController@list_fields');
Route::get('/fields/get/{id}', 'App\Http\Controllers\Api\AdminController@list_fields');
Route::get('/fields/delete/{id}', 'App\Http\Controllers\Api\AdminController@delete_field');
