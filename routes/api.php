<?php

use Illuminate\Http\Request;

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

Route::group(['middleware' => 'auth:api'], function(){

	Route::get('/listkategori','KategoriController@getDataKategori');
    Route::get('/user', 'UserController@user');
    //=============================|End|=========================================
    //=============================|Project|=========================================
    Route::get('/project','ProjectController@index');
    Route::post('/create_project','ProjectController@create_project');
    Route::post('/detail_project','ProjectController@detail_project');

});