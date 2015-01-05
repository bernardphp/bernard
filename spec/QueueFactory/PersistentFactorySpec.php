<?php

namespace spec\Bernard\QueueFactory;

use Bernard\Driver;
use Bernard\Serializer;
use PhpSpec\ObjectBehavior;

class PersistentFactorySpec extends ObjectBehavior
{
    function let(Driver $driver, Serializer $serializer)
    {
        $this->beConstructedWith($driver, $serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\QueueFactory\PersistentFactory');
    }

    function it_is_a_queue_factory()
    {
        $this->shouldImplement('Bernard\QueueFactory');
    }

    function it_creates_queue_objects()
    {
        $this->create('queue1')->shouldReturnAnInstanceOf('Bernard\Queue\PersistentQueue');
    }

    function it_caches_queue_object()
    {
        $queue = $this->create('queue2');

        $this->create('queue2')->shouldReturn($queue);
        $queue->__toString()->shouldReturn('queue2');
    }

    function it_is_countable(Driver $driver)
    {
        $this->shouldImplement('Countable');

        $driver->listQueues()->willReturn(array('Import', 'Export'));

        $this->count()->shouldReturn(2);
    }

    function it_creates_all_known_queues(Driver $driver)
    {
        $driver->createQueue('Import')->willReturn();
        $driver->createQueue('Export')->willReturn();

        $driver->listQueues()->willReturn(array('Import', 'Export'));

        $this->all()
            ->shouldReturn(array('Import' => $this->create('Import'), 'Export' => $this->create('Export')));
    }

    function it_checks_if_queue_exists(Driver $driver)
    {
        $driver->listQueues()->willReturn(array('Import', 'Export'));

        $this->exists('SendNewsletter')->shouldReturn(false);
        $this->exists('Import')->shouldReturn(true);
    }

    function it_calls_close_when_removing($driver)
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
