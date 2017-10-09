<?php

namespace TmlpStats\Policies;

use TmlpStats\GlobalReport;
use TmlpStats\User;

class GlobalReportPolicy extends Policy
{
    public function create(User $user)
    {
        return $user->hasRole('globalStatistician');
    }

    public function read(User $user, GlobalReport $globalReport)
    {
        if ($user->hasRole('readonly')) {
            return ($user->reportToken && $user->reportToken->reportId === $globalReport->id);
        } else {
            return ($user->hasRole('globalStatistician') || $user->hasRole('localStatistician') || $user->hasRole('programLeader'));
        }
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
        return $user->hasRole('globalStatistician') || $user->hasRole('localStatistician') || $user->hasRole('programLeader');
    }

    public function submit(User $user, GlobalReport $globalReport)
    {
        return $this->update($user, $globalReport);
    }

    public function showReportButton(User $user)
    {
        if ($user->hasRole('readonly')) {
            return ($user->reportToken != null);
        }

        return true;
    }
}
