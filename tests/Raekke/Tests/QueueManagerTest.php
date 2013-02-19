<?php

namespace Raekke\Tests;

use Raekke\QueueManager;

class QueueManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->connection = $this->getMockBuilder('Raekke\Connection')
            ->disableOriginalConstructor()->getMock();

        $this->manager = new QueueManager($this->connection);
    }

    public function testItPushesMessages()
    {
        $message = $this->getMock('Raekke\Message\MessageInterface');
        $message->expects($this->once())->method('getQueue')->will($this->returnValue('queue'));

        $queue = $this->getMockBuilder('Raekke\Queue\Queue')->disableOriginalConstructor()
            ->getMock();

        $queue->expects($this->once())->method('push')->with($this->equalTo($message));

        $manager = $this->getMockBuilder('Raekke\QueueManager')->disableOriginalConstructor()
            ->setMethods(array('get'))->getMock();

        $manager->expects($this->once())->method('get')->with($this->equalTo('queue'))
            ->will($this->returnValue($queue));

        $manager->push($message);
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

    public function testItHaveASerializerAndEventDispatcher()
    {
        $this->assertInstanceOf('Raekke\Serializer\Serializer', $this->manager->getSerializer());
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventDispatcherInterface', $this->manager->getDispatcher());

        $manager = new QueueManager($this->connection,
            $serializer = $this->getMock('Raekke\Serializer\Serializer'),
            $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface'));

        $this->assertSame($serializer, $manager->getSerializer());
        $this->assertSame($dispatcher, $manager->getDispatcher());
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
        $this->assertInstanceOf('Countable', $this->manager);
        $this->assertInstanceOf('IteratorAggregate', $this->manager);

        $this->connection->expects($this->once())->method('count')->with($this->equalTo('queues'))
            ->will($this->returnValue(4));

        $this->assertCount(4, $this->manager);
    }

    public function testItGetsAllQueues()
    {
        $this->connection->expects($this->exactly(2))->method('all')->with($this->equalTo('queues'))
            ->will($this->returnValue(array('queue1', 'queue2')));

        $all = $this->manager->all();

        $this->assertInstanceOf('Raekke\Util\ArrayCollection', $all);
        $this->assertCount(3, $all);
        $this->assertTrue($all->containsKey('failed'));

        $this->assertInstanceOf('IteratorAggregate', $this->manager);
        $this->assertEquals($all->getIterator(), $this->manager->getIterator());
    }
}
