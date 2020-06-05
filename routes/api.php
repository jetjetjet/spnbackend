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
    Route::get('/jabatan/list', 'PositionController@getAll');
    Route::get('/jabatan/view/{id?}', 'PositionController@getById');
    Route::post('/jabatan/save/{id?}', 'PositionController@save');
    Route::post('/jabatan/delete/{id?}', 'PositionController@delete');
    Route::post('/jabatan/search', 'PositionController@searchPosition');

    Route::get('/unit/list', 'GroupController@getAll');
    Route::get('/unit/view/{id?}', 'GroupController@getById');
    Route::post('/unit/save/{id?}', 'GroupController@save');
    Route::post('/unit/delete/{id?}', 'GroupController@delete');
    Route::post('/unit/search', 'GroupController@searchGroup');
    
    Route::get('/user/list', 'UserController@getAll');
    Route::get('/user/view/{id?}', 'UserController@getById');
    Route::post('/user/save/{id?}', 'UserController@save');
    Route::post('/user/delete/{id?}', 'UserController@delete');
    Route::post('/user/changePassword/{id?}', 'UserController@changePassword');
    Route::post('/user/search', 'UserController@searchUser');
    Route::post('/user/uploadPhoto/{id}', 'UserController@uploadFoto');

    Route::get('/menu/allMenuPermission', 'MenuController@getAllMenuPermission');
    Route::get('/menu/list', 'MenuController@getAll');
    Route::get('/menu/view/{id?}', 'MenuController@getById');
    Route::get('/menu/sidebar', 'MenuController@getMenuSideBar');
    Route::post('/menu/save/{id?}', 'MenuController@save');
    Route::post('/menu/delete/{id?}', 'MenuController@delete');

    Route::post('/suratKeluar/save/{id?}', 'SuratKeluarController@save');
    route::post('/suratKeluar/read/{idDisposisi?}', 'SuratKeluarController@read');
    Route::post('/suratKeluar/disposisi', 'DisSuratKeluarController@disposisiSuratKeluar');
    
    Route::post('/logout', 'AuthController@logout');
});