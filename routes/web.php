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

Route::get('/', 'AngularController@serve');
Route::post('/post', 'AngularController@post');
Route::get('/login', 'AngularController@serve');
Route::get('/other', 'AngularController@serve');
Route::post('/auth/register', 'AngularController@newUser');
Route::post('/resource/path', 'AngularController@getIcon');
Route::get('/users-list', 'AngularController@serve');
Route::get('/ad/{id}', 'AngularController@ads');


/**
 * Authenticate.
 */
Route::group(['prefix' => 'auth', 'middleware' => ['web']], function() {
    Route::post('api/register', 'TokenAuthController@register');
    Route::post('api/authenticate', 'TokenAuthController@authenticate');
    Route::get('api/authenticate/user', 'TokenAuthController@getAuthenticatedUser');


    Route::post('data', 'AuthController@getData');
    Route::post('login', 'AuthController@login');
    Route::get('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::get('me', 'AuthController@me');

});

/**
 * Content regulations.
 */
Route::group(['prefix' => 'content', 'middleware' => ['web']], function() {
    Route::post('getUsers', 'AuthController@getUsersList');
    Route::post('getApsList', 'AuthController@getAps');
    Route::post('getAppPhotos', 'AuthController@getApPhotos');
    Route::post('uploadAvatar', 'AuthController@uploadPhoto');
    Route::post('getUserAva', 'AuthController@getUserAva');
    Route::post('getAd', 'AuthController@getAd');
    Route::post('loadPermissions', 'AuthController@loadPermissions');
    Route::post('uploadAdImages', 'AuthController@uploadAdImages');
    Route::post('saveAvatarGlobalUrl', 'AuthController@saveAvatarGlobalUrl');
    Route::post('saveAdImgGlobalUrl', 'AuthController@saveAdImgGlobalUrl');
    Route::delete('deleteCity', 'AuthController@deleteCityAction');
    Route::delete('deleteAdImage', 'AuthController@deleteAdImage');
    Route::post('parseStart', 'AuthController@parseStart');
    Route::post('getAdById', 'AuthController@getAdById');
    Route::get('sseStart', 'AuthController@sseService');
    Route::post('proxyParseStart', 'AuthController@ParseProxyService');
});

