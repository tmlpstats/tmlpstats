<?php
namespace TmlpStats\Tests\Unit\Domain;

use TmlpStats\Contracts;
use TmlpStats\Domain;
use TmlpStats\Tests\TestAbstract;

class ReferenceableImplementation implements Contracts\Referenceable
{
    public function getKey()
    {
        return 1;
    }

    public function getReference()
    {
        return 1234;
    }
}

class ValidationMessageTest extends TestAbstract
{
    protected $instantiateApp = true;

    public function testStaticCreateSetsDefaults()
    {
        $params = [
            'id' => 'ZZZ_TEST_MESSAGE_0_PARAM',
        ];
        $expected = [
            'id' => 'ZZZ_TEST_MESSAGE_0_PARAM',
            'level' => 'error',
            'reference' => null,
            'message' => 'This message has 0 params.',
        ];

        $message = Domain\ValidationMessage::create('error', $params);
        $this->assertSame($expected, $message->toArray());
    }

    public function testStaticCreateGetsReferenceForReferenceable()
    {
        $params = [
            'id' => 'ZZZ_TEST_MESSAGE_0_PARAM',
            'ref' => new ReferenceableImplementation(),
        ];
        $expected = [
            'id' => 'ZZZ_TEST_MESSAGE_0_PARAM',
            'level' => 'error',
            'reference' => 1234,
            'message' => 'This message has 0 params.',
        ];

        $message = Domain\ValidationMessage::create('error', $params);
        $this->assertSame($expected, $message->toArray());
    }

    public function testStaticCreateThrowsExceptionWhenNoIdProvided()
    {
        $this->expectException(\Exception::class);

        $message = Domain\ValidationMessage::create('error', []);
    }

    /**
     * @dataProvider providerStaticCreators
     */
    public function testStaticCreators($level, $data, $expectedMessage)
    {
        $message = Domain\ValidationMessage::$level($data);

        $this->assertEquals($level, $message->level());

        $expectedMessage['level'] = $level;
        $this->assertSame($expectedMessage, $message->toArray());
    }

    public function providerStaticCreators()
    {
        $params = [
            'id' => 'ZZZ_TEST_MESSAGE_1_PARAM',
            'ref' => 5678,
            'params' => ['one' => 'myParam'],
        ];
        $message = [
            'id' => 'ZZZ_TEST_MESSAGE_1_PARAM',
            'level' => null,
            'reference' => 5678,
            'message' => 'This message has 1 param: myParam.',
        ];
        return [
            ['error', $params, $message],
            ['info', $params, $message],
            ['warning', $params, $message],
        ];
    }

    public function testMessageSerializesToJson()
    {
        $data = [
            'id' => 'ZZZ_TEST_MESSAGE_2_PARAM',
            'ref' => 5678,
            'params' => ['one' => 'myParam', 'two' => 'myOtherParam'],
        ];
        $expected = json_encode([
            'id' => 'ZZZ_TEST_MESSAGE_2_PARAM',
            'level' => 'error',
            'reference' => 5678,
            'message' => 'This message has 2 params: myParam and myOtherParam.',
        ]);

        $message = Domain\ValidationMessage::error($data);

        $this->assertEquals($expected, json_encode($message));
    }
}
