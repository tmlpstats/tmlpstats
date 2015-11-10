<?php

namespace TmlpStats\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use TmlpStats\User;

class Policy
{
    use HandlesAuthorization;

    protected $defaultAllow = false;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * By default, admins are authorized for everything
     *
     * @param User $user
     * @param $ability
     * @return bool
     */
    public function before(User $user, $ability)
    {
        if ($user->hasRole('administrator')) {
            return true;
        }
    }

    /**
     * Catch all authorization attempts for anything not explicitly defined,
     * and return false. This with the default before results in admin-only access
     *
     * @param $name
     * @param array $arguments
     * @return bool
     */
    public function __call($name, $arguments)
    {
        return $this->defaultAllow;
    }
}
