<?php

namespace TmlpStats\Policies;

use TmlpStats\User;

class UserPolicy extends Policy
{

    public function showReportButton(User $user)
    {
        if ($user->hasRole('readonly')) {
            return ($user->reportToken && $user->reportToken->centerId);
        }

        return true;
    }
}
