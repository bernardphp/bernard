<?php

declare(strict_types=1);

namespace Bernard\Tests\Queue;

use Bernard\Envelope;

abstract class AbstractQueueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dataClosedMethods
     */
    public function testNotAllowedWhenClosed($method, array $arguments = []): void
    {
        $this->expectException(\Bernard\Exception\InvalidOperationException::class);
        $this->expectExceptionMessage('Queue "send-newsletter" is closed.');

        $queue = $this->createQueue('send-newsletter');
        $queue->close();

        \call_user_func_array([$queue, $method], $arguments);
    }

    public function testNameAsToString(): void
    {
        $queue = $this->createQueue('long-name');

        $this->assertEquals('long-name', (string) $queue);
        $this->assertEquals('long-name', $queue);
    }

    public function dataClosedMethods()
    {
        return [
            ['peek', [0, 10]],
            ['count'],
            ['dequeue'],
            ['enqueue', [
                new Envelope($this->createMock('Bernard\Message')),
            ]],
            ['acknowledge', [
                new Envelope($this->createMock('Bernard\Message')),
            ]],
        ];
    }

    abstract protected function createQueue($name);
}
