<?php
namespace TmlpStats\Tests\Unit\Validate;

use stdClass;

class ValidatorTestAbstract extends \TmlpStats\Tests\TestAbstract
{
    protected function getObjectMock($methods = [], $constructorArgs = [])
    {
        $defaultMethods = [
            'addMessage',
        ];
        $methods = $this->mergeMockMethods($defaultMethods, $methods);

        if (!$constructorArgs) {
            $constructorArgs[] = new stdClass;
        }

        return parent::getObjectMock($methods, $constructorArgs);
    }
}
