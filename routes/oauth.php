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
    Route::get('/user', [OAuthUserController::class, 'index'])->middleware('scopes:identify');
    Route::delete('/revoke', [OAuthUserController::class, 'revoke']);
});
