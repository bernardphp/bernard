<?php

namespace Bernard\Tests\Driver;

use Bernard\Driver\IronMqDriver;

class IronMqDriverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->ironmq = $this->getMockBuilder('IronMQ')
            ->setMethods(array(
                'getQueue',
                'getQueues',
                'peekMessages',
                'deleteQueue',
                'postMessage',
                'getMessages',
                'deleteMessage',
            ))
            ->disableOriginalConstructor()
            ->getMock();

        $this->connection = new IronMqDriver($this->ironmq);
    }

    public function testItImplementsDriverInterface()
    {
        $this->assertInstanceOf('Bernard\Driver\AbstractPrefetchDriver', $this->connection);
    }

    public function testItCountsNumberOfMessagesInQueue()
    {
        $this->ironmq->expects($this->at(0))->method('getQueue')
            ->with($this->equalTo('send-newsletter'))->will($this->returnValue((object) array('size' => 4)));

        $this->ironmq->expects($this->at(1))->method('getQueue')
            ->with($this->equalTo('non-existant'))->will($this->returnValue(null));

        $this->assertEquals(4, $this->connection->countMessages('send-newsletter'));
        $this->assertEquals(null, $this->connection->countMessages('non-existant'));
    }

    public function testItListQueues()
    {
        $ironmqQueues = array(
            (object) array('name' => 'failed'),
            (object) array('name' => 'queue1'),
        );

        $this->ironmq->expects($this->once())->method('getQueues')
            ->will($this->returnValue($ironmqQueues));

        $this->assertEquals(array('failed', 'queue1'), $this->connection->listQueues());
    }

    public function testAcknowledgeMessage()
    {
        $this->ironmq->expects($this->once())->method('deleteMessage')
            ->with($this->equalTo('my-queue'), $this->equalTo('receipt1'));

        $this->connection->acknowledgeMessage('my-queue', 'receipt1');
    }

    public function testItPeeksInAQueue()
    {
        $ironmqMessages = array(
            (object) array('body' => 'message1'),
        );

        $this->ironmq->expects($this->at(0))->method('peekMessages')
            ->with($this->equalTo('my-queue'), $this->equalTo(10))->will($this->returnValue($ironmqMessages));
        $this->ironmq->expects($this->at(1))->method('peekMessages')
            ->with($this->equalTo('my-queue2'), $this->equalTo(20))->will($this->returnValue(null));

        $this->assertEquals(array('message1'), $this->connection->peekQueue('my-queue', 10, 10));
        $this->assertEquals(array(), $this->connection->peekQueue('my-queue2'));
    }

    public function testItRemovesAQueue()
    {
        $this->ironmq
            ->expects($this->once())
            ->method('deleteQueue')
            ->with($this->equalTo('my-queue'));

        $this->connection->removeQueue('my-queue');
    }

    public function testItPushesMessages()
    {
        $this->ironmq
            ->expects($this->once())
            ->method('postMessage')
            ->with($this->equalTo('my-queue'), $this->equalTo('This is a message'));

        $this->connection->pushMessage('my-queue', 'This is a message');
    }

    public function testItPrefetchesMessages()
    {
        $ironmqMessages = array(
            (object) array('body' => 'message1', 'id' => 1),
            (object) array('body' => 'message2', 'id' => 2),
        );

        $this->ironmq->expects($this->once())->method('getMessages')
            ->with($this->equalTo('send-newsletter'), $this->equalTo(2))
            ->will($this->returnValue($ironmqMessages));

        $this->assertEquals(array('message1', 1), $this->connection->popMessage('send-newsletter'));
        $this->assertEquals(array('message2', 2), $this->connection->popMessage('send-newsletter'));
    }

    public function testItPopMessages()
    {
        $this->ironmq
            ->expects($this->at(0))
            ->method('getMessages')
            ->with($this->equalTo('my-queue1'), $this->equalTo(2), $this->equalTo(5))
            ->will($this->returnValue(array(
                (object) array('body' => 'message1', 'id' => 1),
            )));
        $this->ironmq
            ->expects($this->at(1))
            ->method('getMessages')
            ->with($this->equalTo('my-queue2'), $this->equalTo(2), $this->equalTo(30))
            ->will($this->returnValue(array(
                (object) array('body' => 'message2', 'id' => 2),
            )));
        $this->ironmq
            ->expects($this->at(1))
            ->method('getMessages')
            ->with($this->equalTo('my-queue2'), $this->equalTo(2), $this->equalTo(30))
            ->will($this->returnValue(null));

        $this->assertEquals(array('message1', 1), $this->connection->popMessage('my-queue1'));
        $this->assertEquals(array('message2', 2), $this->connection->popMessage('my-queue2', 30));
        $this->assertEquals(array(null, null), $this->connection->popMessage('my-queue2'));
    }
}
