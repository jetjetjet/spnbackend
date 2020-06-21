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
Route::post('/forgotPassword', 'AuthController@forgotPassword');
Route::post('/resetPassword', 'AuthController@resetPassword');

Route::group(['middleware' => 'auth:sanctum'], function() {
    
    Route::post('/logout', 'AuthController@logout');

    Route::get('/auditTrail/list', 'AuditTrailController@getAll');

    Route::get('/jabatan/list', 'PositionController@getAll');
    Route::get('/jabatan/view/{id?}', 'PositionController@getById');
    Route::post('/jabatan/save/{id?}', 'PositionController@save');
    Route::post('/jabatan/delete/{id?}', 'PositionController@delete');
    Route::get('/jabatan/search', 'PositionController@searchPosition');
    //PERMISSION
    Route::get('/jabatan/permission/all', 'PermissionController@getAllPermission');
    Route::get('/jabatan/permission/granted/{idJabatan}', 'PermissionController@getPositionPermission');
    Route::get('/jabatan/savePermission/{idJabatan?}', 'PermissionController@savePositionPermission');

    Route::get('/menu/allMenuPermission', 'MenuController@getAllMenuPermission');
    Route::get('/menu/list', 'MenuController@getAll');
    Route::get('/menu/view/{id?}', 'MenuController@getById');
    Route::get('/menu/sidebar', 'MenuController@getMenuSideBar');
    Route::post('/menu/save/{id?}', 'MenuController@save');
    Route::post('/menu/delete/{id?}', 'MenuController@delete');

    Route::get('/suratKeluar/list', 'SuratKeluarController@getAll');
    Route::get('/suratKeluar/view/{id?}', 'SuratKeluarController@getById');
    Route::post('/suratKeluar/save/{id?}', 'SuratKeluarController@save');
    Route::post('/suratKeluar/delete/{id}', 'SuratKeluarController@delete');
    Route::post('/suratKeluar/read/{idDisposisi?}', 'SuratKeluarController@read');
    Route::post('/suratKeluar/disposisi', 'DisSuratKeluarController@disposisiSuratKeluar');
    Route::post('/suratKeluar/agenda/{id?}', 'SuratKeluarController@agendaSuratKeluar');
    Route::post('/suratKeluar/approve/{id?}', 'SuratKeluarController@approveSuratKeluar');

    Route::get('/suratMasuk/list', 'SuratMasukController@getAll');
    Route::get('/suratMasuk/view/{id?}', 'SuratMasukController@getById');
    Route::post('/suratMasuk/save/{id?}', 'SuratMasukController@save'); //->middleware('can:suratMasuk_save');
    Route::post('/suratMasuk/delete/{id}', 'SuratMasukController@delete');
    Route::post('/suratMasuk/read/{idDisposisi?}', 'SuratMasukController@read');
    Route::post('/suratMasuk/disposisi', 'DisSuratMasukController@disposisiSuratMasuk');
    Route::post('/suratMasuk/close/{id?}', 'SuratMasukController@tutupSuratMasuk');

    Route::get('/templateSurat/list', 'TemplateSuratController@getAll');
    Route::get('/templateSurat/view/{id?}', 'TemplateSuratController@getById');
    Route::post('/templateSurat/save/{id?}', 'TemplateSuratController@save');
    Route::post('/templateSurat/delete/{id}', 'TemplateSuratController@delete');

    Route::get('/unit/list', 'GroupController@getAll');
    Route::get('/unit/view/{id?}', 'GroupController@getById');
    Route::post('/unit/save/{id?}', 'GroupController@save');
    Route::post('/unit/delete/{id?}', 'GroupController@delete');
    Route::get('/unit/search', 'GroupController@searchGroup');
    
    Route::get('/user/list', 'UserController@getAll');
    Route::get('/user/view/{id?}', 'UserController@getById');
    Route::post('/user/save/{id?}', 'UserController@save');
    Route::post('/user/delete/{id?}', 'UserController@delete');
    Route::post('/user/changePassword/{id?}', 'UserController@changePassword');
    Route::get('/user/search', 'UserController@searchUser');
    Route::post('/user/uploadPhoto/{id}', 'UserController@uploadFoto');
});