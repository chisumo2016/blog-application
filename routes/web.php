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

Route::get('/', 'HomeController@index')->name('home');

Auth::routes();


        /* Subscriber*/
Route::post('subscriber','SubscriberController@store')->name('subscriber.store');

Route::group(['middleware' => 'auth'], function () {
        Route::post('favorite/{post}/add', 'FavoriteController@add')->name('post.favorite');
});

    /* Admin Group Middleware */
Route::group(['as'=>'admin.', 'prefix' => 'admin', 'namespace'=>'Admin', 'middleware'=>['auth','admin']] ,function () {

    Route::get('/dashboard', 'DashboardController@index')->name('dashboard');

    Route::get('setting','SettingsController@index')->name('settings');
    Route::put('profile-update','SettingsController@updateProfile')->name('profile.update');
    Route::put('password-update','SettingsController@updatePassword')->name('password.update');

    /* Tags*/
    Route::resource('tag', 'TagController');
    Route::resource('category', 'CategoryController');
    Route::resource('post', 'PostController');

    //Author Post Approve


    Route::get('pending/post','PostController@pending')->name('post.pending');
    Route::put('/post/{id}/approved','PostController@approval')->name('post.approve');


    //Subscriver

    Route::get('/subscriber' ,'SubscriberController@index')->name('subscriber.index');
    Route::delete('/subscriber/{subscriber}' ,'SubscriberController@destroy')->name('subscriber.destroy');


});




    /* Author Group Middleware */
Route::group(['as'=>'author.','prefix' => 'author','namespace'=>'Author', 'middleware'=>['auth','author']] ,function () {

        Route::get('/dashboard', 'DashboardController@index')->name('dashboard');
        Route::resource('post', 'PostController');

    Route::get('setting','SettingsController@index')->name('settings');
    Route::put('profile-update','SettingsController@updateProfile')->name('profile.update');
    Route::put('password-update','SettingsController@updatePassword')->name('password.update');


});






