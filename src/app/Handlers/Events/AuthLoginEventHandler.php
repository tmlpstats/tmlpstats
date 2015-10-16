<?php namespace TmlpStats\Handlers\Events;

use TmlpStats\Events\Event;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use Auth;
use Request;
use TmlpStats\User;
use Carbon\Carbon;

class AuthLoginEventHandler {

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
     * @param  User  $user
     */
    public function handle(User $user)
    {
        $user->lastLoginAt = Carbon::now();
        $user->save();
    }

}
