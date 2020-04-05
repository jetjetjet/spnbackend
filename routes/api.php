<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/login', 'AuthController@login');

Route::group(['middleware' => 'auth:sanctum'], function() {
    Route::get('/jabatan/list', 'GroupController@getAll');
    Route::get('/jabatan/view/{id?}', 'GroupController@getById');
    Route::post('/jabatan/save/{id?}', 'GroupController@save');
    Route::post('/jabatan/delete/{id?}', 'GroupController@delete');
    
    Route::get('/user/list', 'UserController@getAll');
    Route::get('/user/view/{id?}', 'UserController@getById');
    Route::post('/user/save/{id?}', 'UserController@save');
    Route::post('/user/delete/{id?}', 'UserController@delete');
    Route::post('/user/changePassword/{id?}', 'UserController@changePassword');

    
    Route::get('/menu/allMenuPermission', 'MenuController@getAllMenuPermission');

    
Route::post('/logout', 'AuthController@logout');
});