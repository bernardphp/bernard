<?php

namespace Bernard\Tests;

use Bernard\QueueFactory\InMemoryFactory;

class InMemoryFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->factory = new InMemoryFactory();
    }

    public function testImplementsQueueFactory()
    {
        $this->assertInstanceOf('Bernard\QueueFactory', $this->factory);
    }

    /**
     * @expectedException \Bernard\Exception\InvalidOperationException
     */
    public function testRemoveClosesQueue()
    {
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

        $this->assertInstanceOf('Bernard\Queue\InMemoryQueue', $queue1);
        $this->assertSame($queue1, $this->factory->create('queue1'));
        $this->assertCount(2, $all);
        $this->assertContainsOnly('Bernard\Queue\InMemoryQueue', $all);
        $this->assertSame(compact('queue1', 'queue2'), $all);
    }
}
