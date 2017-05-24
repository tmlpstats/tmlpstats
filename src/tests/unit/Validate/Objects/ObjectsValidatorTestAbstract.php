<?php
namespace TmlpStats\Tests\Unit\Validate\Objects;

use stdClass;
use TmlpStats\Tests\Unit\Validate\ValidatorTestAbstract;

class ObjectsValidatorTestAbstract extends ValidatorTestAbstract
{
    protected $dataFields = [];
    protected $validateMethods = [];
    protected $instantiateApp = true;

    //
    // populateValidators()
    //
    public function testPopulateValidatorsSetsValidatorsForEachInput($data = null)
    {
        if ($data === null) {
            $data = new stdClass();
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
        $default = array_fill_keys($this->validateMethods, true);

        $testData = [];

        if ($this->validateMethods) {
            // Success case
            $testData[] = [
                $default,
                true,
            ];

            // Each failure case permutation
            foreach ($this->validateMethods as $method) {
                $mapping = $default;
                $mapping[$method] = false;
                $testData[] = [
                    $mapping,
                    false,
                ];
            }
        } else {
            // Success case
            $testData[] = [
                [], true,
            ];
            // Failure case
            $testData[] = [
                [], false,
            ];
        }

        return $testData;
    }
}
