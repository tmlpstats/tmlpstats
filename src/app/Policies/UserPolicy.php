<?php

namespace TmlpStats\Policies;

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
