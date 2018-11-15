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
//Route::middleware('auth:api')->get('/test', 'UserController@test');

//Auth::routes();
//
//Route::get('/home', 'HomeController@index')->name('home');
//所有页面检查访问check
Route::get("/check", function(){
    return [
        'code'=>-1,
        'data'=>[],
        'error'=>"Unauthorized",
    ];
})->name('check');

//用户
Route::post('/user/login', 'UserController@login');
Route::post('/user/register', 'UserController@register');
Route::get('/cache', 'WorkerController@storeWorker');
//Route::get('/search', 'WorkerController@searchCache');

Route::group(['middleware'=>'auth:api'], function(){
    Route::get('/user/get', 'UserController@get');
    Route::post('/user/salary', 'UserController@addSalary');
    Route::post('/user/fund', 'UserController@allocateFund');
    Route::post('/user/pay', 'UserController@payCash');
    //工人
    Route::post('/worker/register', 'WorkerController@register');
    Route::get('/worker/get/{id}', 'WorkerController@get');
    Route::get('/worker/searchdown/{name?}', 'WorkerController@searchSimple');
    Route::get('/worker/search', 'WorkerController@search');
//    Route::get('/worker/search', 'WorkerController@searchCache');
    Route::get('/worker/salarylist/{worker_id}', 'WorkerController@salaryList');

    //统计报表
    Route::get('/report/worker/list', 'ReportController@wokerList');

    Route::get('/report/worker/contact/{worker_id}', 'ReportController@workerContact');
    Route::get('/report/user/contact', 'ReportController@userContact');
    Route::get('/report/user/fundlist', 'ReportController@fundList');
    Route::get('/report/user/paylist', 'ReportController@payList');
});

//公共配置
Route::get('/settings/{key}', 'SettingsController@get');
Route::post('/settings/update/{key}', 'SettingsController@update');


