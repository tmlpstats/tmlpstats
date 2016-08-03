<?php
namespace TmlpStats\Api\Base;

use Gate;
use TmlpStats\Api;

class AuthenticatedApiBase extends ApiBase
{
    protected $gate = null;

    public function __construct(Api\Context $context)
    {
        parent::__construct($context);
        $this->gate = Gate::forUser($context->getUser());
    }
}
