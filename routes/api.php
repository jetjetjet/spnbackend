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
Route::get('/cekSurat', 'EncSuratController@cekKode');
Route::post('/suratKeluar/cetak/{id?}', 'SuratKeluarController@cetakNomor');

Route::get('/tes', 'SuratKeluarController@footer');

Route::group(['middleware' => 'auth:sanctum'], function() {
    
    Route::get('/dashboardTugas', 'DashboardController@getTugas');
    Route::get('/cekUser', 'AuthController@getAuthUser');
    Route::post('/logout', 'AuthController@logout');

    Route::get('/auditTrail/list', 'AuditTrailController@getAll')->middleware('can:auditTrail_list');

    Route::get('/jabatan/list', 'PositionController@getAll');
    Route::get('/jabatan/view/{id?}', 'PositionController@getById');
    Route::post('/jabatan/save/{id?}', 'PositionController@save')->middleware('can:jabatan_save');
    Route::post('/jabatan/delete/{id?}', 'PositionController@delete')->middleware('can:jabatan_delete');
    Route::get('/jabatan/search', 'PositionController@searchPosition');
    Route::get('/jabatan/searchParent', 'PositionController@searchParentPosition');
    //PERMISSION
    Route::get('/jabatan/permission/all', 'PermissionController@getAllPermission')->middleware('can:jabatan_permission');
    Route::get('/jabatan/permission/granted/{idJabatan}', 'PermissionController@getPositionPermission')->middleware('can:jabatan_permission');
    Route::post('/jabatan/permission/save/{idJabatan?}', 'PermissionController@savePositionPermission')->middleware('can:jabatan_permission');

    Route::get('/klasifikasi/list', 'KlasifikasiSuratController@getAll');
    Route::get('/klasifikasi/view/{id?}', 'KlasifikasiSuratController@getById');
    Route::post('/klasifikasi/save/{id?}', 'KlasifikasiSuratController@save')->middleware('can:klasifikasiSurat_save');
    Route::post('/klasifikasi/delete/{id?}', 'KlasifikasiSuratController@delete')->middleware('can:klasifikasiSurat_delete');
    Route::get('/klasifikasi/search', 'KlasifikasiSuratController@searchKlasifikasi');
    
    Route::get('/notif/topbar/count', 'NotificationController@getCount');
    Route::get('/notif/topbar/view', 'NotificationController@getNotif');
    Route::get('/notif/all', 'NotificationController@getAll');
    Route::post('/notif/read/{id}/{subid}/{type}', 'NotificationController@read');

    Route::get('/profile/view/{id?}', 'UserController@getProfile');
    Route::post('/profile/save', 'UserController@saveProfile');
    Route::post('/profile/uploadPhoto/{id}', 'UserController@uploadProfileFoto');
    Route::post('/profile/changePassword/{id?}', 'UserController@changeProfilePassword');

    Route::get('/suratKeluar/list', 'SuratKeluarController@getAll');
    Route::get('/suratKeluar/view/{id?}', 'SuratKeluarController@getById');
    Route::post('/suratKeluar/save/{id?}', 'SuratKeluarController@save')->middleware('can:suratKeluar_save');
    Route::post('/suratKeluar/delete/{id}', 'SuratKeluarController@delete')->middleware('can:suratKeluar_delete');
    Route::post('/suratKeluar/read/{idDisposisi?}', 'SuratKeluarController@read');
    Route::post('/suratKeluar/approve', 'DisSuratKeluarController@disposisiSuratKeluar')->middleware('can:suratKeluar_approve');
    Route::post('/suratKeluar/verify/{id?}', 'SuratKeluarController@verifySuratKeluar')->middleware('can:suratKeluar_verify');
    Route::post('/suratKeluar/generate/{id?}', 'SuratKeluarController@generateNomorSurat')->middleware('can:suratKeluar_agenda');
    Route::post('/suratKeluar/agenda/{id?}', 'SuratKeluarController@agendaSuratKeluar')->middleware('can:suratKeluar_agenda');
    Route::post('/suratKeluar/sign/{id}', 'SuratKeluarController@signSurat')->middleware('can:suratKeluar_sign');
    Route::post('/suratKeluar/void/{id}', 'SuratKeluarController@void')->middleware('can:suratKeluar_void');

    Route::get('/suratMasuk/list', 'SuratMasukController@getAll');
    Route::get('/suratMasuk/view/{id?}', 'SuratMasukController@getById');
    Route::post('/suratMasuk/save/{id?}', 'SuratMasukController@save')->middleware('can:suratMasuk_save'); //->middleware('can:suratMasuk_save');
    Route::post('/suratMasuk/delete/{id}', 'SuratMasukController@delete')->middleware('can:suratMasuk_delete');
    Route::post('/suratMasuk/read/{idDisposisi?}', 'SuratMasukController@read');
    Route::post('/suratMasuk/disposisi', 'DisSuratMasukController@disposisiSuratMasuk')->middleware('can:suratMasuk_disposition');
    Route::post('/suratMasuk/close/{id?}', 'SuratMasukController@tutupSuratMasuk')->middleware('can:suratMasuk_close');

    Route::get('/templateSurat/list', 'TemplateSuratController@getAll');
    Route::get('/templateSurat/view/{id?}', 'TemplateSuratController@getById');
    Route::post('/templateSurat/save/{id?}', 'TemplateSuratController@save')->middleware('can:templateSurat_save');
    Route::post('/templateSurat/delete/{id}', 'TemplateSuratController@delete')->middleware('can:templateSurat_delete');

    Route::get('/unit/list', 'GroupController@getAll');
    Route::get('/unit/view/{id?}', 'GroupController@getById');
    Route::post('/unit/save/{id?}', 'GroupController@save')->middleware('can:unit_save');
    Route::post('/unit/delete/{id?}', 'GroupController@delete')->middleware('can:unit_delete');
    Route::get('/unit/search', 'GroupController@searchGroup');
    
    Route::get('/user/list', 'UserController@getAll')->middleware('can:user_list');
    Route::get('/user/view/{id?}', 'UserController@getById');
    Route::get('/user/createTtdId/{id}', 'UserController@createIdTtd')->middleware('can:user_createttd');
    Route::get('/user/search', 'UserController@searchUser');
    Route::get('/user/searchSM', 'UserController@searchUserSM');
    Route::get('/user/searchSK', 'UserController@searchUserSK');
    Route::get('/user/searchTtd', 'UserController@searchUserTtd');
    Route::post('/user/save/{id?}', 'UserController@save')->middleware('can:user_save');
    Route::post('/user/delete/{id?}', 'UserController@delete')->middleware('can:user_delete');
    Route::post('/user/changePassword/{id?}', 'UserController@changePassword')->middleware('can:user_save');
    Route::post('/user/uploadPhoto/{id}', 'UserController@uploadFoto')->middleware('can:user_save');
    Route::post('/user/savettd/{id}', 'UserController@simpanTTD')->middleware('can:user_delete');
    
});