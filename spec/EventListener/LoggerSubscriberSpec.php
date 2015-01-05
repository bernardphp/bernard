<?php

namespace spec\Bernard\EventListener;

use Psr\Log\LoggerInterface;
use Bernard\Event\EnvelopeEvent;
use Bernard\Event\RejectEnvelopeEvent;
use Bernard\Envelope;
use Bernard\Queue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LoggerSubscriberSpec extends ObjectBehavior
{
    function let(LoggerInterface $logger)
    {
        $this->beConstructedWith($logger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\EventListener\LoggerSubscriber');
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_has_subscribed_events()
    {
        $this->getSubscribedEvents()->shouldReturn(array(
            'bernard.produce' => array('onProduce'),
            'bernard.invoke' => array('onInvoke'),
            'bernard.reject' => array('onReject'),
        ));
    }

    function it_logs_when_a_message_is_produced(LoggerInterface $logger, EnvelopeEvent $event, Envelope $envelope, Queue $queue)
    {
        $logger->info(Argument::type('string'), array('envelope' => $envelope, 'queue' => $queue))->shouldBeCalled();
        $event->getEnvelope()->willReturn($envelope);
        $event->getQueue()->willReturn($queue);

        $this->onProduce($event);
    }

    function it_logs_when_a_message_is_invoked(LoggerInterface $logger, EnvelopeEvent $event, Envelope $envelope)
    {
        $logger->info(Argument::type('string'), array('envelope' => $envelope))->shouldBeCalled();
        $event->getEnvelope()->willReturn($envelope);

        $this->onInvoke($event);
    }

    function it_logs_when_a_message_is_rejected(LoggerInterface $logger, RejectEnvelopeEvent $event, Envelope $envelope, \Exception $e)
    {
        $logger->error(Argument::type('string'), array('envelope' => $envelope, 'exception' => $e))->shouldBeCalled();
        $event->getEnvelope()->willReturn($envelope);
        $event->getException()->willReturn($e);

        $this->onReject($event);
    }
}
