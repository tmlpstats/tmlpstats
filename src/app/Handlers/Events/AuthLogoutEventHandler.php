<?php namespace TmlpStats\Handlers\Events;

use Log;

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
    public function handle(User $user)
    {
        Log::info("User {$user->id} logged out");
    }
}
