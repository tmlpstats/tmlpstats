<?php
namespace TmlpStatsTests\Validate;

use TmlpStats\Validate\CenterStatsValidator;
use stdClass;

class CenterStatsValidatorTest extends ValidatorTestAbstract
{
    protected $testClass = 'TmlpStats\Validate\CenterStatsValidator';

    protected $dataFields = array(
        'reportingDate',
        'type',
        'tdo',
        'cap',
        'cpc',
        't1x',
        't2x',
        'gitw',
        'lf',
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
                    'reportingDate'        => null,
                    'type'                 => null,
                    'tdo'                  => null,
                    'cap'                  => null,
                    'cpc'                  => null,
                    't1x'                  => null,
                    't2x'                  => null,
                    'gitw'                 => null,
                    'lf'                   => null,
                )),
                array(
                    array('INVALID_VALUE', 'Reporting Date', '[empty]'),
                    array('INVALID_VALUE', 'Type', '[empty]'),
                    array('INVALID_VALUE', 'Cap', '[empty]'),
                    array('INVALID_VALUE', 'Cpc', '[empty]'),
                    array('INVALID_VALUE', 'T1x', '[empty]'),
                    array('INVALID_VALUE', 'T2x', '[empty]'),
                    array('INVALID_VALUE', 'Gitw', '[empty]'),
                    array('INVALID_VALUE', 'Lf', '[empty]'),
                ),
                false,
            ),
            // Test Valid
            array(
                $this->arrayToObject(array(
                    'reportingDate'        => '2015-01-01',
                    'type'                 => 'promise',
                    'tdo'                  => 0,
                    'cap'                  => 0,
                    'cpc'                  => 0,
                    't1x'                  => 0,
                    't2x'                  => 0,
                    'gitw'                 => 0,
                    'lf'                   => 0,
                )),
                array(),
                true,
            ),
            // Test Valid (Version 2)
            array(
                $this->arrayToObject(array(
                    'reportingDate'        => '2015-01-01',
                    'type'                 => 'actual',
                    'tdo'                  => 1,
                    'cap'                  => 55,
                    'cpc'                  => 66,
                    't1x'                  => 77,
                    't2x'                  => 88,
                    'gitw'                 => 99,
                    'lf'                   => 100,
                )),
                array(),
                true,
            ),

            // Test Invalid reportingDate
            array(
                $this->arrayToObject(array(
                    'reportingDate'        => 'asdf',
                    'type'                 => 'actual',
                    'tdo'                  => 1,
                    'cap'                  => 55,
                    'cpc'                  => 66,
                    't1x'                  => 77,
                    't2x'                  => 88,
                    'gitw'                 => 99,
                    'lf'                   => 100,
                )),
                array(
                    array('INVALID_VALUE', 'Reporting Date', 'asdf'),
                ),
                false,
            ),
            // Test Invalid type
            array(
                $this->arrayToObject(array(
                    'reportingDate'        => '2015-01-01',
                    'type'                 => 'asdf',
                    'tdo'                  => 1,
                    'cap'                  => 55,
                    'cpc'                  => 66,
                    't1x'                  => 77,
                    't2x'                  => 88,
                    'gitw'                 => 99,
                    'lf'                   => 100,
                )),
                array(
                    array('INVALID_VALUE', 'Type', 'asdf'),
                ),
                false,
            ),
            // Test Invalid tdo
            array(
                $this->arrayToObject(array(
                    'reportingDate'        => '2015-01-01',
                    'type'                 => 'actual',
                    'tdo'                  => 'asdf',
                    'cap'                  => 55,
                    'cpc'                  => 66,
                    't1x'                  => 77,
                    't2x'                  => 88,
                    'gitw'                 => 99,
                    'lf'                   => 100,
                )),
                array(
                    array('INVALID_VALUE', 'Tdo', 'asdf'),
                ),
                false,
            ),
            // Test Invalid cap
            array(
                $this->arrayToObject(array(
                    'reportingDate'        => '2015-01-01',
                    'type'                 => 'actual',
                    'tdo'                  => 1,
                    'cap'                  => 'asdf',
                    'cpc'                  => 66,
                    't1x'                  => 77,
                    't2x'                  => 88,
                    'gitw'                 => 99,
                    'lf'                   => 100,
                )),
                array(
                    array('INVALID_VALUE', 'Cap', 'asdf'),
                ),
                false,
            ),
            // Test Invalid cpc
            array(
                $this->arrayToObject(array(
                    'reportingDate'        => '2015-01-01',
                    'type'                 => 'actual',
                    'tdo'                  => 1,
                    'cap'                  => 55,
                    'cpc'                  => 'asdf',
                    't1x'                  => 77,
                    't2x'                  => 88,
                    'gitw'                 => 99,
                    'lf'                   => 100,
                )),
                array(
                    array('INVALID_VALUE', 'Cpc', 'asdf'),
                ),
                false,
            ),
            // Test Invalid t1x
            array(
                $this->arrayToObject(array(
                    'reportingDate'        => '2015-01-01',
                    'type'                 => 'actual',
                    'tdo'                  => 1,
                    'cap'                  => 55,
                    'cpc'                  => 66,
                    't1x'                  => 'asdf',
                    't2x'                  => 88,
                    'gitw'                 => 99,
                    'lf'                   => 100,
                )),
                array(
                    array('INVALID_VALUE', 'T1x', 'asdf'),
                ),
                false,
            ),
            // Test Invalid t2x
            array(
                $this->arrayToObject(array(
                    'reportingDate'        => '2015-01-01',
                    'type'                 => 'actual',
                    'tdo'                  => 1,
                    'cap'                  => 55,
                    'cpc'                  => 66,
                    't1x'                  => 77,
                    't2x'                  => 'asdf',
                    'gitw'                 => 99,
                    'lf'                   => 100,
                )),
                array(
                    array('INVALID_VALUE', 'T2x', 'asdf'),
                ),
                false,
            ),
            // Test Invalid gitw
            array(
                $this->arrayToObject(array(
                    'reportingDate'        => '2015-01-01',
                    'type'                 => 'actual',
                    'tdo'                  => 1,
                    'cap'                  => 55,
                    'cpc'                  => 66,
                    't1x'                  => 77,
                    't2x'                  => 88,
                    'gitw'                 => 101,
                    'lf'                   => 100,
                )),
                array(
                    array('INVALID_VALUE', 'Gitw', '101'),
                ),
                false,
            ),
            // Test Invalid gitw 1
            array(
                $this->arrayToObject(array(
                    'reportingDate'        => '2015-01-01',
                    'type'                 => 'actual',
                    'tdo'                  => 1,
                    'cap'                  => 55,
                    'cpc'                  => 66,
                    't1x'                  => 77,
                    't2x'                  => 88,
                    'gitw'                 => -101,
                    'lf'                   => 100,
                )),
                array(
                    array('INVALID_VALUE', 'Gitw', '-101'),
                ),
                false,
            ),
            // Test Invalid lf
            array(
                $this->arrayToObject(array(
                    'reportingDate'        => '2015-01-01',
                    'type'                 => 'actual',
                    'tdo'                  => 1,
                    'cap'                  => 55,
                    'cpc'                  => 66,
                    't1x'                  => 77,
                    't2x'                  => 88,
                    'gitw'                 => 99,
                    'lf'                   => 'asdf',
                )),
                array(
                    array('INVALID_VALUE', 'Lf', 'asdf'),
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
