<?php

namespace spec\Bernard\Serializer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SimpleSerializerSpec extends ObjectBehavior
{
    function its_a_serializer()
    {
        $this->shouldHaveType('Bernard\Serializer');
    }

    /**
     * @param Bernard\Envelope $envelope
     * @param Bernard\Message $message
     */
    function it_serializes_messages_into_json($envelope, $message)
    {
        // this is the same as new DefaultMessage('Import', array());
        $envelope->getMessage()->willReturn($message);
        $envelope->getClass()->willReturn('Bernard\Message\DefaultMessage');
        $envelope->getTimestamp()->willReturn(2013);
        $envelope->getName()->willReturn('Import');

        $this->serialize($envelope)
            ->shouldReturn('{"args":{"name":"Import"},"class":"Bernard:Message:DefaultMessage","timestamp":2013}');
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_only_supports_DefaultMessage($envelope)
    {
        $envelope->getClass()->willReturn('Acme\ImportMessage');

        $this->shouldThrow('InvalidArgumentException')->duringSerialize($envelope);

        $envelope->getClass()->willReturn('Bernard\Message\DefaultMessage');

        $this->shouldNotThrow('InvalidArgumentException')->duringSerialize($envelope);
    }
}
