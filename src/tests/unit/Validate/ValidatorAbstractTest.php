<?php
namespace TmlpStats\Tests\Validate;

use TmlpStats\Message;
use Illuminate\Support\Facades\Log;
use stdClass;

class ValidatorAbstractImplementation extends \TmlpStats\Validate\ValidatorAbstract
{
    protected $sheetId = 12;
    protected $dataValidators = array();

    public function setData($data)
    {
        $this->data = $data;
    }

    protected function populateValidators($data) { }
    protected function validate($data) { }
}

class ValidatorAbstractTest extends ValidatorTestAbstract
{
    protected $testClass = 'TmlpStats\Tests\Validate\ValidatorAbstractImplementation';

    protected $dataFields = array();

    public function testRunSuccessfulValidation()
    {
        $data = new stdClass;
        $data->field1 = 1;
        $data->field2 = '2';
        $data->field3 = 3;
        $data->field4 = 'four';

        $dataValidator = $this->getObjectMock(array('validate'));
        $dataValidator->expects($this->exactly(4))
                      ->method('validate')
                      ->withConsecutive(
                            array($this->equalTo($data->field1)),
                            array($this->equalTo($data->field2)),
                            array($this->equalTo($data->field3)),
                            array($this->equalTo($data->field4))
                        )
                      ->will($this->onConsecutiveCalls(true, true, true, true));

        $dataValidators = array(
            'field1' => $dataValidator,
            'field2' => $dataValidator,
            'field3' => $dataValidator,
            'field4' => $dataValidator,
        );

        $validator = $this->getObjectMock(array(
            'populateValidators',
            'getValueDisplayName',
            'addMessage',
            'validate',
        ));
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

        $validator = $this->getObjectMock(array(
            'populateValidators',
            'getValueDisplayName',
            'addMessage',
            'validate',
        ));
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

    public function testRunFailedValidation()
    {
        $data = new stdClass;
        $data->field1 = null;
        $data->field2 = '';
        $data->field3 = 3;
        $data->field4 = 'four';

        $dataValidator = $this->getObjectMock(array('validate'));
        $dataValidator->expects($this->exactly(4))
                      ->method('validate')
                      ->withConsecutive(
                            array($this->equalTo($data->field1)),
                            array($this->equalTo($data->field2)),
                            array($this->equalTo($data->field3))
                        )
                      ->will($this->onConsecutiveCalls(false, false, false, true));

        $dataValidators = array(
            'field1' => $dataValidator,
            'field2' => $dataValidator,
            'field3' => $dataValidator,
            'field4' => $dataValidator,
        );

        $validator = $this->getObjectMock(array(
            'populateValidators',
            'getValueDisplayName',
            'addMessage',
            'validate',
        ));
        $validator->expects($this->once())
                  ->method('populateValidators');
        $validator->expects($this->exactly(3))
                  ->method('getValueDisplayName')
                  ->withConsecutive(
                        array($this->equalTo('field1')),
                        array($this->equalTo('field2')),
                        array($this->equalTo('field3'))
                    )
                  ->will($this->onConsecutiveCalls('Field 1', 'Field 2', 'Field 3'));
        $validator->expects($this->exactly(3))
                  ->method('addMessage')
                  ->withConsecutive(
                        array(
                            $this->equalTo('INVALID_VALUE'),
                            $this->equalTo('Field 1'),
                            $this->equalTo('[empty]'),
                        ),
                        array(
                            $this->equalTo('INVALID_VALUE'),
                            $this->equalTo('Field 2'),
                            $this->equalTo('[empty]'),
                        ),
                        array(
                            $this->equalTo('INVALID_VALUE'),
                            $this->equalTo('Field 3'),
                            $this->equalTo(3),
                        )
                    )
                  ->will($this->onConsecutiveCalls('Field 1', 'Field 2', 'Field 3'));
        $validator->expects($this->once())
                  ->method('validate');

        $this->setProperty($validator, 'dataValidators', $dataValidators);

        $result = $validator->run($data);

        $this->assertFalse($result);
    }

    public function testGetMessagesReturnsMessages()
    {
        $messages = array(
            'message1' => 'OMG!',
            'message2' => 'Not such a big deal.',
        );

        $validator = new ValidatorAbstractImplementation(new stdClass);
        $this->setProperty($validator, 'messages', $messages);
        $result = $validator->getMessages();

        $this->assertEquals($messages, $result);
    }

    public function testGetValueDisplayNameReturnsWords()
    {
        $value = 'someValueToDisplay';
        $display = 'Some Value To Display';

        $validator = new ValidatorAbstractImplementation(new stdClass);
        $result = $this->runMethod($validator, 'getValueDisplayName', $value);

        $this->assertEquals($display, $result);
    }

    public function testGetOffsetReturnsOffset()
    {
        $offset = 50;

        $data = new stdClass;
        $data->offset = $offset;

        $validator = new ValidatorAbstractImplementation(new stdClass);
        $result = $this->runMethod($validator, 'getOffset', $data);

        $this->assertEquals($offset, $result);
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

    public function testAddMessageHandlesBasicMessage()
    {
        $offset = 50;
        $sheetId = 12;
        $messageId = 'IMPORT_TAB_FAILED';

        $message = Message::create($sheetId);

        $messageArray = array(
            'type' => 'error',
            'section' => $sheetId,
            'message' => 'Unable to import tab.',
            'offset' => $offset,
            'offsetType' => 'row',
        );

        $data = new stdClass;
        $data->offset = $offset;

        $validator = new ValidatorAbstractImplementation(new stdClass);
        $validator->setData($data);

        $result = $this->runMethod($validator, 'addMessage', $messageId);

        $messages = $this->getProperty($validator, 'messages');

        $this->assertEquals(array($messageArray), $messages);
    }

    public function testAddMessageHandlesMessageMultipleArguments()
    {
        $offset = 50;
        $sheetId = 12;
        $messageId = 'INVALID_VALUE';
        $displayName = 'My Super Special Variable';
        $value = 42;

        $message = Message::create($sheetId);

        $messageArray = array(
            'type' => 'error',
            'section' => $sheetId,
            'message' => "Incorrect value provided for {$displayName} ('{$value}').",
            'offset' => $offset,
            'offsetType' => 'row',
        );

        $data = new stdClass;
        $data->offset = $offset;

        $validator = new ValidatorAbstractImplementation(new stdClass);
        $validator->setData($data);

        $result = $this->runMethod($validator, 'addMessage', $messageId, $displayName, $value);

        $messages = $this->getProperty($validator, 'messages');

        $this->assertEquals(array($messageArray), $messages);
    }

    public function testGetStatsReportReturnsStatsReport()
    {
        $statsReport = new stdClass;
        $statsReport->sameGuarantee = true;

        $validator = new ValidatorAbstractImplementation($statsReport);
        $result = $this->runMethod($validator, 'getStatsReport');

        $this->assertSame($statsReport, $result);
    }
}
