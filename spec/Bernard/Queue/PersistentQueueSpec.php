<?php

namespace spec\Bernard\Queue;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PersistentQueueSpec extends ObjectBehavior
{
    /**
     * @param Bernard\Driver $driver
     * @param Bernard\Serializer $serializer
     */
    function let($driver, $serializer)
    {
        $this->beConstructedWith('queue-name', $driver, $serializer);
    }

    function it_implements_queue_interface()
    {
        $this->shouldHaveType('Bernard\Queue');
        $this->shouldHaveType('Bernard\Queue\AbstractQueue');
    }

    function it_has_a_name()
    {
        $this->__toString()->shouldReturn('queue-name');
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_only_acknowledges_when_envelope_receipt_is_found($envelope, $driver)
    {
        $driver->createQueue('queue-name')->shouldBeCalled();
        $driver->acknowledgeMessage('queue-name', Argument::any())->shouldNotBeCalled();

        $this->acknowledge($envelope);
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_is_closable($envelope, $driver)
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

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_serializes_message_when_enqueueing($envelope, $driver, $serializer)
    {
        $serializer->serialize($envelope)->willReturn('message1');
        $driver->pushMessage('queue-name', 'message1');

        $this->enqueue($envelope);
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_deserializes_message_when_dequeuing_and_acknowledge_receipt($envelope, $driver, $serializer)
    {
        $serializer->deserialize('message1')->willReturn($envelope);

        $driver->createQueue('queue-name')->willReturn();
        $driver->popMessage('queue-name')->willReturn(array('message1', 'receipt1'));

        $this->dequeue()->shouldReturn($envelope);

        $driver->popMessage('queue-name')->willReturn(array(null, null));

        $this->dequeue()->shouldReturn(null);

        $driver->acknowledgeMessage('queue-name', 'receipt1')->shouldBeCalled();

        $this->acknowledge($envelope);
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_is_countable($envelope, $driver)
    {
        $this->shouldHaveType('Countable');

        $driver->countMessages('queue-name')->shouldBeCalled()->willReturn(10);

        $this->count()->shouldReturn(10);
    }

    /**
     * @param Bernard\Envelope $first
     * @param Bernard\Envelope $second
     */
    function it_is_peekable($first, $second, $driver, $serializer)
    {
        $driver->peekQueue('queue-name', 1, 10)->willReturn(array(
            'message1',
            'message2',
        ));

        $driver->createQueue('queue-name')->shouldBeCalled();

        $serializer->deserialize('message1')->willReturn($first);
        $serializer->deserialize('message2')->willReturn($second);

        $this->peek(1, 10)->shouldReturn(array($first, $second));
    }
}
