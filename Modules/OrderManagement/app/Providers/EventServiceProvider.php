<?php

namespace Modules\OrderManagement\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\OrderManagement\Events\AddToHistoryEvent;
use Modules\OrderManagement\Events\ChangeTotalAmountEvent;
use Modules\OrderManagement\Events\ProcessOrderItemsEvent;
use Modules\OrderManagement\Listeners\AddToHistoryListener;
use Modules\OrderManagement\Listeners\ChangeTotalAmountListener;
use Modules\OrderManagement\Listeners\ProcessOrderItemsListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
