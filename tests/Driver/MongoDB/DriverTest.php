<?php

declare(strict_types=1);

namespace Bernard\Tests\Driver\MongoDB;

use ArrayIterator;
use Bernard\Driver\MongoDB\Driver;
use MongoDate;
use MongoId;

class DriverTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $messages;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $queues;

    /** @var Driver */
    private $driver;

    protected function setUp(): void
    {
        if (!class_exists('MongoCollection')) {
            $this->markTestSkipped('MongoDB extension is not available.');
        }

        $this->queues = $this->getMockMongoCollection();
        $this->messages = $this->getMockMongoCollection();
        $this->driver = new Driver($this->queues, $this->messages);
    }

    public function testListQueues(): void
    {
        $this->queues->expects($this->once())
            ->method('distinct')
            ->with('_id')
            ->willReturn(['foo', 'bar']);

        $this->assertSame(['foo', 'bar'], $this->driver->listQueues());
    }

    public function testCreateQueue(): void
    {
        $this->queues->expects($this->once())
            ->method('update')
            ->with(['_id' => 'foo'], ['_id' => 'foo'], ['upsert' => true]);

        $this->driver->createQueue('foo');
    }

    public function testCountMessages(): void
    {
        $this->messages->expects($this->once())
            ->method('count')
            ->with(['queue' => 'foo', 'visible' => true])
            ->willReturn(2);

        $this->assertSame(2, $this->driver->countMessages('foo'));
    }

    public function testPushMessage(): void
    {
        $this->messages->expects($this->once())
            ->method('insert')
            ->with($this->callback(function ($data) {
                return $data['queue'] === 'foo' &&
                       $data['message'] === 'message1' &&
                       $data['sentAt'] instanceof MongoDate &&
                       $data['visible'] === true;
            }));

        $this->driver->pushMessage('foo', 'message1');
    }

    public function testPopMessageWithFoundMessage(): void
    {
        $this->messages->expects($this->atLeastOnce())
            ->method('findAndModify')
            ->with(
                ['queue' => 'foo', 'visible' => true],
                ['$set' => ['visible' => false]],
                ['message' => 1],
                ['sort' => ['sentAt' => 1]]
            )
            ->willReturn(['message' => 'message1', '_id' => '000000000000000000000000']);

        [$message, $receipt] = $this->driver->popMessage('foo');
        $this->assertSame('message1', $message);
        $this->assertSame('000000000000000000000000', $receipt);
    }

    /**
     * @medium
     */
    public function testPopMessageWithMissingMessage(): void
    {
        $this->messages->expects($this->atLeastOnce())
            ->method('findAndModify')
            ->with(
                ['queue' => 'foo', 'visible' => true],
                ['$set' => ['visible' => false]],
                ['message' => 1],
                ['sort' => ['sentAt' => 1]]
            )
            ->willReturn(false);

        [$message, $receipt] = $this->driver->popMessage('foo', 1);
        $this->assertNull($message);
        $this->assertNull($receipt);
    }

    public function testAcknowledgeMessage(): void
    {
        $this->messages->expects($this->once())
            ->method('remove')
            ->with($this->callback(function ($query) {
                return $query['_id'] instanceof MongoId &&
                       (string) $query['_id'] === '000000000000000000000000' &&
                       $query['queue'] === 'foo';
            }));

        $this->driver->acknowledgeMessage('foo', '000000000000000000000000');
    }

    public function testPeekQueue(): void
    {
        $cursor = $this->getMockBuilder('MongoCursor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->messages->expects($this->once())
            ->method('find')
            ->with(['queue' => 'foo', 'visible' => true], ['_id' => 0, 'message' => 1])
            ->willReturn($cursor);

        $cursor->expects($this->at(0))
            ->method('sort')
            ->with(['sentAt' => 1])
            ->willReturn($cursor);

        $cursor->expects($this->at(1))
            ->method('limit')
            ->with(20)
            ->willReturn($cursor);

        /* Rather than mock MongoCursor's iterator interface, take advantage of
         * the final fluent method call and return an ArrayIterator. */
        $cursor->expects($this->at(2))
            ->method('skip')
            ->with(0)
            ->willReturn(new ArrayIterator([
                ['message' => 'message1'],
                ['message' => 'message2'],
            ]));

        $this->assertSame(['message1', 'message2'], $this->driver->peekQueue('foo'));
    }

    public function testRemoveQueue(): void
    {
        $this->queues->expects($this->once())
            ->method('remove')
            ->with(['_id' => 'foo']);

        $this->messages->expects($this->once())
            ->method('remove')
            ->with(['queue' => 'foo']);

        $this->driver->removeQueue('foo');
    }

    public function testInfo(): void
    {
        $this->queues->expects($this->once())
            ->method('__toString')
            ->willReturn('db.queues');

        $this->messages->expects($this->once())
            ->method('__toString')
            ->willReturn('db.messages');

        $info = [
            'messages' => 'db.messages',
            'queues' => 'db.queues',
        ];

        $this->assertSame($info, $this->driver->info());
    }

    private function getMockMongoCollection()
    {
        return $this->getMockBuilder('MongoCollection')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
