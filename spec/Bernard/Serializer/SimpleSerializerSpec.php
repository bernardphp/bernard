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

    function it_deserialize_into_default_message()
    {
        $envelope = $this->deserialize('{"args":{"name":"Import","newsletterId":10},"class":"Bernard:Message:DefaultMessage","timestamp":2013}');

        $envelope->getClass()->shouldReturn('Bernard\Message\DefaultMessage');
        $envelope->getTimestamp()->shouldReturn(2013);
        $envelope->getName()->shouldReturn('Import');
        $envelope->getMessage()->shouldReturnAnInstanceOf('Bernard\Message\DefaultMessage');

        $message = $envelope->getMessage();
        $message->shouldBeAnInstanceOf('Bernard\Message\DefaultMessage');
        $message->shouldHavePropertyValue('newsletterId', 10);
    }

    function it_uses_DefaultMessage_when_deserializing_non_DefaultMessage_clases()
    {
        $envelope = $this->deserialize('{"args":{"newsletterId":10},"class":"Acme:Message:Import","timestamp":2013}');

        $envelope->getName()->shouldReturn('Import');
        $envelope->getMessage()->shouldBeAnInstanceOf('Bernard\Message\DefaultMessage');
    }

    public function getMatchers()
    {
        return array(
            'havePropertyValue' => function ($subject, $key, $value) {
                return property_exists($subject, $key) && $subject->$key == $value;
            },
        );
    }
}
