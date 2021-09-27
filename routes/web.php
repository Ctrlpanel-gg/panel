<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ApplicationApiController;
use App\Http\Controllers\Admin\ConfigurationController;
use App\Http\Controllers\Admin\NestsController;
use App\Http\Controllers\Admin\NodeController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaypalProductController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ServerController as AdminServerController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UsefulLinkController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\StoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Route::middleware('guest')->get('/', function () {
    return redirect('login');
})->name('welcome');

Auth::routes(['verify' => true]);

Route::middleware(['auth', 'checkSuspended'])->group(function () {
    #resend verification email
    Route::get('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent!');
    })->middleware(['auth', 'throttle:3,1'])->name('verification.send');

    #normal routes
    Route::resource('notifications', NotificationController::class);
    Route::resource('servers', ServerController::class);
    Route::resource('profile', ProfileController::class);
    Route::resource('store', StoreController::class);

    #payments
    Route::get('checkout/{paypalProduct}', [PaymentController::class, 'checkOut'])->name('checkout');
    Route::get('payment/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
    Route::get('payment/pay/{paypalProduct}', [PaymentController::class, 'pay'])->name('payment.pay');

    Route::get('users/logbackin', [UserController::class, 'logBackIn'])->name('users.logbackin');

    #discord
    Route::get('/auth/redirect', [SocialiteController::class, 'redirect'])->name('auth.redirect');
    Route::get('/auth/callback', [SocialiteController::class, 'callback'])->name('auth.callback');

    #voucher redeem
    Route::post('/voucher/redeem', [VoucherController::class, 'redeem'])->middleware('throttle:5,1')->name('voucher.redeem');

    #admin
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {

        Route::resource('activitylogs', ActivityLogController::class);

        Route::get("users.json", [UserController::class, "json"])->name('users.json');
        Route::get('users/loginas/{user}', [UserController::class, 'loginAs'])->name('users.loginas');
        Route::get('users/datatable', [UserController::class, 'datatable'])->name('users.datatable');
        Route::get('users/notifications', [UserController::class, 'notifications'])->name('users.notifications');
        Route::post('users/notifications', [UserController::class, 'notify'])->name('users.notifications');
        Route::post('users/togglesuspend/{user}', [UserController::class, 'toggleSuspended'])->name('users.togglesuspend');
        Route::resource('users', UserController::class);

        Route::get('servers/datatable', [AdminServerController::class, 'datatable'])->name('servers.datatable');
        Route::post('servers/togglesuspend/{server}', [AdminServerController::class, 'toggleSuspended'])->name('servers.togglesuspend');
        Route::resource('servers', AdminServerController::class);

        Route::get('products/datatable', [ProductController::class, 'datatable'])->name('products.datatable');
        Route::patch('products/disable/{product}', [ProductController::class, 'disable'])->name('products.disable');
        Route::resource('products', ProductController::class);

        Route::get('store/datatable', [PaypalProductController::class, 'datatable'])->name('store.datatable');
        Route::patch('store/disable/{paypalProduct}', [PaypalProductController::class, 'disable'])->name('store.disable');
        Route::resource('store', PaypalProductController::class)->parameters([
            'store' => 'paypalProduct',
        ]);

        Route::get('payments/datatable', [PaymentController::class, 'datatable'])->name('payments.datatable');
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');

        Route::get('nodes/datatable', [NodeController::class, 'datatable'])->name('nodes.datatable');
        Route::get('nodes/sync', [NodeController::class, 'sync'])->name('nodes.sync');
        Route::resource('nodes', NodeController::class);

        Route::get('nests/datatable', [NestsController::class, 'datatable'])->name('nests.datatable');
        Route::get('nests/sync', [NestsController::class, 'sync'])->name('nests.sync');
        Route::resource('nests', NestsController::class);

        Route::get('configurations/datatable', [ConfigurationController::class, 'datatable'])->name('configurations.datatable');
        Route::patch('configurations/updatevalue', [ConfigurationController::class, 'updatevalue'])->name('configurations.updatevalue');
        Route::resource('configurations', ConfigurationController::class);
        Route::resource('configurations', ConfigurationController::class);

        Route::patch('settings/update/icons', [SettingsController::class, 'updateIcons'])->name('settings.update.icons');
        Route::resource('settings', SettingsController::class)->only('index');

        Route::get('usefullinks/datatable', [UsefulLinkController::class, 'datatable'])->name('usefullinks.datatable');
        Route::resource('usefullinks', UsefulLinkController::class);

        Route::get('vouchers/datatable', [VoucherController::class, 'datatable'])->name('vouchers.datatable');
        Route::resource('vouchers', VoucherController::class);

        Route::get('api/datatable', [ApplicationApiController::class, 'datatable'])->name('api.datatable');
        Route::resource('api', ApplicationApiController::class)->parameters([
            'api' => 'applicationApi',
        ]);
    });

    Route::get('/home', [HomeController::class, 'index'])->name('home');
});
