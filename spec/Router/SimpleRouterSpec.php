<?php

namespace spec\Bernard\Router;

use Bernard\Envelope;
use PhpSpec\ObjectBehavior;

class SimpleRouterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\Router\SimpleRouter');
    }

    function it_is_a_router()
    {
        $this->shouldImplement('Bernard\Router');
    }

    function it_maps_to_a_callable(Envelope $envelope)
    {
        $this->add('Import', 'strpos');

        $envelope->getName()->willReturn('Import');
        $this->map($envelope)->shouldReturn('strpos');
    }

    function it_guesses_method_name_for_objects_and_classes(Envelope $envelope)
    {
        $this->add('Export', 'Bernard\Producer');
        $this->add('Email', $this);

        $envelope->getName()->willReturn('Export');
        $this->map($envelope)->shouldReturn(array('Bernard\Producer', 'export'));

        $envelope->getName()->willReturn('Email');
        $this->map($envelope)->shouldReturn(array($this, 'email'));
    }

    function it_takes_mapping_in_constructor(Envelope $envelope)
    {
        $envelope->getName()->willReturn('Import');

        $this->beConstructedWith(array('Import' => 'var_dump'));

        $this->map($envelope)->shouldReturn('var_dump');
    }

    function it_throws_an_exception_when_it_does_not_accept_the_receiver()
    {
        $this->shouldThrow('InvalidArgumentException')->duringAdd('Import', 1337);
    }

    function it_throws_an_exception_when_receiver_cannot_be_mapped(Envelope $envelope)
    {
        $envelope->getName()->willReturn('Import');

        $this->shouldThrow('Bernard\Exception\ReceiverNotFoundException')->duringMap($envelope);
    }
}
