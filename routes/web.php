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
    return redirect('https://github.com/necrogami/ssh.pub');
});

Route::group(['prefix' => 'key/{email}'], function () {

    Route::get('/', 'KeyController@getIndex');
    Route::post('/', 'KeyController@postIndex');
    Route::delete('/', 'KeyController@deleteIndex');
    Route::get('upload', 'KeyController@getUpload');
    Route::get('install', 'KeyController@getInstall');
    Route::get('all', 'KeyController@getAll');
    Route::get('all/install', 'KeyController@getAllInstall');
    Route::get('fingerprint', 'KeyController@getFingerprint');
    Route::get('confirm/{token}', 'KeyController@getConfirmToken');

    Route::group(['prefix' => '{keyname}'], function () {

        Route::get('/', 'KeyController@getIndex');
        Route::post('/', 'KeyController@postIndex');
        Route::delete('/', 'KeyController@deleteIndex');
        Route::get('upload', 'KeyController@getUpload');
        Route::get('install', 'KeyController@getInstall');
        Route::get('fingerprint', 'KeyController@getFingerprint');

    });
});
