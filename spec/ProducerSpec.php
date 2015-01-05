<?php

namespace spec\Bernard;

use Bernard\QueueFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Bernard\Queue;
use Bernard\Message;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProducerSpec extends ObjectBehavior
{
    function let(QueueFactory $queueFactory, EventDispatcherInterface $dispatcher)
    {
        $this->beConstructedWith($queueFactory, $dispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\Producer');
    }

    function it_produces_a_message_to_a_specific_queue(QueueFactory $queueFactory, Queue $queue, Message $message, EventDispatcherInterface $dispatcher)
    {
        $message->getName()->willReturn('Import');
        $queueFactory->create('anything-else')->willReturn($queue);
        $queue->enqueue(Argument::type('Bernard\Envelope'))->shouldBeCalled();
        $dispatcher->dispatch('bernard.produce', Argument::type('Bernard\Event\EnvelopeEvent'));

        $this->produce($message, 'anything-else');
    }
}
