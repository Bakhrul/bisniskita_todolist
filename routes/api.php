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
    Route::post('/add_member_project','ProjectController@add_member_project');
    Route::post('/add_todo_project','ProjectController@add_todo_project');
    Route::post('/delete_member_project','ProjectController@delete_member_project');
    Route::post('/delete_todo_project','ProjectController@delete_todo_project');
    Route::post('/update_status_member_project','ProjectController@update_status_member_project');
    Route::post('/update_status_todo_project','ProjectController@update_status_todo_project');
    Route::post('/getdata_project','ProjectController@getdata_project');

});