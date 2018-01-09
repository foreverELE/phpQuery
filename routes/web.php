<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/',function(){
    return '首页';
});

Route::get('/range','QueryListController@index')->name('range');
Route::get('/remove_head','QueryListController@removeHead')->name('remove_head');
Route::get('/query','QueryListController@query')->name('query');
Route::get('/get_data','QueryListController@getData')->name('get_data');
Route::get('/get_goods_info','QueryListController@getGoodsInfo')->name('get_goods_info');
Route::get('/get_login_form','QueryListController@getLoginForm')->name('get_login_form');
Route::get('/get_goods_list','QueryListController@getGoodsLists')->name('get_goods_list');\


Route::get('/test','QueryListController@Test')->name('test');
Route::get('/api_get','QueryListController@apiGet')->name('api_get');
Route::get('/get_bid','QueryListController@getBid')->name('get_bid');
