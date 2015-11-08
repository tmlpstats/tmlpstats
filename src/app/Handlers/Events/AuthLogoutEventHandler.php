<?php namespace TmlpStats\Handlers\Events;

use Log;

use Session;
use TmlpStats\User;
use Carbon\Carbon;

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
