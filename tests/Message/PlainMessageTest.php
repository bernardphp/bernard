<?php

declare(strict_types=1);

namespace Bernard\Tests\Message;

use Bernard\Message\PlainMessage;

final class PlainMessageTest extends \PHPUnit\Framework\TestCase
{
    public function testItHasArguments(): void
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

    public function testItImplementsArrayAccess(): void
    {
        $this->assertInstanceOf(\ArrayAccess::class, new PlainMessage('SendNewsletter'));
    }

    public function testItIsImmutableToMagicSet(): void
    {
        $this->expectException(\LogicException::class);

        $message = new PlainMessage('SendNewsletter');
        $message->key1 = 1;
    }

    public function testItIsImmutableToMagicUnset(): void
    {
        $this->expectException(\LogicException::class);

        $message = new PlainMessage('SendNewsletter', ['key1' => 1]);
        unset($message->key1);
    }

    public function testItIsImmutableToOffsetSet(): void
    {
        $this->expectException(\LogicException::class);

        $message = new PlainMessage('SendNewsletter');
        $message['key1'] = 1;
    }

    public function testItIsImmutableToOffsetUnset(): void
    {
        $this->expectException(\LogicException::class);

        $message = new PlainMessage('SendNewsletter', ['key1' => 1]);
        unset($message['key1']);
    }
}
