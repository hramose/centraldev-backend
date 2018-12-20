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

Route::match(['GET', 'POST'], '/', function(Request $request) { 
    return response()->json([
        'endpoint' => $request->path(),
        'documentation_url' => 'https://docs.centraldev.fr/',
        'timestamp' => Carbon::now()->timestamp,
    ], 200);
});