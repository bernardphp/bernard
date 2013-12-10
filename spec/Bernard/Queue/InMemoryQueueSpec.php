<?php

namespace spec\Bernard\Queue;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InMemoryQueueSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('queue-name');
    }

    function it_implements_queue_interface()
    {
        $this->shouldHaveType('Bernard\Queue');
        $this->shouldHaveType('Bernard\Queue\AbstractQueue');
    }

    function it_have_a_name()
    {
        $this->__toString()->shouldReturn('queue-name');
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_throws_exception_when_closed($envelope)
    {
        $this->close();

        $this->shouldThrow('Bernard\Exception\InvalidOperationException')->duringAcknowledge($envelope);
        $this->shouldThrow('Bernard\Exception\InvalidOperationException')->duringCount();
        $this->shouldThrow('Bernard\Exception\InvalidOperationException')->duringEnqueue($envelope);
        $this->shouldThrow('Bernard\Exception\InvalidOperationException')->duringDequeue();
        $this->shouldThrow('Bernard\Exception\InvalidOperationException')->duringPeek();
    }

    /**
     * @param Bernard\Envelope $first
     * @param Bernard\Envelope $second
     */
    function it_enqueues_and_dequeues_in_order($first, $second)
    {
        $this->enqueue($first);
        $this->enqueue($second);

        $this->dequeue()->shouldReturn($first);
        $this->dequeue()->shouldReturn($second);
        $this->dequeue()->shouldReturn(null);
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_is_countable($envelope)
    {
        $this->shouldHaveType('Countable');

        $this->count()->shouldReturn(0);
        $this->enqueue($envelope);
        $this->count()->shouldReturn(1);
        $this->dequeue();
        $this->count()->shouldReturn(0);
    }

    /**
     * @param Bernard\Envelope $first
     * @param Bernard\Envelope $second
     */
    function it_is_peekable($first, $second)
    {
        $this->peek()->shouldReturn(array());

        $this->enqueue($first);
        $this->enqueue($second);

        $this->count()->shouldReturn(2);

        $this->peek()->shouldReturn(array($first, $second));
        $this->peek(2)->shouldReturn(array($second));
        $this->peek(1, 1)->shouldReturn(array($first));
    }
}
