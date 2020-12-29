<?php

namespace Bernard\Tests\Message;

use Bernard\Message\PlainMessage;

final class PlainMessageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function it_has_arguments()
    {
        $message = new PlainMessage('SendNewsletter', [
            'key1' => 1,
            'key2' => [1, 2, 3, 4],
            'key3' => null,
        ]);

        $this->assertArrayHasKey('key1', $message);

        $this->assertEquals(1, $message['key1']);
        $this->assertEquals([1, 2, 3, 4], $message['key2']);
        $this->assertNull($message['key3']);

        $this->assertTrue(isset($message->key1));

        $this->assertEquals(1, $message->key1);
        $this->assertEquals([1, 2, 3, 4], $message->key2);
        $this->assertNull($message->key3);
    }

    /**
     * @test
     */
    public function it_implements_ArrayAccess()
    {
        $this->assertInstanceOf(\ArrayAccess::class, new PlainMessage('SendNewsletter'));
    }

    /**
     * @test
     */
    public function it_is_immutable_to_magic_set()
    {
        $this->expectException(\LogicException::class);

        $message = new PlainMessage('SendNewsletter');
        $message->key1 = 1;
    }

    /**
     * @test
     */
    public function it_is_immutable_to_magic_unset()
    {
        $this->expectException(\LogicException::class);

        $message = new PlainMessage('SendNewsletter', ['key1' => 1]);
        unset($message->key1);
    }

    /**
     * @test
     */
    public function it_is_immutable_to_offset_set()
    {
        $this->expectException(\LogicException::class);

        $message = new PlainMessage('SendNewsletter');
        $message['key1'] = 1;
    }

    /**
     * @test
     */
    public function it_is_immutable_to_offset_unset()
    {
        $this->expectException(\LogicException::class);

        $message = new PlainMessage('SendNewsletter', ['key1' => 1]);
        unset($message['key1']);
    }
}
