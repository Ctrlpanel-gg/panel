<?php

namespace App\Providers;

use App\Events\PaymentEvent;
use App\Events\UserUpdateCreditsEvent;
use App\Events\CouponUsedEvent;
use App\Listeners\CouponUsed;
use App\Listeners\CreateInvoice;
use App\Listeners\UnsuspendServers;
use App\Listeners\UserPayment;
use App\Listeners\Verified as ListenerVerified;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UserUpdateCreditsEvent::class => [
            UnsuspendServers::class,
        ],
        PaymentEvent::class => [
            CreateInvoice::class,
            UserPayment::class,
        ],
        CouponUsedEvent::class => [
            CouponUsed::class
        ],
        SocialiteWasCalled::class => [
            // ... other providers
            'SocialiteProviders\\Discord\\DiscordExtendSocialite@handle',
        ],
        Verified::class => [
            ListenerVerified::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
