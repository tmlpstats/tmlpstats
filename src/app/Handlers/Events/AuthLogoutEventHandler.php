<?php namespace TmlpStats\Handlers\Events;

use TmlpStats\User;

use Log;
use Session;

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
    public function handle(User $user = null)
    {
        // We can only log it if the session hasn't already expired
        if ($user) {
            Log::info("User {$user->id} logged out");
        }

        Session::flush();
    }
}
