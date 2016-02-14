<?php
namespace TmlpStats\Tests\Validate;

use Carbon\Carbon;
use stdClass;
use TmlpStats\Tests\Traits\MocksQuarters;

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
    use MocksQuarters;

    protected $testClass = ValidatorAbstractImplementation::class;

    protected $dataFields = [];

    public function testGetter()
    {
        $statsReport = new stdClass;
        $statsReport->center = new stdClass;
        $statsReport->center->name = 'Vancouver';

        $statsReport->quarter = $this->getQuarterMock([], [
            'startWeekendDate' => Carbon::createFromDate(2015, 11, 20)->startOfDay(),
        ]);

        $statsReport->reportingDate = Carbon::create(2015, 12, 18);

        $validator = new ValidatorAbstractImplementation($statsReport);

        $this->assertSame($statsReport->center, $validator->center);
        $this->assertSame($statsReport->quarter, $validator->quarter);
        $this->assertSame($statsReport->reportingDate, $validator->reportingDate);
        $this->assertNull($validator->nonExistantProperty);
    }

    /**
     * @dataProvider providerRun
     */
    public function testRun($data, $supplementalData, $validateResponse, $expectedResponse)
    {
        $validator = $this->getObjectMock([
            'validate',
        ]);
        $validator->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($data))
            ->will($this->returnValue($validateResponse));

        if ($supplementalData) {
            $result = $validator->run($data, $supplementalData);
        } else {
            $result = $validator->run($data);
        }

        $this->assertEquals($data, $this->getProperty($validator, 'data'));
        $this->assertEquals($supplementalData, $this->getProperty($validator, 'supplementalData'));
        $this->assertEquals($expectedResponse, $result);
    }

    public function providerRun()
    {
        $data = new stdClass;
        $supplementalData = ['moreDataz'];
        return [
            // Successful validation
            [
                'data' => $data,
                null,
                true,
                true,
            ],
            // Successful validation with $supplementalData
            [
                'data' => $data,
                $supplementalData,
                true,
                true,
            ],
            // Failed validation
            [
                'data' => $data,
                null,
                false,
                false,
            ],
        ];
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

    public function testGetWorkingDataReturnsEmptyArray()
    {
        $validator = new ValidatorAbstractImplementation(new stdClass);

        $result = $validator->getWorkingData();

        $this->assertEmpty($result);
    }

    public function testResetWorkingDataIsImplemented()
    {
        $validator = new ValidatorAbstractImplementation(new stdClass);

        $validator->resetWorkingData();
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

    public function testAddMessageHandlesBasicMessage()
    {
        $offset = 50;
        $sheetId = 12;
        $messageId = 'IMPORT_TAB_FAILED';

        $messageArray = [
            'type' => 'error',
            'section' => $sheetId,
            'message' => 'Unable to import tab.',
            'offset' => $offset,
            'offsetType' => 'row',
            'id' => $messageId,
        ];

        $data = new stdClass;
        $data->offset = $offset;

        $validator = new ValidatorAbstractImplementation(new stdClass);
        $validator->setData($data);

        $result = $this->runMethod($validator, 'addMessage', $messageId);

        $messages = $this->getProperty($validator, 'messages');

        $this->assertEquals([$messageArray], $messages);
    }

    public function testAddMessageHandlesMessageMultipleArguments()
    {
        $offset = 50;
        $sheetId = 12;
        $messageId = 'INVALID_VALUE';
        $displayName = 'My Super Special Variable';
        $value = 42;

        $messageArray = [
            'type' => 'error',
            'section' => $sheetId,
            'message' => "Incorrect value provided for {$displayName} ('{$value}').",
            'offset' => $offset,
            'offsetType' => 'row',
            'id' => $messageId,
        ];

        $data = new stdClass;
        $data->offset = $offset;

        $validator = new ValidatorAbstractImplementation(new stdClass);
        $validator->setData($data);

        $result = $this->runMethod($validator, 'addMessage', $messageId, $displayName, $value);

        $messages = $this->getProperty($validator, 'messages');

        $this->assertEquals([$messageArray], $messages);
    }
}
