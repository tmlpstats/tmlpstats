<?php
namespace TmlpStats\Tests\Validate;

use TmlpStats\Validate\NullValidator;
use stdClass;

class NullValidatorTest extends ValidatorTestAbstract
{
    protected $testClass = NullValidator::class;

    protected $dataFields = [];

    public function testValidate()
    {
        $data = new stdClass;

        $validator = $this->getObjectMock();

        $result = $this->runMethod($validator, 'validate', $data);

        $this->assertTrue($result);
    }
}
