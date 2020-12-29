<?php

namespace Bernard\Tests\Driver\IronMQ;

use Bernard\Driver\IronMQ\Driver;
use IronMQ\IronMQ;
use Bernard\Driver\AbstractPrefetchDriver;

class DriverTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $ironmq;

    /** @var Driver */
    private $driver;

    public function setUp(): void
    {
        $this->ironmq = $this->getMockBuilder(IronMQ::class)
            ->setMethods([
                'getQueue',
                'getQueues',
                'peekMessages',
                'deleteQueue',
                'postMessage',
                'getMessages',
                'deleteMessage',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->driver = new Driver($this->ironmq);
    }

    public function testItExposesInfo()
    {
        $driver = new Driver($this->ironmq, 10);

        $this->assertEquals(['prefetch' => 10], $driver->info());
        $this->assertEquals(['prefetch' => 2], $this->driver->info());
    }

    public function testItImplementsDriverInterface()
    {
        $this->assertInstanceOf(AbstractPrefetchDriver::class, $this->driver);
    }

    public function testItCountsNumberOfMessagesInQueue()
    {
        $this->ironmq->expects($this->at(0))->method('getQueue')
            ->with($this->equalTo('send-newsletter'))->will($this->returnValue((object) ['size' => 4]));

        $this->ironmq->expects($this->at(1))->method('getQueue')
            ->with($this->equalTo('non-existant'))->will($this->returnValue(null));

        $this->assertEquals(4, $this->driver->countMessages('send-newsletter'));
        $this->assertEquals(null, $this->driver->countMessages('non-existant'));
    }

    public function testItListQueues()
    {
        $ironmqQueues = [
            (object) ['name' => 'failed'],
            (object) ['name' => 'queue1'],
        ];

        $this->ironmq->expects($this->once())->method('getQueues')
            ->will($this->returnValue($ironmqQueues));

        $this->assertEquals(['failed', 'queue1'], $this->driver->listQueues());
    }

    public function testAcknowledgeMessage()
    {
        $this->ironmq->expects($this->once())->method('deleteMessage')
            ->with($this->equalTo('my-queue'), $this->equalTo('receipt1'));

        $this->driver->acknowledgeMessage('my-queue', 'receipt1');
    }

    public function testItPeeksInAQueue()
    {
        $ironmqMessages = [
            (object) ['body' => 'message1'],
        ];

        $this->ironmq->expects($this->at(0))->method('peekMessages')
            ->with($this->equalTo('my-queue'), $this->equalTo(10))->will($this->returnValue($ironmqMessages));
        $this->ironmq->expects($this->at(1))->method('peekMessages')
            ->with($this->equalTo('my-queue2'), $this->equalTo(20))->will($this->returnValue(null));

        $this->assertEquals(['message1'], $this->driver->peekQueue('my-queue', 10, 10));
        $this->assertEquals([], $this->driver->peekQueue('my-queue2'));
    }

    public function testItRemovesAQueue()
    {
        $this->ironmq
            ->expects($this->once())
            ->method('deleteQueue')
            ->with($this->equalTo('my-queue'));

        $this->driver->removeQueue('my-queue');
    }

    public function testItPushesMessages()
    {
        $this->ironmq
            ->expects($this->once())
            ->method('postMessage')
            ->with($this->equalTo('my-queue'), $this->equalTo('This is a message'));

        $this->driver->pushMessage('my-queue', 'This is a message');
    }

    public function testItPrefetchesMessages()
    {
        $ironmqMessages = [
            (object) ['body' => 'message1', 'id' => 1],
            (object) ['body' => 'message2', 'id' => 2],
        ];

        $this->ironmq->expects($this->once())->method('getMessages')
            ->with($this->equalTo('send-newsletter'), $this->equalTo(2))
            ->will($this->returnValue($ironmqMessages));

        $this->assertEquals(['message1', 1], $this->driver->popMessage('send-newsletter'));
        $this->assertEquals(['message2', 2], $this->driver->popMessage('send-newsletter'));
    }

    public function testItPopMessages()
    {
        $this->ironmq
            ->expects($this->at(0))
            ->method('getMessages')
            ->with($this->equalTo('my-queue1'), $this->equalTo(2), $this->equalTo(60))
            ->will($this->returnValue([
                (object) ['body' => 'message1', 'id' => 1],
            ]));
        $this->ironmq
            ->expects($this->at(1))
            ->method('getMessages')
            ->with($this->equalTo('my-queue2'), $this->equalTo(2), $this->equalTo(60))
            ->will($this->returnValue([
                (object) ['body' => 'message2', 'id' => 2],
            ]));
        $this->ironmq
            ->expects($this->at(1))
            ->method('getMessages')
            ->with($this->equalTo('my-queue2'), $this->equalTo(2), $this->equalTo(60))
            ->will($this->returnValue(null));

        $this->assertEquals(['message1', 1], $this->driver->popMessage('my-queue1'));
        $this->assertEquals(['message2', 2], $this->driver->popMessage('my-queue2'));
        $this->assertEquals([null, null], $this->driver->popMessage('my-queue2', 0.01));
    }
}
