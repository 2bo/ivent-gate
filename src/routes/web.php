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

Route::get('/', 'WorkshopController@index')->name('root');
Route::get('/vue', 'WorkshopController@vueIndex');

Route::resource('workshops', 'WorkshopController');
Route::resource('tags', 'TagController');


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/sample', function () {
    return view('sample');
});
