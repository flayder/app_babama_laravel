<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\OrderCreated;
use App\Listeners\CreateOrderTransaction;
use App\Listeners\NotifyAdminAboutOrderCreation;
use App\Listeners\NotifyUserAboutOrderCreation;
use App\Listeners\SubtractUserBalanceForOrder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

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
        OrderCreated::class => [
            NotifyAdminAboutOrderCreation::class,
            NotifyUserAboutOrderCreation::class,
            CreateOrderTransaction::class,
            SubtractUserBalanceForOrder::class,
            CreateOrderTransaction::class,
        ],
    ];

    /** Register any events for your application. */
    public function boot(): void
    {
    }
}
