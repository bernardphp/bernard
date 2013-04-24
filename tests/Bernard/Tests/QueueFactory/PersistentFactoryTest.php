<?php

namespace Bernard\Tests;

use Bernard\QueueFactory\PersistentFactory;

class PersistentFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->connection = $this->getMockBuilder('Bernard\Connection')
            ->disableOriginalConstructor()->getMock();

        $this->factory = new PersistentFactory($this->connection, $this->getMock('Bernard\Serializer'));
    }

    public function testImplementsQueueFactory()
    {
        $this->assertInstanceOf('Bernard\QueueFactory', $this->factory);
    }

    public function testItSavesQueueObjects()
    {
        $this->connection->expects($this->once())->method('insert')
            ->with($this->equalTo('queues'), $this->equalTo('queue'));

        $queue = $this->factory->create('queue');

        $this->assertSame($queue, $this->factory->create('queue'));
    }

    public function testRemoveClosesQueue()
    {
        $this->setExpectedException('Bernard\Exception\InvalidOperationException');

        $queue = $this->factory->create('queue');

        $this->assertTrue($this->factory->exists('queue'));
        $this->assertCount(0, $this->factory);

        $this->factory->remove('queue');
        $this->assertCount(0, $this->factory);

        $queue->slice(0, 1);
    }

    public function testItLazyCreatesQueuesAndAttaches()
    {
        $this->connection->expects($this->once())->method('insert')->with($this->equalTo('queues'), $this->equalTo('queue'));

        $this->assertInstanceOf('Bernard\Queue\PersistentQueue', $this->factory->create('queue'));
    }

    public function testItsCountable()
    {
        $this->connection->expects($this->once())->method('all')->with($this->equalTo('queues'))
            ->will($this->returnValue(array('failed', 'something', 'queue-ness')));

        $this->assertCount(3, $this->factory);
    }

    public function testItGetsAllQueues()
    {
        $this->connection->expects($this->once())->method('all')->with($this->equalTo('queues'))
            ->will($this->returnValue(array('queue1', 'queue2')));

        $all = $this->factory->all();

        $this->assertCount(2, $all);
        $this->assertContainsOnly('Bernard\Queue\PersistentQueue', $all);
    }

    public function testItCannotGetAQueue()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->factory->get('queue');
    }

    public function testItGetsAQueue()
    {
        $queue = $this->factory->create('queue');

        $this->assertSame($queue, $this->factory->get('queue'));
    }
}
