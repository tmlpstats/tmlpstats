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
     * @param  bool $value     A boolean value. Usually the result of an expression, not hard-coded.
     * @param  string $message If provided, a message to use instead of the default.
     */
    protected function assertAuthz($value, $message = null)
    {
        if (!$value) {
            throw new Exceptions\UnauthorizedException($message);
        }
    }

    /**
     * A shortcut of assertAuthz for checking a single permission.
     * @param  string $permission The permission to check
     * @param  Any    $target     What to check against. Usually an ORM model. (see laravel authz docs)
     * @param  string $message    If provided, a message to override.
     */
    protected function assertCan($permission, $target, $message = null)
    {
        return $this->assertAuthz($this->context->can($permission, $target), $message);
    }
}
