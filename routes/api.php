<?php

use Illuminate\Http\Request;
use Carbon\Carbon;

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

Route::match(['GET', 'POST'], '/', function(Request $request)
{ 
    return response()->json([
        'endpoint' => $request->path(),
        'documentation_url' => 'https://docs.centraldev.fr/',
        'timestamp' => Carbon::now()->timestamp,
    ], 200);
});

Route::group(['middleware' => 'api', 'prefix' => 'auth', 'as' => 'auth.'], function() {
    Route::post('register', 'AuthController@register')->name('register');
    Route::post('login', 'AuthController@login')->name('login');
    Route::post('logout', 'AuthController@logout')->name('logout');
    Route::post('refresh', 'AuthController@refresh')->name('refresh');
    Route::get('verify/{code}', 'AuthController@verify')->name('verify');
    Route::post('me', 'AuthController@me');
});