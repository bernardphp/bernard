<?php

namespace Raekke\Tests;

use Raekke\QueueFactory\PersistentFactory;

class PersistentFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->connection = $this->getMockBuilder('Raekke\Connection')
            ->disableOriginalConstructor()->getMock();

        $this->factory = new PersistentFactory($this->connection, $this->getMock('Raekke\Serializer'));
    }

    public function testImplementsQueueFactory()
    {
        $this->assertInstanceOf('Raekke\QueueFactory', $this->factory);
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
        $queue = $this->factory->create('queue');

        $this->assertTrue($this->factory->exists('queue'));
        $this->assertCount(0, $this->factory);

        $this->assertTrue($this->factory->remove('queue'));
        $this->assertCount(0, $this->factory);

        $this->assertFalse($this->factory->remove('queue-nonexistant'));

    }

    public function testItLazyCreatesQueuesAndAttaches()
    {
        $this->connection->expects($this->once())->method('insert')->with($this->equalTo('queues'), $this->equalTo('queue'));

        $this->assertInstanceOf('Raekke\Queue\PersistentQueue', $this->factory->create('queue'));
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
        $this->assertContainsOnly('Raekke\Queue\PersistentQueue', $all);
    }
}
