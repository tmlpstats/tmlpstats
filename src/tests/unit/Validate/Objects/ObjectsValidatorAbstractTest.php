<?php
namespace TmlpStats\Tests\Validate\Objects;

use Log;
use stdClass;

class ValidatorAbstractImplementation extends \TmlpStats\Validate\Objects\ObjectsValidatorAbstract
{
    protected $sheetId = 12;
    protected $dataValidators = [];

    protected function populateValidators($data)
    {
    }

    protected function validate($data)
    {
        return $this->isValid;
    }
}

class ObjectsValidatorAbstractTest extends ObjectsValidatorTestAbstract
{
    protected $testClass = ValidatorAbstractImplementation::class;
    protected $instantiateApp = true;
    protected $dataFields = [];

    public function testRunSuccessfulValidation()
    {
        $data = new stdClass;
        $data->field1 = 1;
        $data->field2 = '2';
        $data->field3 = 3;
        $data->field4 = 'four';

        $dataValidator = $this->getObjectMock(['validate']);
        $dataValidator->expects($this->exactly(4))
            ->method('validate')
            ->withConsecutive(
                [$this->equalTo($data->field1)],
                [$this->equalTo($data->field2)],
                [$this->equalTo($data->field3)],
                [$this->equalTo($data->field4)]
            )
            ->will($this->onConsecutiveCalls(true, true, true, true));

        $dataValidators = [
            'field1' => $dataValidator,
            'field2' => $dataValidator,
            'field3' => $dataValidator,
            'field4' => $dataValidator,
        ];

        $validator = $this->getObjectMock([
            'populateValidators',
            'getValueDisplayName',
            'addMessage',
            'validate',
        ]);
        $validator->expects($this->once())
            ->method('populateValidators');
        $validator->expects($this->never())
            ->method('getValueDisplayName');
        $validator->expects($this->never())
            ->method('addMessage');
        $validator->expects($this->once())
            ->method('validate');

        $this->setProperty($validator, 'dataValidators', $dataValidators);

        $result = $validator->run($data);

        $this->assertTrue($result);
    }

    public function testRunSuccessfulValidationWhenEmpty()
    {
        $data = new stdClass;

        $validator = $this->getObjectMock([
            'populateValidators',
            'getValueDisplayName',
            'addMessage',
            'validate',
        ]);
        $validator->expects($this->once())
            ->method('populateValidators');
        $validator->expects($this->never())
            ->method('getValueDisplayName');
        $validator->expects($this->never())
            ->method('addMessage');
        $validator->expects($this->once())
            ->method('validate');

        $result = $validator->run($data);

        $this->assertTrue($result);
    }

    public function testRunSkipped()
    {
        $data = new stdClass;

        $validator = $this->getObjectMock([
            'populateValidators',
            'validate',
        ]);
        $validator->expects($this->once())
            ->method('populateValidators');
        $validator->expects($this->never())
            ->method('validate');

        $this->setProperty($validator, 'skipped', true);

        $result = $validator->run($data);

        $this->assertTrue($result);
    }

    public function testRunFailedValidation()
    {
        $data = new stdClass;
        $data->field1 = null;
        $data->field2 = '';
        $data->field3 = 3;
        $data->field4 = 'four';

        $dataValidator = $this->getObjectMock(['validate']);
        $dataValidator->expects($this->exactly(4))
            ->method('validate')
            ->withConsecutive(
                [$this->equalTo($data->field1)],
                [$this->equalTo($data->field2)],
                [$this->equalTo($data->field3)]
            )
            ->will($this->onConsecutiveCalls(false, false, false, true));

        $dataValidators = [
            'field1' => $dataValidator,
            'field2' => $dataValidator,
            'field3' => $dataValidator,
            'field4' => $dataValidator,
        ];

        $validator = $this->getObjectMock([
            'populateValidators',
            'getValueDisplayName',
            'addMessage',
            'validate',
        ]);
        $validator->expects($this->once())
            ->method('populateValidators');
        $validator->expects($this->exactly(3))
            ->method('getValueDisplayName')
            ->withConsecutive(
                [$this->equalTo('field1')],
                [$this->equalTo('field2')],
                [$this->equalTo('field3')]
            )
            ->will($this->onConsecutiveCalls('Field 1', 'Field 2', 'Field 3'));
        $validator->expects($this->exactly(3))
            ->method('addMessage')
            ->withConsecutive(
                [
                    $this->equalTo('INVALID_VALUE'),
                    $this->equalTo('Field 1'),
                    $this->equalTo('[empty]'),
                ],
                [
                    $this->equalTo('INVALID_VALUE'),
                    $this->equalTo('Field 2'),
                    $this->equalTo('[empty]'),
                ],
                [
                    $this->equalTo('INVALID_VALUE'),
                    $this->equalTo('Field 3'),
                    $this->equalTo(3),
                ]
            )
            ->will($this->onConsecutiveCalls('Field 1', 'Field 2', 'Field 3'));
        $validator->expects($this->once())
            ->method('validate');

        $this->setProperty($validator, 'dataValidators', $dataValidators);

        $result = $validator->run($data);

        $this->assertFalse($result);
    }

    public function testGetValueDisplayNameReturnsWords()
    {
        $value = 'someValueToDisplay';
        $display = 'Some Value To Display';

        $validator = new ValidatorAbstractImplementation(new stdClass);
        $result = $this->runMethod($validator, 'getValueDisplayName', $value);

        $this->assertEquals($display, $result);
    }

    public function testGetDateObjectReturnsCorrectDateObject()
    {
        $date = '2015-05-11';

        $validator = new ValidatorAbstractImplementation(new stdClass);
        $result = $this->runMethod($validator, 'getDateObject', $date);

        $this->assertInstanceOf('Carbon\Carbon', $result);
        $this->assertEquals("2015-05-11 00:00:00", $result->toDateTimeString());
    }

    public function testGetDateObjectReturnsCorrectDateObjectForMisformedDate()
    {
        $date = '05/11/2015';

        $validator = new ValidatorAbstractImplementation(new stdClass);
        $result = $this->runMethod($validator, 'getDateObject', $date);

        $this->assertInstanceOf('Carbon\Carbon', $result);
        $this->assertEquals("2015-05-11 00:00:00", $result->toDateTimeString());
    }

    public function testGetDateObjectReturnsNullForInvalidDates()
    {
        $date = 'asdf';

        Log::shouldReceive('error');

        $validator = new ValidatorAbstractImplementation(new stdClass);
        $result = $this->runMethod($validator, 'getDateObject', $date);

        $this->assertNull($result);
    }
}
