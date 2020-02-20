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

Route::get('/', function () {
    return view('welcome');
});

// Command Line (PHP ARTISAN)
//Testing URL
Route::get('/test', function() {
	try {
	   return '<h1>Cache facade value cleared</h1>';	
	} catch (Exception $e) {
	   return $e;	
	}
    
});

// command line untuk mengaktifkan laravel passport tanpa cli/terminal
// php artisan passport:install
Route::get('/passport-install', function () {
    try{
    return Artisan::call('passport:install');       
    }catch(Exception $e){
        return $e;
    }
});

//Clear Cache facade value:
// php artisan clear:cache
Route::get('/clear-cache', function() {
    try {
    Artisan::call('cache:clear');
        return '<h1>Cache facade value cleared</h1>';    
    } catch (Exception $e) {
        return $e;    
    }
    
});

//Reoptimized class loader:
// php artisan optimize 
Route::get('/optimize', function() {
    try {
        Artisan::call('optimize');
        return '<h1>Reoptimized class loader</h1>';
    } catch (Exception $e) {
        return $e;
    }
});

//Route cache:
// php artisan route:cache
Route::get('/route-cache', function() {
    try {
        Artisan::call('route:cache');
        return '<h1>Routes cached</h1>';
    } catch (Exception $e) {
        return $e;        
    }
});

//Clear Route cache:
// php artisan route:clear
Route::get('/route-clear', function() {
    try {
        Artisan::call('route:clear');
        return '<h1>Route cache cleared</h1>';
    } catch (Exception $e) {
        return $e;    
    }
});

//Clear View cache:
// php artisan view:clear
Route::get('/view-clear', function() {
    try {
        Artisan::call('view:clear');    
        return '<h1>View cache cleared</h1>';
    } catch (Exception $e) {
        return $e;
    }
    
});

//Clear Config cache:
// php artisan config:cache
Route::get('/config-cache', function() {
    try {
        Artisan::call('config:cache');    
        return '<h1>Clear Config cleared</h1>';
    } catch (Exception $e) {
        return $e;        
    }
    
});