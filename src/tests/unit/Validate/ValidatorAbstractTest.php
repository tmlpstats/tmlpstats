<?php
namespace TmlpStats\Tests\Validate;

use stdClass;

class ValidatorAbstractImplementation extends \TmlpStats\Validate\ValidatorAbstract
{
    protected $sheetId = 12;

    public function setData($data)
    {
        $this->data = $data;
    }

    protected function validate($data)
    {
        return $this->isValid;
    }
}

class ValidatorAbstractTest extends ValidatorTestAbstract
{
    protected $testClass = ValidatorAbstractImplementation::class;

    protected $dataFields = [];

    public function testRunSuccessfulValidation()
    {
        $data = new stdClass;

        $validator = $this->getObjectMock([
            'validate',
        ]);
        $validator->expects($this->once())
                  ->method('validate')
                  ->with($this->equalTo($data))
                  ->will($this->returnValue(true));

        $result = $validator->run($data);

        $this->assertTrue($result);
    }

    public function testRunFailedValidation()
    {
        $data = new stdClass;

        $validator = $this->getObjectMock([
            'validate',
        ]);

        $validator->expects($this->once())
                  ->method('validate')
                  ->with($this->equalTo($data))
                  ->will($this->returnValue(false));

        $result = $validator->run($data);

        $this->assertFalse($result);
    }

    public function testGetMessagesReturnsMessages()
    {
        $messages = [
            'message1' => 'OMG!',
            'message2' => 'Not such a big deal.',
        ];

        $validator = new ValidatorAbstractImplementation(new stdClass);
        $this->setProperty($validator, 'messages', $messages);
        $result = $validator->getMessages();

        $this->assertEquals($messages, $result);
    }

    public function testGetOffsetReturnsOffset()
    {
        $offset = 50;

        $data         = new stdClass;
        $data->offset = $offset;

        $validator = new ValidatorAbstractImplementation(new stdClass);
        $result    = $this->runMethod($validator, 'getOffset', $data);

        $this->assertEquals($offset, $result);
    }

    public function testAddMessageHandlesBasicMessage()
    {
        $offset    = 50;
        $sheetId   = 12;
        $messageId = 'IMPORT_TAB_FAILED';

        $messageArray = [
            'type'       => 'error',
            'section'    => $sheetId,
            'message'    => 'Unable to import tab.',
            'offset'     => $offset,
            'offsetType' => 'row',
            'id'         => $messageId,
        ];

        $data         = new stdClass;
        $data->offset = $offset;

        $validator = new ValidatorAbstractImplementation(new stdClass);
        $validator->setData($data);

        $result = $this->runMethod($validator, 'addMessage', $messageId);

        $messages = $this->getProperty($validator, 'messages');

        $this->assertEquals([$messageArray], $messages);
    }

    public function testAddMessageHandlesMessageMultipleArguments()
    {
        $offset      = 50;
        $sheetId     = 12;
        $messageId   = 'INVALID_VALUE';
        $displayName = 'My Super Special Variable';
        $value       = 42;

        $messageArray = [
            'type'       => 'error',
            'section'    => $sheetId,
            'message'    => "Incorrect value provided for {$displayName} ('{$value}').",
            'offset'     => $offset,
            'offsetType' => 'row',
            'id'         => $messageId,
        ];

        $data         = new stdClass;
        $data->offset = $offset;

        $validator = new ValidatorAbstractImplementation(new stdClass);
        $validator->setData($data);

        $result = $this->runMethod($validator, 'addMessage', $messageId, $displayName, $value);

        $messages = $this->getProperty($validator, 'messages');

        $this->assertEquals([$messageArray], $messages);
    }
}
