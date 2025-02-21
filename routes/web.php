<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ApplicationApiController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\LegalController;
use App\Http\Controllers\Admin\OverViewController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServerController as AdminServerController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ShopProductController;
use App\Http\Controllers\Admin\TicketCategoryController;
use App\Http\Controllers\Admin\TicketsController as AdminTicketsController;
use App\Http\Controllers\Admin\UsefulLinkController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PreferencesController;
use App\Http\Controllers\ProductController as FrontProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\TicketsController;
use App\Http\Controllers\TranslationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

Route::get('/terms/{type}', [TermsController::class, 'index'])->name('terms');

Route::middleware(['auth', 'checkSuspended'])->group(function () {
    //resend verification email
    Route::get('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent!');
    })->middleware(['auth', 'throttle:3,1'])->name('verification.send');

    //normal routes
    Route::get('notifications/readAll', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::resource('notifications', NotificationController::class);
    Route::patch('/servers/cancel/{server}', [ServerController::class, 'cancel'])->name('servers.cancel');
    Route::post('/servers/validateDeploymentVariables', [ServerController::class, 'validateDeploymentVariables'])->name('servers.validateDeploymentVariables');
    Route::delete('/servers/{server}', [ServerController::class, 'destroy'])->name('servers.destroy');
    Route::patch('/servers/{server}', [ServerController::class, 'update'])->name('servers.update');
    Route::resource('servers', ServerController::class);

    try {
        $serverSettings = app(App\Settings\ServerSettings::class);
        if ($serverSettings->creation_enabled) {
            Route::post('servers/{server}/upgrade', [ServerController::class, 'upgrade'])->name('servers.upgrade');
        }
    } catch (Exception $e) {
        Log::error("ServerSettings not found, skipping server upgrade route");
    }

    Route::post('profile/selfdestruct', [ProfileController::class, 'selfDestroyUser'])->name('profile.selfDestroyUser');
    Route::resource('profile', ProfileController::class);
    Route::resource('store', StoreController::class);
    Route::get('preferences', [PreferencesController::class, 'index'])->name('preferences.index');
    Route::post('preferences', [PreferencesController::class, 'update'])->name('preferences.update');

    //server create utility routes (product)
    //routes made for server create page to fetch product info
    Route::get('/products/nodes/egg/{egg?}', [FrontProductController::class, 'getNodesBasedOnEgg'])->name('products.nodes.egg');
    Route::get('/products/locations/egg/{egg?}', [FrontProductController::class, 'getLocationsBasedOnEgg'])->name('products.locations.egg');
    Route::get('/products/products/{egg?}/{location?}', [FrontProductController::class, 'getProductsBasedOnLocation'])->name('products.products.location');

    //payments
    Route::get('checkout/{shopProduct}', [PaymentController::class, 'checkOut'])->name('checkout');
    Route::post('payment/pay', [PaymentController::class, 'pay'])->name('payment.pay');
    Route::get('payment/FreePay/{shopProduct}', [PaymentController::class, 'FreePay'])->name('payment.FreePay');
    Route::get('payment/Cancel', [PaymentController::class, 'Cancel'])->name('payment.Cancel');

    Route::get('users/logbackin', [UserController::class, 'logBackIn'])->name('users.logbackin');

    //discord
    Route::get('/auth/redirect', [SocialiteController::class, 'redirect'])->name('auth.redirect');
    Route::get('/auth/callback', [SocialiteController::class, 'callback'])->name('auth.callback');

    //voucher redeem
    Route::post('/voucher/redeem', [VoucherController::class, 'redeem'])->middleware('throttle:5,1')->name('voucher.redeem');

    //ticket user
    Route::get('ticket', [TicketsController::class, 'index'])->name('ticket.index');
    Route::get('ticket/datatable', [TicketsController::class, 'datatable'])->name('ticket.datatable');
    Route::get('ticket/new', [TicketsController::class, 'create'])->name('ticket.new');

    Route::post('ticket/new', [TicketsController::class, 'store'])->name('ticket.new.store');
    Route::get('ticket/show/{ticket_id}', [TicketsController::class, 'show'])->name('ticket.show');
    Route::post('ticket/reply', [TicketsController::class, 'reply'])->name('ticket.reply');

    Route::post('ticket/status/{ticket_id}', [TicketsController::class, 'changeStatus'])->name('ticket.changeStatus');


    //admin
    Route::prefix('admin')->name('admin.')->group(function () {
        //Roles
        Route::get('roles/datatable', [RoleController::class, 'datatable'])->name('roles.datatable');
        Route::resource('roles', RoleController::class);
        //overview
        Route::get('overview', [OverViewController::class, 'index'])->name('overview.index');
        Route::get('overview/sync', [OverViewController::class, 'syncPterodactyl'])->name('overview.sync');

        Route::resource('activitylogs', ActivityLogController::class);

        //users
        Route::get('users.json', [UserController::class, 'json'])->name('users.json');
        Route::get('users/loginas/{user}', [UserController::class, 'loginAs'])->name('users.loginas');
        Route::get('users/verifyEmail/{user}', [UserController::class, 'verifyEmail'])->name('users.verifyEmail');
        Route::get('users/datatable', [UserController::class, 'datatable'])->name('users.datatable');
        Route::get('users/notifications', [UserController::class, 'notifications'])->name('users.notifications.index');
        Route::post('users/notifications', [UserController::class, 'notify'])->name('users.notifications.notify');
        Route::post('users/togglesuspend/{user}', [UserController::class, 'toggleSuspended'])->name('users.togglesuspend');
        Route::resource('users', UserController::class);

        //servers
        Route::get('servers/datatable', [AdminServerController::class, 'datatable'])->name('servers.datatable');
        Route::post('servers/togglesuspend/{server}', [AdminServerController::class, 'toggleSuspended'])->name('servers.togglesuspend');
        Route::patch('/servers/cancel/{server}', [AdminServerController::class, 'cancel'])->name('servers.cancel');
        Route::get('servers/sync', [AdminServerController::class, 'syncServers'])->name('servers.sync');
        Route::resource('servers', AdminServerController::class);

        //products
        Route::get('products/datatable', [ProductController::class, 'datatable'])->name('products.datatable');
        Route::get('products/clone/{product}', [ProductController::class, 'clone'])->name('products.clone');
        Route::patch('products/disable/{product}', [ProductController::class, 'disable'])->name('products.disable');
        Route::resource('products', ProductController::class);

        //store
        Route::get('store/datatable', [ShopProductController::class, 'datatable'])->name('store.datatable');
        Route::patch('store/disable/{shopProduct}', [ShopProductController::class, 'disable'])->name('store.disable');
        Route::resource('store', ShopProductController::class)->parameters([
            'store' => 'shopProduct',
        ]);

        //payments
        Route::get('payments/datatable', [PaymentController::class, 'datatable'])->name('payments.datatable');
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');

        //settings
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('settings/icons', [SettingsController::class, 'updateIcons'])->name('settings.updateIcons');


        //invoices
        Route::get('invoices/download-invoices', [InvoiceController::class, 'downloadAllInvoices'])->name('invoices.downloadAllInvoices');
        Route::get('invoices/download-single-invoice', [InvoiceController::class, 'downloadSingleInvoice'])->name('invoices.downloadSingleInvoice');

        //usefullinks
        Route::get('usefullinks/datatable', [UsefulLinkController::class, 'datatable'])->name('usefullinks.datatable');
        Route::resource('usefullinks', UsefulLinkController::class);

        //vouchers
        Route::get('vouchers/datatable', [VoucherController::class, 'datatable'])->name('vouchers.datatable');
        Route::get('vouchers/{voucher}/usersdatatable', [VoucherController::class, 'usersdatatable'])->name('vouchers.usersdatatable');
        Route::get('vouchers/{voucher}/users', [VoucherController::class, 'users'])->name('vouchers.users');
        Route::resource('vouchers', VoucherController::class);

        //partners
        Route::get('partners/datatable', [PartnerController::class, 'datatable'])->name('partners.datatable');
        Route::get('partners/{voucher}/users', [PartnerController::class, 'users'])->name('partners.users');
        Route::resource('partners', PartnerController::class);

        //coupons
        Route::get('coupons/datatable', [CouponController::class, 'dataTable'])->name('coupons.datatable');
        Route::post('coupons/redeem', [CouponController::class, 'redeem'])->name('coupon.redeem');
        Route::resource('coupons', CouponController::class);

        //api-keys
        Route::get('api/datatable', [ApplicationApiController::class, 'datatable'])->name('api.datatable');
        Route::resource('api', ApplicationApiController::class)->parameters([
            'api' => 'applicationApi',
        ]);

        //ticket moderation
        Route::get('ticket', [AdminTicketsController::class, 'index'])->name('ticket.index');
        Route::get('ticket/datatable', [AdminTicketsController::class, 'datatable'])->name('ticket.datatable');
        Route::get('ticket/show/{ticket_id}', [AdminTicketsController::class, 'show'])->name('ticket.show');
        Route::post('ticket/reply', [AdminTicketsController::class, 'reply'])->name('ticket.reply');
        Route::post('ticket/status/{ticket_id}', [AdminTicketsController::class, 'changeStatus'])->name('ticket.changeStatus');
        Route::post('ticket/delete/{ticket_id}', [AdminTicketsController::class, 'delete'])->name('ticket.delete');
        //ticket moderation blacklist
        Route::get('ticket/blacklist', [AdminTicketsController::class, 'blacklist'])->name('ticket.blacklist');
        Route::post('ticket/blacklist', [AdminTicketsController::class, 'blacklistAdd'])->name('ticket.blacklist.add');
        Route::post('ticket/blacklist/delete/{id}', [AdminTicketsController::class, 'blacklistDelete'])->name('ticket.blacklist.delete');
        Route::post('ticket/blacklist/change/{id}', [AdminTicketsController::class, 'blacklistChange'])->name('ticket.blacklist.change');
        Route::get('ticket/blacklist/datatable', [AdminTicketsController::class, 'dataTableBlacklist'])->name('ticket.blacklist.datatable');


        Route::get('ticket/category/datatable', [TicketCategoryController::class, 'datatable'])->name('ticket.category.datatable');
        Route::resource("ticket/category", TicketCategoryController::class, ['as' => 'ticket']);
    });



    Route::get('/home', [HomeController::class, 'index'])->name('home');
});
