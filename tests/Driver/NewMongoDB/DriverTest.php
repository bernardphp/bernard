<?php

namespace Bernard\Tests\Driver\NewMongoDB;

use Bernard\Driver\MongoDB\Driver;
use ArrayIterator;
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

    public function setUp()
    {
        if (!class_exists('MongoCollection')) {
            $this->markTestSkipped('MongoDB extension is not available.');
        }

        $this->queues = $this->getMockMongoCollection();
        $this->messages = $this->getMockMongoCollection();
        $this->driver = new Driver($this->queues, $this->messages);
    }

    public function testListQueues()
    {
        $this->queues->expects($this->once())
            ->method('distinct')
            ->with('_id')
            ->will($this->returnValue(['foo', 'bar']));

        $this->assertSame(['foo', 'bar'], $this->driver->listQueues());
    }

    public function testCreateQueue()
    {
        $this->queues->expects($this->once())
            ->method('update')
            ->with(['_id' => 'foo'], ['_id' => 'foo'], ['upsert' => true]);

        $this->driver->createQueue('foo');
    }

    public function testCountMessages()
    {
        $this->messages->expects($this->once())
            ->method('count')
            ->with(['queue' => 'foo', 'visible' => true])
            ->will($this->returnValue(2));

        $this->assertSame(2, $this->driver->countMessages('foo'));
    }

    public function testPushMessage()
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

    public function testPopMessageWithFoundMessage()
    {
        $this->messages->expects($this->atLeastOnce())
            ->method('findAndModify')
            ->with(
                ['queue' => 'foo', 'visible' => true],
                ['$set' => ['visible' => false]],
                ['message' => 1],
                ['sort' => ['sentAt' => 1]]
            )
            ->will($this->returnValue(['message' => 'message1', '_id' => '000000000000000000000000']));

        list($message, $receipt) = $this->driver->popMessage('foo');
        $this->assertSame('message1', $message);
        $this->assertSame('000000000000000000000000', $receipt);
    }

    /**
     * @medium
     */
    public function testPopMessageWithMissingMessage()
    {
        $this->messages->expects($this->atLeastOnce())
            ->method('findAndModify')
            ->with(
                ['queue' => 'foo', 'visible' => true],
                ['$set' => ['visible' => false]],
                ['message' => 1],
                ['sort' => ['sentAt' => 1]]
            )
            ->will($this->returnValue(false));

        list($message, $receipt) = $this->driver->popMessage('foo', 1);
        $this->assertNull($message);
        $this->assertNull($receipt);
    }

    public function testAcknowledgeMessage()
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

    public function testPeekQueue()
    {
        $cursor = $this->getMockBuilder('MongoCursor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->messages->expects($this->once())
            ->method('find')
            ->with(['queue' => 'foo', 'visible' => true], ['_id' => 0, 'message' => 1])
            ->will($this->returnValue($cursor));

        $cursor->expects($this->at(0))
            ->method('sort')
            ->with(['sentAt' => 1])
            ->will($this->returnValue($cursor));

        $cursor->expects($this->at(1))
            ->method('limit')
            ->with(20)
            ->will($this->returnValue($cursor));

        /* Rather than mock MongoCursor's iterator interface, take advantage of
         * the final fluent method call and return an ArrayIterator. */
        $cursor->expects($this->at(2))
            ->method('skip')
            ->with(0)
            ->will($this->returnValue(new ArrayIterator([
                ['message' => 'message1'],
                ['message' => 'message2'],
            ])));

        $this->assertSame(['message1', 'message2'], $this->driver->peekQueue('foo'));
    }

    public function testRemoveQueue()
    {
        $this->queues->expects($this->once())
            ->method('remove')
            ->with(['_id' => 'foo']);

        $this->messages->expects($this->once())
            ->method('remove')
            ->with(['queue' => 'foo']);

        $this->driver->removeQueue('foo');
    }

    public function testInfo()
    {
        $this->queues->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue('db.queues'));

        $this->messages->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue('db.messages'));

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
