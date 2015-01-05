<?php

namespace spec\Bernard\Queue;

use Bernard\Driver;
use Bernard\Serializer;
use Bernard\Envelope;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PersistentQueueSpec extends ObjectBehavior
{
    function let(Driver $driver, Serializer $serializer)
    {
        $this->beConstructedWith('queue-name', $driver, $serializer);
    }

    function it_is_a_queue()
    {
        $this->shouldHaveType('Bernard\Queue\AbstractQueue');
        $this->shouldImplement('Bernard\Queue');
    }

    function it_has_a_name()
    {
        $this->__toString()->shouldReturn('queue-name');
    }

    function it_only_acknowledges_when_envelope_receipt_is_found(Envelope $envelope, Driver $driver)
    {
        $driver->createQueue('queue-name')->shouldBeCalled();
        $driver->acknowledgeMessage('queue-name', Argument::any())->shouldNotBeCalled();

        $this->acknowledge($envelope);
    }

    function it_is_closable(Envelope $envelope, Driver $driver)
    {
        $driver->createQueue('queue-name')->shouldBeCalled();
        $driver->removeQueue('queue-name')->shouldBeCalled();

        $this->close();

        $this->shouldThrow('Bernard\Exception\InvalidOperationException')->duringAcknowledge($envelope);
        $this->shouldThrow('Bernard\Exception\InvalidOperationException')->duringCount();
        $this->shouldThrow('Bernard\Exception\InvalidOperationException')->duringEnqueue($envelope);
        $this->shouldThrow('Bernard\Exception\InvalidOperationException')->duringDequeue();
        $this->shouldThrow('Bernard\Exception\InvalidOperationException')->duringPeek();
        $this->shouldThrow('Bernard\Exception\InvalidOperationException')->duringRegister();
    }

    function it_serializes_message_when_enqueueing(Envelope $envelope, Driver $driver, Serializer $serializer)
    {
        $driver->createQueue('queue-name')->shouldBeCalled();
        $serializer->serialize($envelope)->willReturn('message1');
        $driver->pushMessage('queue-name', 'message1')->shouldBeCalled();

        $this->enqueue($envelope);
    }

    function it_unserializes_message_when_dequeuing_and_acknowledge_receipt(Envelope $envelope, Driver $driver, Serializer $serializer)
    {
        $serializer->unserialize('message1')->willReturn($envelope);

        $driver->createQueue('queue-name')->shouldBeCalled();
        $driver->popMessage('queue-name')->willReturn(array('message1', 'receipt1'));

        $this->dequeue()->shouldReturn($envelope);

        $driver->popMessage('queue-name')->willReturn(array(null, null));

        $this->dequeue()->shouldReturn(null);

        $driver->acknowledgeMessage('queue-name', 'receipt1')->shouldBeCalled();

        $this->acknowledge($envelope);
    }

    function it_is_countable(Envelope $envelope, Driver $driver)
    {
        $this->shouldImplement('Countable');

        $driver->countMessages('queue-name')->willReturn(10);

        $this->count()->shouldReturn(10);
    }

    function it_is_peekable(Envelope $first, Envelope $second, Driver $driver, Serializer $serializer)
    {
        $driver->peekQueue('queue-name', 1, 10)->willReturn(array(
            'message1',
            'message2',
        ));

        $driver->createQueue('queue-name')->shouldBeCalled();

        $serializer->unserialize('message1')->willReturn($first);
        $serializer->unserialize('message2')->willReturn($second);

        $this->peek(1, 10)->shouldReturn(array($first, $second));
    }
}
