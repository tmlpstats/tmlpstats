<?php
namespace TmlpStats\Api\Base;

use Gate;
use TmlpStats\Api;

class AuthenticatedApiBase
{
    protected $user = null;
    protected $gate = null;

    public function __construct(Api\Context $context)
    {
        $this->context = $context;
        $this->user = $context->getUser();
        $this->gate = Gate::forUser($this->user);
    }
}
