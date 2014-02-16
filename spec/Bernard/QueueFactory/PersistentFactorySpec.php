<?php

namespace spec\Bernard\QueueFactory;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PersistentFactorySpec extends ObjectBehavior
{
    /**
     * @param Bernard\Driver $driver
     * @param Bernard\Serializer $serializer
     */
    function let($driver, $serializer)
    {
        $this->beConstructedWith($driver, $serializer);
    }

    function it_creates_queue_objects()
    {
        $this->create('queue1')->shouldReturnAnInstanceOf('Bernard\Queue\PersistentQueue');
    }

    function its_countable($driver)
    {
        $driver->listQueues()->willReturn(array('Import', 'Export'));

        $this->count()->shouldReturn(2);
    }

    function it_can_create_all_known_queues($driver)
    {
        $driver->createQueue('Import')->willReturn();
        $driver->createQueue('Export')->willReturn();

        $driver->listQueues()->willReturn(array('Import', 'Export'));

        $this->all()
            ->shouldReturn(array('Import' => $this->create('Import'), 'Export' => $this->create('Export')));
    }

    function it_knows_if_queue_exists($driver)
    {
        $driver->listQueues()->willReturn(array('Import', 'Export'));

        $this->exists('SendNewsletter')->shouldReturn(false);
        $this->exists('Import')->shouldReturn(true);
    }

    function it_caches_queue_object()
    {
        $queue = $this->create('queue2');

        $this->create('queue2')->shouldReturn($queue);
        $queue->__toString()->shouldReturn('queue2');
    }

    function it_closes_queues_when_removing($driver)
    {
        $driver->createQueue('Import');

        $queue = $this->create('Import');

        $this->remove('Import');

        // This is not the prettiest because it have to know the object when its closed.
        // But $queue = $this->create('Import');$queue->close()->shouldBeCalled() does
        // not work.
        $queue->shouldThrow('Bernard\Exception\InvalidOperationException')->duringPeek();
    }
}
