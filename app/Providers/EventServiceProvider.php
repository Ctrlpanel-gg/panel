<?php

namespace App\Providers;

use App\Events\UserUpdateCreditsEvent;
use App\Listeners\UnsuspendServers;
use App\Listeners\Verified;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
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
            UnsuspendServers::class
        ],
        SocialiteWasCalled::class => [
            // ... other providers
            'SocialiteProviders\\Discord\\DiscordExtendSocialite@handle',
        ],
        'Illuminate\Auth\Events\Verified' => [
            Verified::class,
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
}
