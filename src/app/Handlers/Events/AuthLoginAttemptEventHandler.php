<?php namespace TmlpStats\Handlers\Events;

use Illuminate\Auth\Events as AuthEvents;
use Log;
use Request;

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
    public function handle(AuthEvents\Attempting $input)
    {
        Log::info("User attempted login for {$input->credentials['email']} from " . Request::ip());
    }
}
