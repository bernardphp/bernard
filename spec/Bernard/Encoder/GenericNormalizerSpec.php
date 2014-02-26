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
            'id' => 'hello',
            'values' => array(1,2,3,4),
        ));
    }

    function it_denormalizes_into_default_message()
    {
        $message = $this->denormalize('Bernard\\Message\\DefaultMessage', array(
            'name' => 'Import',
            'id' => 'hello',
        ));

        $message->getName()->shouldReturn('Import');
        $message->shouldHavePropertyValue('id', 'hello');
    }

    function it_does_not_override_name_when_normalizing()
    {
        $message = new DefaultMessage('Import', array(
            'name' => 'OtherThanImport',
        ));

        $this->normalize($message)->shouldReturn(array(
            'name' => 'Import',
        ));
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

    public function getMatchers()
    {
        return array(
            'havePropertyValue' => function ($subject, $property, $value) {
                return property_exists($subject, $property) && $subject->$property == $value;
            },
        );
    }
}
