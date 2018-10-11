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
Route::get('/posts', 'PostController@index')->name('post.index');

Route::get('post/{slug}', 'PostController@details')->name('post.details');

Route::get('/category/{slug}', 'PostController@postByCategory')->name('category.posts');
Route::get('/tag/{slug}', 'PostController@postByTag')->name('tag.posts');

Auth::routes();


        /* Subscriber*/
Route::post('subscriber','SubscriberController@store')->name('subscriber.store');

/* Subscriber*/
Route::get('/search','SearchController@search')->name('search');


/* Favorite*/
Route::group(['middleware' => 'auth'], function () {
        Route::post('favorite/{post}/add', 'FavoriteController@add')->name('post.favorite');
        Route::post('comment/{post}', 'CommentController@store')->name('comment.store');
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

    //Favorites

    Route::get('/favorite' ,'FavoriteController@index')->name('favorite.index');

    //Show Comment

    Route::get('comments', 'CommentController@index')->name('comments.index');
    Route::delete('comments/{id}', 'CommentController@destroy')->name('comment.destroy');

    Route::get('authors', 'AuthorController@index')->name('author.index');
    Route::delete('authors/{id}', 'AuthorController@destroy')->name('author.destroy');



});

    /* Author Group Middleware */
Route::group(['as'=>'author.','prefix' => 'author','namespace'=>'Author', 'middleware'=>['auth','author']] ,function () {

        Route::get('/dashboard', 'DashboardController@index')->name('dashboard');
        Route::resource('post', 'PostController');

    Route::get('setting','SettingsController@index')->name('settings');
    Route::put('profile-update','SettingsController@updateProfile')->name('profile.update');
    Route::put('password-update','SettingsController@updatePassword')->name('password.update');


    //Favorites

    Route::get('/favorite' ,'FavoriteController@index')->name('favorite.index');


    //Show Comment

    Route::get('comments', 'CommentController@index')->name('comment.index');
    Route::delete('comments/{id}', 'CommentController@destroy')->name('comment.destroy');


});



View::composer('layouts.frontend.partials.footer', function ($view){
    $categories = App\Category::all();
    $view->with('categories',$categories);
});







