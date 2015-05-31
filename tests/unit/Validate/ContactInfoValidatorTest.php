<?php
namespace TmlpStatsTests\Validate;

use TmlpStats\Validate\ContactInfoValidator;
use stdClass;

class ContactInfoValidatorTest extends ValidatorTestAbstract
{
    protected $testClass = 'TmlpStats\Validate\ContactInfoValidator';

    protected $dataFields = array(
        'firstName',
        'lastName',
        'accountability',
        'phone',
        'email',
        'statsReportId',
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
                    'firstName'      => null,
                    'lastName'       => null,
                    'accountability' => null,
                    'phone'          => null,
                    'email'          => null,
                    'statsReportId'  => null,
                )),
                array(
                    array('INVALID_VALUE', 'First Name', '[empty]'),
                    array('INVALID_VALUE', 'Last Name', '[empty]'),
                    array('INVALID_VALUE', 'Accountability', '[empty]'),
                    array('INVALID_VALUE', 'Phone', '[empty]'),
                    array('INVALID_VALUE', 'Email', '[empty]'),
                    array('INVALID_VALUE', 'Stats Report Id', '[empty]'),
                ),
                false,
            ),
            // Test Valid
            array(
                $this->arrayToObject(array(
                    'firstName'      => 'Keith',
                    'lastName'       => 'Stone',
                    'accountability' => 'Program Manager',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                    'statsReportId'  => 1234,
                )),
                array(),
                true,
            ),
            // Test Valid
            array(
                $this->arrayToObject(array(
                    'firstName'      => 'Keith',
                    'lastName'       => 'Stone',
                    'accountability' => 'Classroom Leader',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                    'statsReportId'  => 1234,
                )),
                array(),
                true,
            ),
            // Test Valid
            array(
                $this->arrayToObject(array(
                    'firstName'      => 'Keith',
                    'lastName'       => 'Stone',
                    'accountability' => 'T-1 Leader',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                    'statsReportId'  => 1234,
                )),
                array(),
                true,
            ),
            // Test Valid
            array(
                $this->arrayToObject(array(
                    'firstName'      => 'Keith',
                    'lastName'       => 'Stone',
                    'accountability' => 'T-2 Leader',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                    'statsReportId'  => 1234,
                )),
                array(),
                true,
            ),
            // Test Valid
            array(
                $this->arrayToObject(array(
                    'firstName'      => 'Keith',
                    'lastName'       => 'Stone',
                    'accountability' => 'Team 2 Team Leader',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                    'statsReportId'  => 1234,
                )),
                array(),
                true,
            ),
            // Test Valid
            array(
                $this->arrayToObject(array(
                    'firstName'      => 'Keith',
                    'lastName'       => 'Stone',
                    'accountability' => 'Team 1 Team Leader',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                    'statsReportId'  => 1234,
                )),
                array(),
                true,
            ),
            // Test Valid
            array(
                $this->arrayToObject(array(
                    'firstName'      => 'Keith',
                    'lastName'       => 'Stone',
                    'accountability' => 'Statistician',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                    'statsReportId'  => 1234,
                )),
                array(),
                true,
            ),
            // Test Valid
            array(
                $this->arrayToObject(array(
                    'firstName'      => 'Keith',
                    'lastName'       => 'Stone',
                    'accountability' => 'Statistician Apprentice',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                    'statsReportId'  => 1234,
                )),
                array(),
                true,
            ),
            // Test Valid
            array(
                $this->arrayToObject(array(
                    'firstName'      => 'Keith',
                    'lastName'       => 'Stone',
                    'accountability' => 'Reporting Statistician',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                    'statsReportId'  => 1234,
                )),
                array(),
                true,
            ),


            // Test Invalid First Name
            array(
                $this->arrayToObject(array(
                    'firstName'      => '',
                    'lastName'       => 'Stone',
                    'accountability' => 'Program Manager',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                    'statsReportId'  => 1234,
                )),
                array(
                    array('INVALID_VALUE', 'First Name', '[empty]'),
                ),
                false,
            ),
            // Test Invalid Last Name
            array(
                $this->arrayToObject(array(
                    'firstName'      => 'Keith',
                    'lastName'       => '',
                    'accountability' => 'Program Manager',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                    'statsReportId'  => 1234,
                )),
                array(
                    array('INVALID_VALUE', 'Last Name', '[empty]'),
                ),
                false,
            ),
            // Test Invalid accountability
            array(
                $this->arrayToObject(array(
                    'firstName'      => 'Keith',
                    'lastName'       => 'Stone',
                    'accountability' => 'asdf',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                    'statsReportId'  => 1234,
                )),
                array(
                    array('INVALID_VALUE', 'Accountability', 'asdf'),
                ),
                false,
            ),
            // Test Invalid phone
            array(
                $this->arrayToObject(array(
                    'firstName'      => 'Keith',
                    'lastName'       => 'Stone',
                    'accountability' => 'Program Manager',
                    'phone'          => 'asdf',
                    'email'          => 'keith.stone@example.com',
                    'statsReportId'  => 1234,
                )),
                array(
                    array('INVALID_VALUE', 'Phone', 'asdf'),
                ),
                false,
            ),
            // Test Invalid email
            array(
                $this->arrayToObject(array(
                    'firstName'      => 'Keith',
                    'lastName'       => 'Stone',
                    'accountability' => 'Program Manager',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone',
                    'statsReportId'  => 1234,
                )),
                array(
                    array('INVALID_VALUE', 'Email', 'keith.stone'),
                ),
                false,
            ),
            // Test Invalid statsReportId
            array(
                $this->arrayToObject(array(
                    'firstName'      => 'Keith',
                    'lastName'       => 'Stone',
                    'accountability' => 'Program Manager',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                    'statsReportId'  => 0,
                )),
                array(
                    array('INVALID_VALUE', 'Stats Report Id', '0'),
                ),
                false,
            ),
            // Test Invalid statsReportId
            array(
                $this->arrayToObject(array(
                    'firstName'      => 'Keith',
                    'lastName'       => 'Stone',
                    'accountability' => 'Program Manager',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                    'statsReportId'  => 'asdf',
                )),
                array(
                    array('INVALID_VALUE', 'Stats Report Id', 'asdf'),
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
        $validator = $this->getObjectMock();

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
}