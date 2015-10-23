<?php namespace TmlpStats\Handlers\Events;

use Log;
use Request;

use TmlpStats\User;
use Carbon\Carbon;

class AuthLoginAttemptEventHandler
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
     * @param  array $input
     */
    public function handle(array $input)
    {
        Log::info("User attempted login for {$input['email']} from " . Request::ip());
    }
}
