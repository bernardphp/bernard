<?php

namespace spec\Bernard\Event;

use Bernard\Envelope;
use Bernard\Queue;
use PhpSpec\ObjectBehavior;

class EnvelopeEventSpec extends ObjectBehavior
{
    function let(Envelope $envelope, Queue $queue)
    {
        $this->beConstructedWith($envelope, $queue);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\Event\EnvelopeEvent');
    }

    function it_is_an_event()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\Event');
    }

    function it_has_an_envelope(Envelope $envelope)
    {
        $this->getEnvelope()->shouldReturn($envelope);
    }

    function it_has_a_queue(Queue $queue)
    {
        $this->getQueue()->shouldReturn($queue);
    }
}
