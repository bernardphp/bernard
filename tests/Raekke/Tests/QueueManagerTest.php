<?php

namespace Raekke\Tests;

use Raekke\QueueManager;

class QueueManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->connection = $this->getMockBuilder('Raekke\Connection')
            ->disableOriginalConstructor()->getMock();

        $this->manager = new QueueManager($this->connection, $this->getMock('Raekke\Serializer\SerializerInterface'));
    }

    public function testItSavesQueueObjects()
    {
        $this->connection->expects($this->once())->method('insert')
            ->with($this->equalTo('queues'), $this->equalTo('queue'));

        $queue = $this->manager->get('queue');

        $this->assertSame($queue, $this->manager->get('queue'));
    }

    public function testRemoveClosesQueue()
    {
        $queue = $this->manager->get('queue');

        $this->assertTrue($this->manager->has('queue'));
        $this->assertTrue($this->manager->remove('queue'));
        $this->assertTrue($queue->isClosed());

        $this->assertFalse($this->manager->remove('queue-nonexistant'));
    }

    public function testItLazyCreatesQueuesAndAttaches()
    {
        $this->connection->expects($this->once())->method('insert')->with($this->equalTo('queues'), $this->equalTo('queue'));

        $this->assertInstanceOf('Raekke\Queue\Queue', $this->manager->get('queue'));
        $this->assertSame($this->manager, $this->manager->get('queue')->getManager());
    }

    public function testItHaveASerializer()
    {
        $this->assertInstanceOf('Raekke\Serializer\SerializerInterface', $this->manager->getSerializer());

        $manager = new QueueManager($this->connection,
            $serializer = $this->getMock('Raekke\Serializer\SerializerInterface'));

        $this->assertSame($serializer, $manager->getSerializer());
    }

    public function testArrayAccess()
    {
        $queue = $this->manager->get('queue');
        $this->assertSame($queue, $this->manager['queue']);

        $this->assertFalse(isset($this->manager['queue2']));
        $this->assertTrue(isset($this->manager['queue']));

        unset($this->manager['queue']);

        $this->assertTrue($queue->isClosed());

        try {
            $this->manager['queue3'] = 'something';

            $this->fail('Setting on QueueManager is expected to raise an exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf('BadMethodCallException', $e);
        }
    }

    public function testItsCountable()
    {
        $this->connection->expects($this->once())->method('all')->with($this->equalTo('queues'))
            ->will($this->returnValue(array('failed', 'something', 'queue-ness')));

        $this->assertCount(3, $this->manager);
    }

    public function testItGetsAllQueues()
    {
        $this->connection->expects($this->once())->method('all')->with($this->equalTo('queues'))
            ->will($this->returnValue(array('queue1', 'queue2')));

        $all = $this->manager->all();

        $this->assertInstanceOf('Raekke\Util\ArrayCollection', $all);
        $this->assertCount(2, $all);
    }
}
