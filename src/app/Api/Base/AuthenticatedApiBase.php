<?php
namespace TmlpStats\Api\Base;

use Gate;
use TmlpStats\User;

class AuthenticatedApiBase
{
    protected $user = null;
    protected $gate = null;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->gate = Gate::forUser($user);
    }
}
