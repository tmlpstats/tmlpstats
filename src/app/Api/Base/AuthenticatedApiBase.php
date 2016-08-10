<?php
namespace TmlpStats\Api\Base;

use Gate;
use TmlpStats\Api;
use TmlpStats\Api\Exceptions;

class AuthenticatedApiBase extends ApiBase
{
    protected $gate = null;

    public function __construct(Api\Context $context)
    {
        parent::__construct($context);
        $this->gate = Gate::forUser($context->getUser());
    }

    /**
     * A simple check for authz to avoid boilerplate, throwing unauthorized if the check fails.
     *
     * Instead of the common pattern:
     *     if (!expr1 || !expr2) {
     *         throw new NotAuthorizedException(...);
     *     }
     * You can simply use the assertAuthz for whatever value should be true.
     * IOW:
     *    $this->assertAuthz(expr1 && expr2);
     *
     * @param  [type] $value   [description]
     * @param  [type] $message [description]
     * @return [type]          [description]
     */
    protected function assertAuthz($value, $message = null)
    {
        if (!$value) {
            throw new Exceptions\UnauthorizedException($message);
        }
    }
}
