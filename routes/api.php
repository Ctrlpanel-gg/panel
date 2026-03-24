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

Route::middleware(['api.token', 'api.audit', 'throttle:60,1'])->group(function () {
    Route::get('users', [UserController::class, 'index'])->middleware('api.scope:users.read')->name('users.index');
    Route::post('users', [UserController::class, 'store'])->middleware('api.scope:users.write')->name('users.store');
    Route::get('users/{user}', [UserController::class, 'show'])->middleware('api.scope:users.read')->name('users.show');
    Route::match(['put', 'patch'], 'users/{user}', [UserController::class, 'update'])->middleware('api.scope:users.write')->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('api.scope:users.write')->name('users.destroy');

    Route::controller(UserController::class)->name('users.')->prefix('users')->group(function () {
        Route::patch('/{user}/increment', 'increment')->middleware('api.scope:users.write')->name('increment');
        Route::patch('/{user}/decrement', 'decrement')->middleware('api.scope:users.write')->name('decrement');
        Route::patch('/{user}/suspend', 'suspend')->middleware('api.scope:users.write')->name('suspend');
        Route::patch('/{user}/unsuspend', 'unsuspend')->middleware('api.scope:users.write')->name('unsuspend');
    });

    Route::get('servers', [ServerController::class, 'index'])->middleware('api.scope:servers.read')->name('servers.index');
    Route::post('servers', [ServerController::class, 'store'])->middleware('api.scope:servers.write')->name('servers.store');
    Route::get('servers/{server}', [ServerController::class, 'show'])->middleware('api.scope:servers.read')->name('servers.show');
    Route::match(['put', 'patch'], 'servers/{server}', [ServerController::class, 'update'])->middleware('api.scope:servers.write')->name('servers.update');
    Route::delete('servers/{server}', [ServerController::class, 'destroy'])->middleware('api.scope:servers.write')->name('servers.destroy');

    Route::controller(ServerController::class)->name('servers.')->prefix('servers')->group(function () {
        Route::patch('/{server}/build', 'updateBuild')->middleware('api.scope:servers.write')->name('updateBuild');
        Route::patch('/{server}/suspend', 'suspend')->middleware('api.scope:servers.write')->name('suspend');
        Route::patch('/{server}/unsuspend', 'unSuspend')->middleware('api.scope:servers.write')->name('unsuspend');
    });

    Route::get('vouchers', [VoucherController::class, 'index'])->middleware('api.scope:vouchers.read')->name('vouchers.index');
    Route::post('vouchers', [VoucherController::class, 'store'])->middleware('api.scope:vouchers.write')->name('vouchers.store');
    Route::get('vouchers/{voucher}', [VoucherController::class, 'show'])->middleware('api.scope:vouchers.read')->name('vouchers.show');
    Route::match(['put', 'patch'], 'vouchers/{voucher}', [VoucherController::class, 'update'])->middleware('api.scope:vouchers.write')->name('vouchers.update');
    Route::delete('vouchers/{voucher}', [VoucherController::class, 'destroy'])->middleware('api.scope:vouchers.write')->name('vouchers.destroy');

    Route::get('roles', [RoleController::class, 'index'])->middleware('api.scope:roles.read')->name('roles.index');
    Route::post('roles', [RoleController::class, 'store'])->middleware('api.scope:roles.write')->name('roles.store');
    Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('api.scope:roles.read')->name('roles.show');
    Route::match(['put', 'patch'], 'roles/{role}', [RoleController::class, 'update'])->middleware('api.scope:roles.write')->name('roles.update');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('api.scope:roles.write')->name('roles.destroy');

    Route::get('products', [ProductController::class, 'index'])->middleware('api.scope:products.read')->name('products.index');
    Route::post('products', [ProductController::class, 'store'])->middleware('api.scope:products.write')->name('products.store');
    Route::get('products/{product}', [ProductController::class, 'show'])->middleware('api.scope:products.read')->name('products.show');
    Route::match(['put', 'patch'], 'products/{product}', [ProductController::class, 'update'])->middleware('api.scope:products.write')->name('products.update');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->middleware('api.scope:products.write')->name('products.destroy');

    Route::controller(NotificationController::class)->name('notifications.')->prefix('notifications')->group(function () {
        Route::get('/{user}', 'index')->middleware('api.scope:notifications.read')->name('index');
        Route::get('/{user}/{notification}', 'view')->middleware('api.scope:notifications.read')->scopeBindings()->name('view');
        Route::post('/send-to-users', 'sendToUsers')->middleware('api.scope:notifications.write')->name('sendToUsers');
        Route::post('/send-to-all', 'sendToAll')->middleware('api.scope:notifications.write')->name('sendToAll');
        Route::delete('/{user}', 'destroyAll')->middleware('api.scope:notifications.write')->name('destroyAll');
        Route::delete('/{user}/{notification}', 'destroyOne')->middleware('api.scope:notifications.write')->scopeBindings()->name('destroyOne');
    });
});
