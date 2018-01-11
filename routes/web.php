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

Route::get('/', 'QueryListController@index');

Route::get('/range','QueryListController@range')->name('range');
Route::get('/remove_head','QueryListController@removeHead')->name('remove_head');
Route::get('/query','QueryListController@query')->name('query');
Route::get('/get_data','QueryListController@getData')->name('get_data');
Route::get('/get_goods_info','QueryListController@getGoodsInfo')->name('get_goods_info');
Route::get('/get_login_form','QueryListController@getLoginForm')->name('get_login_form');
Route::get('/get_goods_list','QueryListController@getGoodsLists')->name('get_goods_list');
Route::get('/get_auction_category','QueryListController@getAuctionCategory')->name('get_auction_category');

Route::get('/get_category','QueryListController@getCategory')->name('get_category');
Route::get('/show_category','QueryListController@showCategory')->name('show_category');


Route::get('/test','QueryListController@Test')->name('test');
Route::get('/api_get','QueryListController@apiGet')->name('api_get');
Route::get('/get_bid','QueryListController@getBid')->name('get_bid');


//baidu翻译测试
Route::get('/translate','TranslateController@index')->name('translate');
