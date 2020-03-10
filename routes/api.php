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

Route::post('/reset_password','ResetPasswordController@reminder_password');

Route::group(['middleware' => 'auth:api'], function(){

    Route::get('/listkategori','KategoriController@getDataKategori');
    //=============================|User|=========================================    
    Route::get('/user', 'UserController@user');
    Route::patch('/user', 'UserController@update');
    Route::patch('/user/profile/updateimage', 'UserController@updateProfile');
    Route::patch('/user/profile/deleteimage', 'UserController@destroyProfile');
    //=============================|End|=========================================
    //=============================|Category|=========================================
    Route::get('/category','ToDoController@category');
    Route::post('/tambah_kategori','KategoriController@tambah_kategori');

    //=============================|Todo|=========================================
    Route::get('/todo/{index}','ToDoController@index');
    Route::get('/todo/attachment/{todo}','ToDoController@getFiles');
    Route::post('/todo/attachment','ToDoController@store_attachment');
    Route::post('/todo/create','ToDoController@store');
    Route::get('/todo/edit/{id}','ToDoController@edit');
    Route::patch('/todo/update/{id}', 'ToDoController@update');

    Route::get('/todo/todo_ready/{id}','ToDoController@todo_ready');
    Route::get('/todo/todo_normal/{id}','ToDoController@todo_normal');
    Route::get('/todo/todo_done/{id}','ToDoController@todo_done');
    Route::post('/todo/started-todo','ToDoController@started_todo');

    Route::post('todo_edit/tambah_member','ToDoController@todo_edit_addmember');
    Route::post('todo_edit/delete_member','ToDoController@todo_edit_deletemember');
    Route::post('todo_edit/ganti_statusmember','ToDoController@todo_edit_ganti_statusmember');
    Route::post('todo_edit/tambah_file','ToDoController@store_attachment');
    Route::post('todo/list/validation','ToDoController@validation_listtodo');
    Route::get('/todo/list/actions/{id}','ToDoController@getTodoAction');
    Route::post('/todo/list/actions','ToDoController@storeAction');
    Route::patch('/todo/list/actions/{id}','ToDoController@updateAction');
    Route::get('/todo/search/peserta','ToDoController@getPesertaFilter');
    Route::get('/todo/peserta/{todo}/{access}','ToDoController@getPeserta');
    Route::post('/todo/peserta/create','ToDoController@storePeserta');
    Route::delete('/todo/peserta/delete/{user}/{todo}','ToDoController@destroyPeserta');
    Route::delete('/todo/attachment/{id}', 'ToDoController@destroyFile');
    Route::post('/detail_member_todo','ToDoController@detail_member_todo');
    Route::post('/realisasi_todo','ToDoController@realisasi_todo');
    Route::post('/delete_todo','ToDoController@delete_todo');

    //=============================|Project|=========================================
    Route::get('/history', 'ToDoController@getHistory');
    Route::get('/archive', 'ToDoController@getArchive');

    Route::post('/actionpinned_todo','ToDoController@actionpinned_todo');
    Route::post('/todolist_berbintang','ToDoController@todolist_berbintang');
    Route::post('/detail_todo','ToDoController@detail_todo');
    Route::post('/todo_activity','ToDoController@todo_activity');
    //=============================|Project|=========================================
    Route::get('/dashboard','ProjectController@dashboard');
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
    Route::post('/filter_detail_project','ProjectController@filter_detail_project');
    Route::post('/detail_project_all','ProjectController@detail_project_all');
    Route::post('/searc_todo_project','ToDoController@search_todo_project');
    Route::post('/detail_member_project','ProjectController@detail_member_project');
    Route::post('/delete_project','ProjectController@delete_project');

    Route::post('/update_data_project','ProjectController@update_data_project');
    Route::post('/project/started-project','ProjectController@started_project');

    Route::get('/userdetail/{id}','UserController@detail_user');

    Route::get('/get_friendlist','FriendListController@get_friendlist');
    Route::get('/get_friendlist/filter/{name}','FriendListController@get_friendlist_filter');
    Route::post('/tambah_teman','FriendListController@tambah_teman');
    Route::post('/konfirmasiTeman','FriendListController@konfirmasiTeman');
    Route::post('/hapus_teman','FriendListController@hapus_teman');
    Route::get('/get_confirmation_friend','FriendListController@get_confirmation_friend');
    Route::post('/get_friend_acc','FriendListController@get_friend_acc');
});