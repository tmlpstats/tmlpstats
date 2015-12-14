<?php
namespace TmlpStats\Tests\Validate\Objects;

use stdClass;
use TmlpStats\Tests\Validate\ValidatorTestAbstract;

class ObjectsValidatorTestAbstract extends ValidatorTestAbstract
{
    protected $dataFields = [];

    //
    // populateValidators()
    //
    public function testPopulateValidatorsSetsValidatorsForEachInput($data = null)
    {
        if ($data === null) {
            $data = new stdClass;
        }

        $validator = $this->getObjectMock();

        $this->runMethod($validator, 'populateValidators', $data);

        $dataValidators = $this->getProperty($validator, 'dataValidators');

        foreach ($this->dataFields as $field) {
            $this->assertArrayHasKey($field, $dataValidators, "dataValidators missing field $field");
            $this->assertInstanceOf('Respect\Validation\Validator',
                $dataValidators[$field],
                "dataValidators missing validator for field $field");
        }
    }

    /**
     * @dataProvider providerValidate
     */
    public function testValidate($returnValues, $expectedResult)
    {
        $methods = $returnValues
            ? array_keys($returnValues)
            : [];

        $validator = $this->getObjectMock($methods);

        if ($returnValues) {
            foreach ($returnValues as $method => $returnValue) {
                $validator->expects($this->once())
                          ->method($method)
                          ->will($this->returnValue($returnValue));
            }
        } else {
            $this->setProperty($validator, 'isValid', $expectedResult);
        }

        $result = $this->runMethod($validator, 'validate', []);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidate()
    {
        return [
            [
                [],
                true,
            ],
            [
                [],
                false,
            ],
        ];
    }
}
