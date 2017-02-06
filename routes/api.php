<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('cors')->get('/leaderboard', 'Github\LeaderboardController@index');

Route::middleware('cors')->get('/github/show', 'Github\GitAuthController@show');

// Route::middleware('cors')->get('/github/update', 'Github\GitAuthController@update');

// Route::middleware('cors')->get('/github/user/callback', 'Github\GitAuthController@callback');
