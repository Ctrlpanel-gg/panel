<?php

use App\Http\Controllers\OAuth\OAuthUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| OAuth Routes
|--------------------------------------------------------------------------
|
| Here is where you can register OAuth routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "oauth" middleware group.
|
*/

Route::middleware('auth:oauth')->group(function () {
    Route::resource('/user', OAuthUserController::class)->middleware('scopes:identify');
});
