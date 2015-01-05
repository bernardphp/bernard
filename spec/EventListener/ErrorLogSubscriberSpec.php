<?php

namespace spec\Bernard\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErrorLogSubscriberSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\EventListener\ErrorLogSubscriber');
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_has_subscribed_events()
    {
        $this->getSubscribedEvents()->shouldReturn(array(
            'bernard.reject' => array('onReject'),
        ));
    }
}
