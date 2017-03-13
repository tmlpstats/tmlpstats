<?php namespace TmlpStats\Handlers\Events;

use Carbon\Carbon;
use Illuminate\Auth\Events as AuthEvents;
use Log;
use Request;
use TmlpStats\User;

class AuthLoginEventHandler
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
    public function handle(AuthEvents\login $event)
    {
        $user = $event->user;
        $user->lastLoginAt = Carbon::now();
        $user->save();

        Log::info("User {$user->id} logged in from " . Request::ip());
    }
}
