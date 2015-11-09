<?php

namespace TmlpStats\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use TmlpStats\User;

class Policy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function before(User $user, $ability)
    {
        if ($user->hasRole('administrator')) {
            return true;
        }
    }
}
