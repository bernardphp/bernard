<?php

namespace Bernard\Tests\QueueFactory;

use Bernard\QueueFactory\PersistentFactory;

class PersistentFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->connection = $this->getMockBuilder('Bernard\Driver')
            ->disableOriginalConstructor()->getMock();

        $this->factory = new PersistentFactory($this->connection, $this->createMock('Bernard\Serializer'));
    }

    public function testImplementsQueueFactory()
    {
        $this->assertInstanceOf('Bernard\QueueFactory', $this->factory);
    }

    public function testItSavesQueueObjects()
    {
        $this->connection->expects($this->once())->method('createQueue')
            ->with($this->equalTo('send-newsletter'));

        $queue = $this->factory->create('send-newsletter');

        $this->assertSame($queue, $this->factory->create('send-newsletter'));
    }

    public function testRemoveClosesQueue()
    {
        $this->expectException(\Bernard\Exception\InvalidOperationException::class);

        $this->connection->expects($this->once())->method('listQueues')
            ->will($this->returnValue([]));

        $queue = $this->factory->create('send-newsletter');

        $this->assertTrue($this->factory->exists('send-newsletter'));

        $this->factory->remove('send-newsletter');
        $this->assertCount(0, $this->factory);

        $queue->peek(0, 1);
    }

    public function testItLazyCreatesQueuesAndAttaches()
    {
        $this->connection->expects($this->once())->method('createQueue')->with($this->equalTo('send-newsletter'));

        $this->assertInstanceOf('Bernard\Queue\PersistentQueue', $this->factory->create('send-newsletter'));
    }

    public function testItsCountable()
    {
        $this->connection->expects($this->once())->method('listQueues')
            ->will($this->returnValue(['failed', 'something', 'queue-ness']));

        $this->assertCount(3, $this->factory);
    }

    public function testItGetsAllQueues()
    {
        $this->connection->expects($this->once())->method('listQueues')
            ->will($this->returnValue(['queue1', 'queue2']));

        $all = $this->factory->all();

        $this->assertCount(2, $all);
        $this->assertContainsOnly('Bernard\Queue\PersistentQueue', $all);
    }
}
