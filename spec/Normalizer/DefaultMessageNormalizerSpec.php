<?php

namespace spec\Bernard\Normalizer;

use Bernard\Message\DefaultMessage;
use PhpSpec\ObjectBehavior;

class DefaultMessageNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\Normalizer\DefaultMessageNormalizer');
    }

    function it_is_a_normalizer_and_denormailzer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_normalizes_a_default_message(DefaultMessage $message)
    {
        $message->getName()->willReturn('message');
        $message->all()->willReturn(array('key' => 'value'));

        $this->normalize($message)->shouldReturn(array(
            'name'      => 'message',
            'arguments' => array('key' => 'value'),
        ));
    }

    function it_denormalizes_a_default_message()
    {
        $normalized = array(
            'name'      => 'message',
            'arguments' => array('key' => 'value'),
        );

        $message = $this->denormalize($normalized, 'Bernard\Message\DefaultMessage');

        $message->shouldImplement('Bernard\Message');
        $message->getName()->shouldReturn('message');
        $message->all()->shouldReturn(array('key' => 'value'));
    }

    function it_supports_denormalization_of_a_default_message()
    {
        $this->supportsDenormalization([], 'Bernard\Message\DefaultMessage')->shouldReturn(true);
    }

    function it_supports_normalization_of_a_default_message(DefaultMessage $message)
    {
        $this->supportsNormalization($message)->shouldReturn(true);
    }
}
