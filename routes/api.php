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
Route::post('/user/register', 'UserController@register');

Route::group(['middleware' => 'auth:api'], function(){

	Route::get('/listkategori','KategoriController@getDataKategori');
    Route::get('/user', 'UserController@user');
    Route::patch('/user', 'UserController@update');
    //=============================|End|=========================================
    //=============================|Category|=========================================
    Route::get('/category','ToDoController@category');

    //=============================|Todo|=========================================
    Route::get('/todo/{index}','ToDoController@index');
    Route::post('/todo/create','ToDoController@store');
    //=============================|Project|=========================================
    Route::get('/project','ProjectController@index');
    Route::post('/create_project','ProjectController@create_project');
    Route::post('/detail_project','ProjectController@detail_project');

});