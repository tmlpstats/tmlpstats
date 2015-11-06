<?php

namespace TmlpStats\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use TmlpStats\GlobalReport;
use TmlpStats\User;

class GlobalReportPolicy
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

    public function before($user, $ability)
    {
        if ($user->hasRole('administrator')) {
            return true;
        }
    }

    public function create(User $user)
    {
        return $user->hasRole('globalStatistician');
    }

    public function read(User $user, GlobalReport $globalReport)
    {
        return $user->hasRole('globalStatistician') || $user->hasRole('localStatistician');
    }

    public function update(User $user, GlobalReport $globalReport)
    {
        return $user->hasRole('globalStatistician');
    }

    public function delete(User $user, GlobalReport $globalReport)
    {
        return $user->hasRole('globalStatistician');
    }

    public function index(User $user)
    {
        return $user->hasRole('globalStatistician') || $user->hasRole('localStatistician');
    }

    public function submit(User $user, GlobalReport $globalReport)
    {
        return $this->update($user, $globalReport);
    }
}
