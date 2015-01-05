<?php

namespace spec\Bernard\EventListener;

use Bernard\QueueFactory;
use Bernard\Event\RejectEnvelopeEvent;
use Bernard\Envelope;
use Bernard\Queue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FailureSubscriberSpec extends ObjectBehavior
{
    function let(QueueFactory $queueFactory)
    {
        $this->beConstructedWith($queueFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\EventListener\FailureSubscriber');
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

    function it_acknowledges_and_enqueues_a_rejected_message(QueueFactory $queueFactory, RejectEnvelopeEvent $event, Envelope $envelope, Queue $queue)
    {
        $event->getEnvelope()->willReturn($envelope);
        $event->getQueue()->willReturn($queue);
        $queue->acknowledge($envelope)->shouldBeCalled();
        $queueFactory->create('failed')->willReturn($queue);
        $queue->enqueue($envelope)->shouldBeCalled();

        $this->onReject($event);
    }
}
