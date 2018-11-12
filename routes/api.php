<?php

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
//用户
Route::post('/user/login', 'UserController@login');
Route::post('/user/register', 'UserController@register');
Route::get('/user/fund', 'UserController@allocateFund');

//工人
Route::post('/worker/register', 'WorkerController@register');
Route::get('/worker/get/{id}', 'WorkerController@get');
Route::get('/worker/search/{name?}', 'WorkerController@searchSimple');


//公共配置
Route::get('/settings/{key}', 'SettingsController@get');


Route::middleware('auth:api')->get('/report/user/contact', 'ReportController@userContact');

Route::get('/redirect', function (){
    $query = http_build_query([
        'client_id' => '4',
        'redirect_uri' => 'http://jz.api.com:8080/auth/callback',
        'response_type' => 'code',
        'scope' => '',
    ]);

    return redirect('http://jz.api.com:8080/oauth/authorize?' . $query);
});