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
                'getMessage'
            ))
            ->disableOriginalConstructor()
            ->getMock();

        $this->connection = new IronMqDriver($this->ironmq);
    }

    public function testItImplementsDriverInterface()
    {
        $this->assertInstanceOf('Bernard\Driver', $this->connection);
    }

    public function testItCountsNumberOfMessagesInQueue()
    {
        $this->ironmq
            ->expects($this->once())
            ->method('getQueue')
            ->with($this->equalTo('send-newsletter'))
            ->will($this->returnValue((object)array('size' => 4)));
        $this->assertEquals(4, $this->connection->countMessages('send-newsletter'));
    }

    public function testItGetsAllKeys()
    {
        $this->ironmq
            ->expects($this->once())
            ->method('getQueues')
            ->will($this->returnValue(array(
                (object)array('name' => 'failed'),
                (object)array('name' => 'queue1')
            )));
        $this->assertEquals(array('failed', 'queue1'), $this->connection->listQueues());
    }

    public function testItPeeksInAQueue()
    {
        $this->ironmq
            ->expects($this->once())
            ->method('peekMessages')
            ->with($this->equalTo('my-queue'), $this->equalTo(10))
            ->will($this->returnValue(array(
                (object)array('body' => 'message1')
            )));

        $this->assertEquals(array('message1'), $this->connection->peekQueue('my-queue', 10, 10));
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

    public function testItPopMessages()
    {
        $this->ironmq
            ->expects($this->at(0))
            ->method('getMessage')
            ->with($this->equalTo('my-queue1'))
            ->will($this->returnValue((object)(array('body' => 'message1', 'id' => 1))));
        $this->ironmq
            ->expects($this->at(1))
            ->method('getMessage')
            ->with($this->equalTo('my-queue2'), $this->equalTo(30))
            ->will($this->returnValue((object)(array('body' => 'message2', 'id' => 2))));

        $this->assertEquals(array('message1', 1), $this->connection->popMessage('my-queue1'));
        $this->assertEquals(array('message2', 2), $this->connection->popMessage('my-queue2', 30));
    }
}
