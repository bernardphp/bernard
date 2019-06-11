<?php

namespace Bernard\Tests\Driver\NewMongoDB;

use Bernard\Driver\NewMongoDB\Driver;
use ArrayIterator;
use MongoDB\Collection;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectID;


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
        if (!class_exists('\MongoDB\Collection')) {
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
            ->method('updateOne')
            ->with(['_id' => 'foo'], ['$set' => ['_id' => 'foo']], ['upsert' => true]);

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
            ->method('insertOne')
            ->with($this->callback(function ($data) {
                return $data['queue'] === 'foo' &&
                    $data['message'] === 'message1' &&
                    $data['sentAt'] instanceof UTCDateTime &&
                    $data['visible'] === true;
            }));

        $this->driver->pushMessage('foo', 'message1');
    }

    public function testPopMessageWithFoundMessage()
    {
        $this->messages->expects($this->atLeastOnce())
            ->method('findOneAndUpdate')
            ->with(
                ['queue' => 'foo', 'visible' => true],
                ['$set' => ['visible' => false]],
                ['sort' => ['sentAt' => 1], 'projection' => ['message' => 1]]
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
            ->method('findOneAndUpdate')
            ->with(
                ['queue' => 'foo', 'visible' => true],
                ['$set' => ['visible' => false]],
                ['sort' => ['sentAt' => 1], 'projection' => ['message' => 1]]
            )
            ->will($this->returnValue(false));

        list($message, $receipt) = $this->driver->popMessage('foo', 1);
        $this->assertNull($message);
        $this->assertNull($receipt);
    }

    public function testAcknowledgeMessage()
    {
        $this->messages->expects($this->once())
            ->method('deleteOne')
            ->with($this->callback(function ($query) {
                return $query['_id'] instanceof ObjectID &&
                    (string) $query['_id'] === '000000000000000000000000' &&
                    $query['queue'] === 'foo';
            }));

        $this->driver->acknowledgeMessage('foo', '000000000000000000000000');
    }

    public function testPeekQueue()
    {
        $ursor = $this->getMockBuilder('CursorStub')
            ->disableOriginalConstructor()
            ->getMock();

        $cursor = new CursorStub();

        $this->messages->expects($this->once())
            ->method('find')
            ->with(['queue' => 'foo', 'visible' => true],
                   [
                       'projection' => ['_id' => 0, 'message' => 1],
                       'sort' => ['sentAt' => 1],
                       'limit' => 20,
                       'skip' => 0
                   ])
            ->will($this->returnValue($cursor));

        $this->assertSame(['message1', 'message2'], $this->driver->peekQueue('foo'));
    }

    public function testRemoveQueue()
    {
        $this->queues->expects($this->once())
            ->method('deleteOne')
            ->with(['_id' => 'foo']);

        $this->messages->expects($this->once())
            ->method('deleteMany')
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
        return $this->getMockBuilder('\MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
