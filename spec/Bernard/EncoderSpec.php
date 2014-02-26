<?php

namespace spec\Bernard;

class EncoderSpec extends \PhpSpec\ObjectBehavior
{
    /**
     * @param Bernard\Encoder\Normalizer $normalizer
     */
    function let($normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    /**
     * @param Bernard\Envelope $envelope
     * @param Bernard\Message $message
     */
    function it_encodes_envelope_and_message_into_json($envelope, $message, $normalizer)
    {
        $envelope->getMessage()->willReturn($message);
        $envelope->getClass()->willReturn('Bernard\Message');
        $envelope->getTimestamp()->willReturn(1337);

        $normalizer->normalize($message)->willReturn(array(
            'arg1' => 'value',
        ));

        // \\\\ is because the \\ is escaped so it should be doubled!
        $this->encode($envelope)->shouldReturn('{"class":"Bernard\\\\Message","timestamp":1337,"message":{"arg1":"value"}}');
    }

    /**
     * @param Bernard\Message $message
     */
    function it_decodes_into_envelope($message, $normalizer)
    {
        $normalizer->denormalize('Bernard\\Message', array('arg1' => 'value'))->willReturn($message);

        $envelope = $this->decode('{"class":"Bernard\\\\Message","timestamp":1337,"message":{"arg1":"value"}}');
        $envelope->getTimestamp()->shouldReturn(1337);
        $envelope->getClass()->shouldReturn('Bernard\\Message');
        $envelope->getMessage()->shouldReturn($message);
    }
}
