<?php

namespace spec\Bernard\Router;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SimpleRouterSpec extends ObjectBehavior
{
    function its_a_router()
    {
        $this->shouldHaveType('Bernard\Router');
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_maps_to_callable($envelope)
    {
        $this->add('Import', 'strpos');

        $envelope->getName()->willReturn('Import');
        $this->map($envelope)->shouldReturn('strpos');
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_guess_method_name_for_objects_and_classes($envelope)
    {
        $this->add('Export', 'Bernard\Producer');
        $this->add('Email', $this);

        $envelope->getName()->willReturn('Export');
        $this->map($envelope)->shouldReturn(array('Bernard\Producer', 'export'));

        $envelope->getName()->willReturn('Email');
        $this->map($envelope)->shouldReturn(array($this, 'email'));
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_takes_mapping_in_constructor($envelope)
    {
        $envelope->getName()->willReturn('Import');

        $this->beConstructedWith(array('Import' => 'var_dump'));

        $this->map($envelope)->shouldReturn('var_dump');
    }

    function it_throws_exception_when_it_dosent_accept_the_receiver()
    {
        $this->shouldThrow('InvalidArgumentException')->duringAdd('Import', 1337);
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_throws_exception_when_receiver_cannot_be_mapped($envelope)
    {
        $envelope->getName()->willReturn('Import');

        $this->shouldThrow('Bernard\Exception\ReceiverNotFoundException')->duringMap($envelope);
    }
}
