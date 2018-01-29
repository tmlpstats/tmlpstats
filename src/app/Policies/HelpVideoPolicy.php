<?php
namespace TmlpStats\Policies;

use TmlpStats as Models;

class HelpVideoPolicy extends Policy
{
    public function watch(Models\User $user, Models\HelpVideo $video)
    {
        // Note: intentionally falling throw to next role check
        switch ($video->accessGroup) {
            case 'local':
                // localStatistician or higher
                if ($user->hasRole('localStatistician')) {
                    return true;
                }
            case 'regional':
                // globalStatistician or higher
                if ($user->hasRole('globalStatistician')) {
                    return true;
                }
            case 'admin':
                // administrator only
                return $user->hasRole('administrator');
            default:
                return false;
        }
    }
}
