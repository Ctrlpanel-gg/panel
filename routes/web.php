<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ApplicationApiController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\OverViewController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\CreditProductController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ServerController as AdminServerController;
use App\Http\Controllers\Admin\SettingsControllers\SettingsController;
use App\Http\Controllers\Admin\UsefulLinkController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController as FrontProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TranslationController;
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

# Stripe WebhookRoute -> validation in Route Handler
Route::post('payment/StripeWebhooks', [PaymentController::class, 'StripeWebhooks'])->name('payment.StripeWebhooks');

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

    #server create utility routes (product)
    #routes made for server create page to fetch product info
    Route::get('/products/nodes/egg/{egg?}', [FrontProductController::class, 'getNodesBasedOnEgg'])->name('products.nodes.egg');
    Route::get('/products/locations/egg/{egg?}', [FrontProductController::class, 'getLocationsBasedOnEgg'])->name('products.locations.egg');
    Route::get('/products/products/{egg?}/{node?}', [FrontProductController::class, 'getProductsBasedOnNode'])->name('products.products.node');

    #payments
    Route::get('checkout/{creditProduct}', [PaymentController::class, 'checkOut'])->name('checkout');
    Route::get('payment/PaypalPay/{creditProduct}', [PaymentController::class, 'PaypalPay'])->name('payment.PaypalPay');
    Route::get('payment/PaypalSuccess', [PaymentController::class, 'PaypalSuccess'])->name('payment.PaypalSuccess');
    Route::get('payment/StripePay/{creditProduct}', [PaymentController::class, 'StripePay'])->name('payment.StripePay');
    Route::get('payment/StripeSuccess', [PaymentController::class, 'StripeSuccess'])->name('payment.StripeSuccess');
    Route::get('payment/Cancel', [PaymentController::class, 'Cancel'])->name('payment.Cancel');

    Route::get('users/logbackin', [UserController::class, 'logBackIn'])->name('users.logbackin');

    #discord
    Route::get('/auth/redirect', [SocialiteController::class, 'redirect'])->name('auth.redirect');
    Route::get('/auth/callback', [SocialiteController::class, 'callback'])->name('auth.callback');

    #voucher redeem
    Route::post('/voucher/redeem', [VoucherController::class, 'redeem'])->middleware('throttle:5,1')->name('voucher.redeem');

    #switch language
    Route::post('changelocale', [TranslationController::class, 'changeLocale'])->name('changeLocale');


    #admin
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {

        #overview
        Route::get('overview', [OverViewController::class, 'index'])->name('overview.index');
        Route::get('overview/sync', [OverViewController::class, 'syncPterodactyl'])->name('overview.sync');

        Route::resource('activitylogs', ActivityLogController::class);

        #users
        Route::get("users.json", [UserController::class, "json"])->name('users.json');
        Route::get('users/loginas/{user}', [UserController::class, 'loginAs'])->name('users.loginas');
        Route::get('users/datatable', [UserController::class, 'datatable'])->name('users.datatable');
        Route::get('users/notifications', [UserController::class, 'notifications'])->name('users.notifications');
        Route::post('users/notifications', [UserController::class, 'notify'])->name('users.notifications');
        Route::post('users/togglesuspend/{user}', [UserController::class, 'toggleSuspended'])->name('users.togglesuspend');
        Route::resource('users', UserController::class);

        #servers
        Route::get('servers/datatable', [AdminServerController::class, 'datatable'])->name('servers.datatable');
        Route::post('servers/togglesuspend/{server}', [AdminServerController::class, 'toggleSuspended'])->name('servers.togglesuspend');
        Route::resource('servers', AdminServerController::class);

        #products
        Route::get('products/datatable', [ProductController::class, 'datatable'])->name('products.datatable');
        Route::get('products/clone/{product}', [ProductController::class, 'clone'])->name('products.clone');
        Route::patch('products/disable/{product}', [ProductController::class, 'disable'])->name('products.disable');
        Route::resource('products', ProductController::class);

        #store
        Route::get('store/datatable', [CreditProductController::class, 'datatable'])->name('store.datatable');
        Route::patch('store/disable/{creditProduct}', [CreditProductController::class, 'disable'])->name('store.disable');
        Route::resource('store', CreditProductController::class)->parameters([
            'store' => 'creditProduct',
        ]);

        #payments
        Route::get('payments/datatable', [PaymentController::class, 'datatable'])->name('payments.datatable');
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');

        #settings
        Route::get('settings/datatable', [SettingsController::class, 'datatable'])->name('settings.datatable');
        Route::patch('settings/updatevalue', [SettingsController::class, 'updatevalue'])->name('settings.updatevalue');

        #settings
        Route::patch('settings/update/icons', [SettingsController::class, 'updateIcons'])->name('settings.update.icons');
        Route::patch('settings/update/invoice-settings', [\App\Classes\Settings\InvoiceSettingsC::class, 'updateInvoiceSettings'])->name('settings.update.invoicesettings');
        Route::patch('settings/update/lagnguage', [SettingsController::class, 'updateLanguageSettings'])->name('settings.update.languagesettings');
        Route::resource('settings', SettingsController::class)->only('index');

        #invoices
        Route::get('invoices/download-invoices', [InvoiceController::class, 'downloadAllInvoices'])->name('invoices.downloadAllInvoices');;
        Route::get('invoices/download-single-invoice', [InvoiceController::class, 'downloadSingleInvoice'])->name('invoices.downloadSingleInvoice');;

        #usefullinks
        Route::get('usefullinks/datatable', [UsefulLinkController::class, 'datatable'])->name('usefullinks.datatable');
        Route::resource('usefullinks', UsefulLinkController::class);

        #vouchers
        Route::get('vouchers/datatable', [VoucherController::class, 'datatable'])->name('vouchers.datatable');
        Route::get('vouchers/{voucher}/usersdatatable', [VoucherController::class, 'usersdatatable'])->name('vouchers.usersdatatable');
        Route::get('vouchers/{voucher}/users', [VoucherController::class, 'users'])->name('vouchers.users');
        Route::resource('vouchers', VoucherController::class);

        #api-keys
        Route::get('api/datatable', [ApplicationApiController::class, 'datatable'])->name('api.datatable');
        Route::resource('api', ApplicationApiController::class)->parameters([
            'api' => 'applicationApi',
        ]);
    });

    Route::get('/home', [HomeController::class, 'index'])->name('home');
});
