<?php

namespace spec\Bernard\Symfony;

use Bernard\Message\DefaultMessage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DefaultMessageNormalizerSpec extends ObjectBehavior
{
    function it_supports_default_message_objects_when_normalizing()
    {
        $this->supportsNormalization(new DefaultMessage('Import'))
            ->shouldReturn(true);
    }

    function its_a_normalizer_and_denormalizer()
    {
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_default_message_types_when_denormalizing()
    {
        $this->supportsDenormalization('{}', 'Bernard\Message\DefaultMessage')
            ->shouldReturn(true);
    }

    function it_normalizes_and_keeps_name_as_priority_over_properties()
    {
        $message = new DefaultMessage("Import", array(
            'name' => 'NotImport',
            'users' => array(
                'Henrik',
                'Jakob',
            ),
        ));

        $this->normalize($message)->shouldReturn(array(
            'name' => 'Import',
            'users' => array('Henrik', 'Jakob'),
        ));
    }

    function it_denormalizes_into_default_message()
    {
        $data = array('name' => 'Import', 'users' => array('Henrik'));

        $message = $this->denormalize($data, 'Bernard\Message\DefaultMessage');

        $message->getName()->shouldReturn('Import');
        $message->shouldHavePropertyValue('users', array('Henrik'));
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
