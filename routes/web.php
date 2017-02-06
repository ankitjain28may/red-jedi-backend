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
    return view('welcome');
});

Route::get('/api/github/auth', 'Github\GitAuthController@index');
Route::get('/api/github/update', 'Github\GitAuthController@update');
Route::get('/api/github/user/callback', 'Github\GitAuthController@callback');

Route::resource('/api/guzz', 'Github\ApiController');

// Route::get('/{any}', function() {
//     return View('error');
// });

