<?php

namespace spec\Bernard;

class EncoderSpec extends \PhpSpec\ObjectBehavior
{
    /**
     * @param Normalt\NormalizerSet $normalizer
     */
    function let($normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    /**
     * @param Bernard\Envelope $envelope
     * @param Bernard\Message\DefaultMessage $message
     */
    function it_encodes_normalized_envelope_into_json($envelope, $message, $normalizer)
    {
        $envelope->getMessage()->willReturn($message);
        $envelope->getClass()->willReturn('Bernard\\Message\\DefaultMessage');
        $envelope->getTimestamp()->willReturn(1337);

        $message->getName()->willReturn('Import');
        $message->all()->willReturn(array(
            'arg1' => 'value',
        ));

        $this->encode($envelope)
            ->shouldReturn('{"class":"Bernard\\\\Message\\\\DefaultMessage","timestamp":1337,"message":{"name":"Import","arguments":{"arg1":"value"}}}');
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_decodes_into_envelope($envelope, $normalizer)
    {
        $envelope = $this->decode('{"class":"Bernard\\\\Message\\\\DefaultMessage","timestamp":1337,"message":{"name":"Import","arguments":{"arg1":"value"}}}');

        $envelope->getClass()->shouldReturn('Bernard\\Message\\DefaultMessage');
        $envelope->getTimestamp()->shouldReturn(1337);

        $message = $envelope->getMessage();
        $message->shouldBeAnInstanceOf('Bernard\\Message\\DefaultMessage');
        $message->getName()->shouldReturn('Import');
        $message->all()->shouldReturn(array('arg1' => 'value'));
    }
}
