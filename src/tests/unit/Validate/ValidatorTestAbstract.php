<?php
namespace TmlpStats\Tests\Validate;

use stdClass;

class ValidatorTestAbstract extends \TmlpStats\Tests\TestAbstract
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

    protected function getObjectMock($methods = array(), $constructorArgs = array())
    {
        $defaultMethods = array(
            'addMessage'
        );
        $methods = $this->mergeMockMethods($defaultMethods, $methods);

        if (!$constructorArgs) {
            $constructorArgs[] = new stdClass;
        }

        return parent::getObjectMock($methods, $constructorArgs);
    }

    protected function arrayToObject($array)
    {
        $object = new stdClass;
        foreach ($array as $key => $value) {
            $object->$key = $value;
        }
        return $object;
    }
}
