<?php
namespace TmlpStats\Tests\Validate;

use TmlpStats\Validate\TmlpCourseInfoValidator;
use stdClass;

class TmlpCourseInfoValidatorTest extends ValidatorTestAbstract
{
    protected $testClass = 'TmlpStats\Validate\TmlpCourseInfoValidator';

    protected $dataFields = array(
        'type',
        'quarterStartRegistered',
        'quarterStartApproved',
    );

    /**
    * @dataProvider providerRun
    */
    public function testRun($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock(array('addMessage', 'validate'));

        $i = 0;
        if ($messages) {
            for ($i = 0; $i < count($messages); $i++) {
                $validator->expects($this->at($i))
                          ->method('addMessage')
                          ->with($messages[$i][0], $messages[$i][1], $messages[$i][2]);
            }
        } else {
            $validator->expects($this->never())
                      ->method('addMessage');
        }

        $validator->expects($this->at($i))
                  ->method('validate')
                  ->with($data);

        $result = $validator->run($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerRun()
    {
        return array(
            // Test Required
            array(
                $this->arrayToObject(array(
                    'type'                   => null,
                    'quarterStartRegistered' => null,
                    'quarterStartApproved'   => null,
                )),
                array(
                    array('INVALID_VALUE', 'Type', '[empty]'),
                    array('INVALID_VALUE', 'Quarter Start Registered', '[empty]'),
                    array('INVALID_VALUE', 'Quarter Start Approved', '[empty]'),
                ),
                false,
            ),
        );
    }

    /**
    * @dataProvider providerValidateExtended
    */
    public function testValidateExtended($returnValues, $expectedResult)
    {
        $validator = $this->getObjectMock(array(
            'validateQuarterStartValues',
        ));
        $validator->expects($this->once())
                  ->method('validateQuarterStartValues')
                  ->will($this->returnValue($returnValues['validateQuarterStartValues']));

        $result = $this->runMethod($validator, 'validate', array());

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateExtended()
    {
        return array(
            // Validate Succeeds
            array(
                array(
                    'validateQuarterStartValues' => true,
                ),
                true,
            ),
            // validateQuarterStartValues fails
            array(
                array(
                    'validateQuarterStartValues' => false,
                ),
                false,
            ),
        );
    }

    /**
    * @dataProvider providerValidate
    */
    public function testValidate($expectedResult)
    {
        $validator = $this->getObjectMock(array(
            'validateQuarterStartValues',
        ));
        $validator->expects($this->once())
                  ->method('validateQuarterStartValues')
                  ->will($this->returnValue(true));

        $this->setProperty($validator, 'isValid', $expectedResult);

        $result = $this->runMethod($validator, 'validate', array());

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidate()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
    * @dataProvider providerValidateQuarterStartValues
    */
    public function testValidateQuarterStartValues($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock();

        if ($messages) {
            for ($i = 0; $i < count($messages); $i++) {
                $validator->expects($this->at($i))
                          ->method('addMessage')
                          ->with($messages[$i]);
            }
        } else {
            $validator->expects($this->never())
                      ->method('addMessage');
        }

        $result = $validator->validateQuarterStartValues($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateQuarterStartValues()
    {
        return array(
            // validateQuarterStartValues passes
            array(
                $this->arrayToObject(array(
                    'quarterStartApproved' => 2,
                    'quarterStartRegistered' => 2,
                )),
                array(),
                true,
            ),
            // validateQuarterStartValues passes
            array(
                $this->arrayToObject(array(
                    'quarterStartApproved' => 1,
                    'quarterStartRegistered' => 2,
                )),
                array(),
                true,
            ),
            // validateQuarterStartValues Fails When quarterStartApproved greater than quarterStartRegistered
            array(
                $this->arrayToObject(array(
                    'quarterStartApproved' => 3,
                    'quarterStartRegistered' => 2,
                )),
                array('TMLPCOURSE_QSTART_TER_LESS_THAN_QSTART_APPROVED'),
                false,
            ),
        );
    }
}