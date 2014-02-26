<?php

namespace spec\Bernard\Encoder;

use Bernard\Message\DefaultMessage;

class GenericNormalizerSpec extends \PhpSpec\ObjectBehavior
{
    function it_normalizes_into_array()
    {
        $message = new DefaultMessage('Import', array(
            'id' => 'hello',
            'values' => array(1, 2, 3, 4),
        ));

        $this->normalize($message)->shouldReturn(array(
            'name' => 'Import',
            'arguments' => array(
                'id' => 'hello',
                'values' => array(1,2,3,4),
            ),
        ));
    }

    function it_denormalizes_into_default_message()
    {
        $message = $this->denormalize('Bernard\\Message\\DefaultMessage', array(
            'name' => 'Import',
            'arguments' => array(
                'id' => 'hello',
            ),
        ));

        $message->getName()->shouldReturn('Import');
        $message->offsetGet('id')->shouldReturn('hello');
    }

    /**
     * @param Bernard\Message $message
     */
    function it_only_supports_DefaultMessage($message)
    {
        $this->shouldThrow('InvalidArgumentException')->duringNormalize($message);

        $this->shouldThrow('InvalidArgumentException')
            ->duringDenormalize('Bernard\\Message', array());
    }
}
