<?php
namespace TmlpStats\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use Carbon\Carbon;

class EventServiceProvider extends ServiceProvider {

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'event.name' => [
            'EventListener',
        ],
        'auth.login' => [
            'TmlpStats\Handlers\Events\AuthLoginEventHandler',
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);
    }

}
