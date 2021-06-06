<?php

use App\Http\Controllers\Api\ServerController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VerifyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::post('/verify', [VerifyController::class, 'verify']);

Route::middleware('api.token')->group(function () {
    Route::resource('users', UserController::class)->except(['store', 'create']);

    Route::patch('/servers/{server}/suspend', [ServerController::class, 'suspend']);
    Route::patch('/servers/{server}/unsuspend', [ServerController::class, 'unSuspend']);
    Route::resource('servers', ServerController::class)->except(['store', 'create', 'edit', 'update']);
});



