<?php


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', 'HomeController@home');


Auth::routes();

Route::get('/admin', 'HomeController@index');

Route::post('/add_cat', 'HomeController@add_cat');

Route::post('/get_children', 'HomeController@get_children');

Route::post('add_file', 'HomeController@add_file');

Route::post('check_files', 'HomeController@check_files');


Route::get('download/{file_id}', 'HomeController@download');

Route::post('edit_cat', 'HomeController@edit_cat');

Route::post('delete_cat', 'HomeController@delete_cat');

Route::post('set_rights', 'HomeController@set_rights');
