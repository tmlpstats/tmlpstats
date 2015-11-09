<?php

namespace TmlpStats\Policies;

use TmlpStats\User;

class ReportTokenPolicy extends Policy
{
    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Can user view the ReportToken's URL?
     *
     * @param User $user
     * @return bool
     */
    public function readLink(User $user)
    {
        return $user->hasRole('globalStatistician');
    }
}
