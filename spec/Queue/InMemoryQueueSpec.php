<?php

namespace spec\Bernard\Queue;

use Bernard\Envelope;
use PhpSpec\ObjectBehavior;

class InMemoryQueueSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('queue-name');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\Queue\InMemoryQueue');
    }

    function it_is_a_queue()
    {
        $this->shouldImplement('Bernard\Queue');
    }

    function it_has_a_name()
    {
        $this->__toString()->shouldReturn('queue-name');
    }

    function it_is_closable(Envelope $envelope)
    {
        $this->close();

        $this->shouldThrow('Bernard\Exception\InvalidOperationException')->duringAcknowledge($envelope);
        $this->shouldThrow('Bernard\Exception\InvalidOperationException')->duringCount();
        $this->shouldThrow('Bernard\Exception\InvalidOperationException')->duringEnqueue($envelope);
        $this->shouldThrow('Bernard\Exception\InvalidOperationException')->duringDequeue();
        $this->shouldThrow('Bernard\Exception\InvalidOperationException')->duringPeek();
    }

    function it_acknowledges_an_envelope(Envelope $envelope)
    {
        $this->acknowledge($envelope);
    }

    function it_enqueues_and_dequeues_in_order(Envelope $first, Envelope $second)
    {
        $this->enqueue($first);
        $this->enqueue($second);

        $this->dequeue()->shouldReturn($first);
        $this->dequeue()->shouldReturn($second);
        $this->dequeue()->shouldReturn(null);
    }

    function it_is_countable(Envelope $envelope)
    {
        $this->shouldImplement('Countable');

        $this->count()->shouldReturn(0);
        $this->enqueue($envelope);
        $this->count()->shouldReturn(1);
        $this->dequeue();
        $this->count()->shouldReturn(0);
    }

    function it_is_peekable(Envelope $first, Envelope $second)
    {
        $this->peek()->shouldReturn(array());

        $this->enqueue($first);
        $this->enqueue($second);

        $this->count()->shouldReturn(2);

        $this->peek()->shouldReturn(array($first, $second));
        $this->peek(1)->shouldReturn(array($second));
        $this->peek(0, 1)->shouldReturn(array($first));
        $this->peek(2)->shouldReturn(array());
    }
}
