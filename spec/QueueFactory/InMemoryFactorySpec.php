<?php

namespace spec\Bernard\QueueFactory;

use PhpSpec\ObjectBehavior;

class InMemoryFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\QueueFactory\InMemoryFactory');
    }

    function it_is_a_queue_factory()
    {
        $this->shouldImplement('Bernard\QueueFactory');
    }

    function it_creates_queue_objects()
    {
        $this->create('queue1')->shouldHaveType('Bernard\Queue\InMemoryQueue');
    }

    function it_caches_queue_object()
    {
        $queue = $this->create('queue2');

        $this->create('queue2')->shouldReturn($queue);
        $queue->__toString()->shouldReturn('queue2');
    }

    function its_countable()
    {
        $this->shouldImplement('Countable');

        $this->count()->shouldReturn(0);

        $this->create('Import');

        $this->count()->shouldReturn(1);
    }

    function it_returns_all_queues()
    {
        $import = $this->create('Import');
        $newsletter = $this->create('SendNewsletter');

        $this->all()->shouldReturn(array('Import' => $import, 'SendNewsletter' => $newsletter));
    }

    function it_checks_if_queue_exists()
    {
        $this->create('Import');

        $this->exists('Import')->shouldReturn(true);
        $this->exists('SendNewsletter')->shouldReturn(false);
    }

    function it_calls_close_when_removing()
    {
        $queue = $this->create('Import');

        $this->remove('Import');

        // This is not the prettiest because it have to know the object when its closed.
        // But $queue = $this->create('Import');$queue->close()->shouldBeCalled() does
        // not work.
        $queue->shouldThrow('Bernard\Exception\InvalidOperationException')->duringPeek();
    }
}
