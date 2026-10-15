<?php

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ServerController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VoucherController;
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

Route::middleware('api.token')->group(function () {
    Route::apiResource('users', UserController::class);

    Route::controller(UserController::class)->name('users.')->prefix('users')->group(function () {
        Route::patch('/{user}/increment', 'increment')->name('increment');
        Route::patch('/{user}/decrement', 'decrement')->name('decrement');
        Route::patch('/{user}/suspend', 'suspend')->name('suspend');
        Route::patch('/{user}/unsuspend', 'unsuspend')->name('unsuspend');
    });

    Route::apiResource('servers', ServerController::class);

    Route::controller(ServerController::class)->name('servers.')->prefix('servers')->group(function () {
        Route::patch('/{server}/build', 'updateBuild')->name('updateBuild');
        Route::patch('/{server}/suspend', 'suspend')->name('suspend');
        Route::patch('/{server}/unsuspend', 'unSuspend')->name('unsuspend');
    });

    Route::apiResource('vouchers', VoucherController::class);

    Route::apiResource('roles', RoleController::class);

    Route::apiResource('products', ProductController::class);

    Route::controller(NotificationController::class)->name('notifications.')->prefix('notifications')->group(function () {
        Route::get('/{user}', 'index')->name('index');
        Route::get('/{user}/{notification}', 'view')->scopeBindings()->name('view');
        Route::post('/send-to-users', 'sendToUsers')->name('sendToUsers');
        Route::post('/send-to-all', 'sendToAll')->name('sendToAll');
        Route::delete('/{user}', 'destroyAll')->name('destroyAll');
        Route::delete('/{user}/{notification}', 'destroyOne')->scopeBindings()->name('destroyOne');
    });
});
