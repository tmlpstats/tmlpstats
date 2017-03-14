<?php namespace TmlpStats\Handlers\Events;

use Illuminate\Auth\Events as AuthEvents;
use Log;
use Session;
use TmlpStats\User;

class AuthLogoutEventHandler
{
    /**
     * Create the event handler.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  User $user
     */
    public function handle(AuthEvents\Logout $event)
    {
        $user = $event->user;
        // We can only log it if the session hasn't already expired
        if ($user) {
            Log::info("User {$user->id} logged out");
        }

        Session::flush();
    }
}
