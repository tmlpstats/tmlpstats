<?php
namespace TmlpStats\Tests\Validate;

use TmlpStats\Validate\NullValidator;
use Carbon\Carbon;
use stdClass;

class NullValidatorTest extends ValidatorTestAbstract
{
    protected $testClass = 'TmlpStats\Validate\NullValidator';

    protected $dataFields = array();

    public function testPopulateValidators()
    {
        $data = new stdClass;

        $validator = $this->getObjectMock();

        $this->runMethod($validator, 'populateValidators', $data);

        $dataValidators = $this->getProperty($validator, 'dataValidators');

        $this->assertEmpty($dataValidators);
    }

    public function testValidate()
    {
        $data = new stdClass;

        $validator = $this->getObjectMock();

        $result = $this->runMethod($validator, 'validate', $data);

        $this->assertTrue($result);
    }
}
