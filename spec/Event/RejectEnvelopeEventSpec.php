<?php

namespace spec\Bernard\Event;

use Bernard\Envelope;
use Bernard\Queue;
use PhpSpec\ObjectBehavior;

class RejectEnvelopeEventSpec extends ObjectBehavior
{
    function let(Envelope $envelope, Queue $queue, \Exception $e)
    {
        $this->beConstructedWith($envelope, $queue, $e);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\Event\RejectEnvelopeEvent');
    }

    function it_is_an_event()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\Event');
    }

    function it_has_an_exception(\Exception $e)
    {
        $this->getException()->shouldReturn($e);
    }
}
