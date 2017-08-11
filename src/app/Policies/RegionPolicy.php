<?php

namespace TmlpStats\Policies;

use TmlpStats\Region;
use TmlpStats\User;

class RegionPolicy extends Policy
{
    public function viewManageUi(User $user, Region $region)
    {
        return ($user->hasRole('globalStatistician'));
    }

    public function adminScoreboard(User $user, Region $region)
    {
        return ($user->hasRole('globalStatistician'));
    }

    public function reconcile(User $user, Region $region)
    {
        return $user->hasRole('globalStatistician');
    }
}
