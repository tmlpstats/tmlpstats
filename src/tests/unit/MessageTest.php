<?php
namespace TmlpStats\Tests\Unit;

use TmlpStats\Message;
use Exception;
use TmlpStats\Tests\TestAbstract;

class MessageTest extends TestAbstract
{
    protected $testClass = Message::class;

    public function testCreateReturnsNewMessage()
    {
        $message = Message::create('mySection');

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals('mySection', $this->getProperty($message, 'section'));
    }

    /**
     * @dataProvider providerAddMessage
     */
    public function testAddMessage($messageId, $offset, $arguments, $expectedResponse)
    {
        $messageList = [
            'MESSAGE_ONE'   => [
                'type'      => Message::ERROR,
                'format'    => "Message One.",
                'arguments' => [],
            ],
            'MESSAGE_TWO'   => [
                'type'      => Message::WARNING,
                'format'    => "Message %%number%%.",
                'arguments' => ['%%number%%'],
            ],
            'MESSAGE_THREE' => [
                'type'      => Message::DEBUG,
                'format'    => "%%quality%% Message %%number%%.",
                'arguments' => [
                    '%%number%%',
                    '%%quality%%',
                ],
            ],
        ];

        $message = Message::create('mySection');

        $this->setProperty($message, 'messageList', $messageList);

        $args   = array_merge([$messageId, $offset], $arguments);
        $result = call_user_func_array([$message, 'addMessage'], $args);

        $this->assertEquals($expectedResponse, $result);
    }

    public function providerAddMessage()
    {
        return [
            [
                'MESSAGE_ONE',
                null,
                [],
                [
                    'id'      => 'MESSAGE_ONE',
                    'type'    => 'error',
                    'section' => 'mySection',
                    'message' => "Message One.",
                ],
            ],
            [
                'MESSAGE_ONE',
                '1',
                [],
                [
                    'id'         => 'MESSAGE_ONE',
                    'type'       => 'error',
                    'section'    => 'mySection',
                    'message'    => "Message One.",
                    'offset'     => '1',
                    'offsetType' => 'row',
                ],
            ],
            [
                'MESSAGE_TWO',
                'C',
                ['Two'],
                [
                    'id'         => 'MESSAGE_TWO',
                    'type'       => 'warning',
                    'section'    => 'mySection',
                    'message'    => "Message Two.",
                    'offset'     => 'C',
                    'offsetType' => 'column',
                ],
            ],
            [
                'MESSAGE_THREE',
                'C1',
                ['Three', 'Sweet'],
                [
                    'id'         => 'MESSAGE_THREE',
                    'type'       => 'debug',
                    'section'    => 'mySection',
                    'message'    => "Sweet Message Three.",
                    'offset'     => 'C1',
                    'offsetType' => 'cell',
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerMessageFromFormats
     */
    public function testGetMessageFromFormat($messageId, $format, $argumentNames, $arguments, $expectedResponse)
    {
        if ($expectedResponse instanceof Exception) {
            $this->setExpectedException(Exception::class);
        }

        $message = Message::create('mySection');

        $result = $this->runMethod($message, 'getMessageFromFormat', $messageId, $format, $argumentNames, $arguments);

        $this->assertEquals($expectedResponse, $result);
    }

    public function providerMessageFromFormats()
    {
        return [
            [1, 'No replacements', [], [], 'No replacements'],
            [2, '%%ONE%% replacement', ['%%ONE%%'], ['One'], 'One replacement'],
            [3, '%%ONE%% %%TWO%% replacements', ['%%ONE%%', '%%TWO%%'], ['Two', 'cool'], 'Two cool replacements'],
            [4, 'No replacements', ['%%ONE%%', '%%TWO%%'], ['One'], new Exception()],
        ];
    }

    /**
     * @dataProvider providerOffsetTypes
     */
    public function testGetOffsetType($offset, $expectedResponse)
    {
        $message = Message::create('mySection');

        $offsetString = $this->runMethod($message, 'getOffsetType', $offset);

        $this->assertEquals($expectedResponse, $offsetString);
    }

    public function providerOffsetTypes()
    {
        return [
            ['a', 'column'],
            ['AA', 'column'],
            ['1', 'row'],
            [22, 'row'],
            ['A1', 'cell'],
            ['cc22', 'cell'],
            ['a1b2c3', 'offset'],
            ['', 'offset'],
            [null, 'offset'],
        ];
    }

    /**
     * @dataProvider providerMessageTypes
     */
    public function testGetMessageTypeString($type, $expectedResponse)
    {
        $message = Message::create('mySection');

        $typeString = $this->runMethod($message, 'getMessageTypeString', $type);

        $this->assertEquals($expectedResponse, $typeString);
    }

    public function providerMessageTypes()
    {
        return [
            [Message::EMERGENCY, 'emergency'],
            [Message::ALERT, 'alert'],
            [Message::CRITICAL, 'critical'],
            [Message::ERROR, 'error'],
            [Message::WARNING, 'warning'],
            [Message::NOTICE, 'notice'],
            [Message::INFO, 'info'],
            [Message::DEBUG, 'debug'],
            [1234, 'debug'],
        ];
    }
}
