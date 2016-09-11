<?php
namespace TmlpStats\Tests\Unit\Validate;

use stdClass;

class ValidatorTestAbstract extends \TmlpStats\Tests\TestAbstract
{
    protected $defaultObjectMethods = ['addMessage'];

    protected function getObjectMock($methods = [], $constructorArgs = [])
    {
        $methods = $this->mergeMockMethods($this->defaultObjectMethods, $methods);

        if (!$constructorArgs) {
            $constructorArgs[] = new stdClass;
        }

        return parent::getObjectMock($methods, $constructorArgs);
    }
}
