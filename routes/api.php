<?php

use Illuminate\Http\Request;
use Carbon\Carbon;

Route::match(['GET', 'POST'], '/', function(Request $request) { 
    return response()->json([
        'endpoint' => '/'.$request->path(),
        'message' => 'hello_world',
        'success' => true,
        'timestamp' => Carbon::now()->timestamp,
    ], 200);
});

Route::post('/auth/register', 'AuthController@register')->name('auth.register');
Route::post('/auth/login', 'AuthController@login')->name('auth.login');
Route::get('/auth/verify/{email}/{code}', 'AuthController@verify')->name('auth.verify');
Route::match(['GET', 'POST'], '/auth/check', 'AuthController@check')->name('auth.check');

Route::group(['middleware' => ['jwt.auth']], function() {
    Route::match(['GET', 'POST'], '/users/me', 'UsersController@me')->name('users.me');
});