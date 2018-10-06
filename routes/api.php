<?php

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

use App\Http\Traits\SendFCMNotification;

$base_url = "";
Route::group(['prefix' => 'auth'], function () use ($base_url) {

    Route::group(['prefix' => 'user'], function () use ($base_url) {
        //unauthenticated routes
        Route::post('register', $base_url . 'Api\AuthController@register_user');
        Route::post('login', $base_url . 'Api\AuthController@login_user');
        Route::post('reset_password_email', $base_url . 'Api\AuthController@reset_password_user');

        //authenticated
        Route::group(['middleware' => 'auth:api'], function () use ($base_url) {
            Route::get('users', $base_url . 'Api\AuthController@index');
            Route::post('update_auto_follow_status', $base_url . 'Api\AuthController@update_auto_follow_status');
            Route::post('update_fcm_token', $base_url . 'Api\AuthController@update_fcm_token');
        });
    });

    Route::group(['prefix' => 'scanner'], function () use ($base_url) {
        ///unauthenticated routes
        Route::post('register', $base_url . 'Api\ScannerAuthController@register');
        Route::post('login', $base_url . 'Api\ScannerAuthController@login');
        Route::post('reset_password_email', $base_url . 'Api\ScannerAuthController@reset_password');

        //authenticated ones
        Route::group(['middleware' => 'auth:api'], function () use ($base_url) {
            Route::get('scanners', $base_url . 'Api\ScannerAuthController@index');
        });
    });
});

Route::group(['prefix' => 'user', 'middleware' => 'auth:api'], function () use ($base_url) {
    Route::get('adverts', $base_url . 'Api\AdvertController@index');
    Route::post('adverts_view', $base_url . 'Api\AdvertController@advert_view');
    Route::get('countries', $base_url . 'Api\CountryController@index');
    Route::get('events/{user_id}', $base_url . 'Api\EventController@index');
    Route::get('notifications/{user_id}', $base_url . 'Api\NotificationController@index');
    Route::post('notifications', $base_url . 'Api\NotificationController@markSeen');
    Route::get('venues/{user_id}', $base_url . 'Api\VenueController@index');
    Route::post('follow_venue', $base_url . 'Api\VenueController@follow_venue');
    Route::get('posts/{user_id}', $base_url . 'Api\PostController@index');
    Route::post('posts', $base_url . 'Api\PostController@store');
    Route::post('delete_post', $base_url . 'Api\PostController@delete');
    Route::post('like_post', $base_url . 'Api\PostController@like');
    Route::post('report_abuse', $base_url . 'Api\PostController@report_abuse');
    Route::get('{id}/relations', $base_url . 'Api\AuthController@user_relations');
    Route::post('follow', $base_url . 'Api\AuthController@follow');
});

Route::group(['prefix' => 'scanner', 'middleware' => 'api'], function () use ($base_url) {
    Route::get('events/{scanner_id}', $base_url . 'Api\EventController@scanner_events');

});

Route::get('payments/{user_id}/{event_id}', $base_url . 'Api\MulaPaymentController@initiate_payment');


