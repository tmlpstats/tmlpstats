<?php
namespace TmlpStats\Api\Base;

use Gate;
use Illuminate\Auth\Guard;
use Illuminate\Http\Request;
use TmlpStats\Api;

class AuthenticatedApiBase extends ApiBase
{
    protected $gate = null;
    protected $context = null;

    public function __construct(Api\Context $context, Guard $auth, Request $request)
    {
        parent::__construct($auth, $request);
        $this->user = $context->getUser();
        $this->context = $context;
        $this->gate = Gate::forUser($this->user);
    }
}
