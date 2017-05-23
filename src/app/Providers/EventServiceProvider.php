<?php
namespace TmlpStats\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'event.name' => [
            'EventListener',
        ],
        'Illuminate\Auth\Events\Login' => [
            'TmlpStats\Handlers\Events\AuthLoginEventHandler',
        ],
        'Illuminate\Auth\Events\Logout' => [
            'TmlpStats\Handlers\Events\AuthLogoutEventHandler',
        ],
        'Illuminate\Auth\Events\Attempting' => [
            'TmlpStats\Handlers\Events\AuthLoginAttemptEventHandler',
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
