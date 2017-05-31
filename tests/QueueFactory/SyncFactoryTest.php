<?php

namespace Bernard\Tests;

use Bernard\QueueFactory\SyncFactory;
use Bernard\Router\SimpleRouter;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SyncFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->factory = new SyncFactory(new EventDispatcher, new SimpleRouter);
    }

    public function testImplementsQueueFactory()
    {
        $this->assertInstanceOf('Bernard\QueueFactory', $this->factory);
    }

    public function testRemoveClosesQueue()
    {
        $this->setExpectedException('Bernard\Exception\InvalidOperationException');

        $queue = $this->factory->create('queue');

        $this->assertTrue($this->factory->exists('queue'));
        $this->assertCount(1, $this->factory);

        $this->factory->remove('queue');
        $this->assertCount(0, $this->factory);

        // Trigger close
        $queue->peek(0, 1);
    }

    public function testItCanCreateQueues()
    {
        $this->assertCount(0, $this->factory);

        $queue1 = $this->factory->create('queue1');
        $queue2 = $this->factory->create('queue2');

        $all = $this->factory->all();

        $this->assertInstanceOf('Bernard\Queue\SyncQueue', $queue1);
        $this->assertSame($queue1, $this->factory->create('queue1'));
        $this->assertCount(2, $all);
        $this->assertContainsOnly('Bernard\Queue\SyncQueue', $all);
        $this->assertSame(compact('queue1', 'queue2'), $all);
    }
}
