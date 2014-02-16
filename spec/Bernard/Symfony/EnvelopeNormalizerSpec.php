<?php

namespace spec\Bernard\Symfony;

use Bernard\Envelope;
use Bernard\Message\DefaultMessage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EnvelopeNormalizerSpec extends ObjectBehavior
{
    function its_serializer_aware()
    {
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer');
    }

    function its_a_normalizer_and_denormalizer()
    {
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    /**
     * @param Symfony\Component\Serializer\Serializer $serializer
     * @param Bernard\Message $message
     * @param Bernard\Envelope $envelope
     */
    function it_normalizes_message($serializer, $message, $envelope)
    {
        $this->setSerializer($serializer);

        $envelope->getTimestamp()->willReturn(1337);
        $envelope->getClass()->willReturn('Bernard\Message\DefaultMessage');
        $envelope->getMessage()->willReturn($message);

        $serializer->normalize($message, null, array())->willReturn(array(
            'newsletter' => 10,
        ));

        $this->normalize($envelope)->shouldReturn(array(
            'args' => array('newsletter' => 10),
            'class' => 'Bernard:Message:DefaultMessage',
            'timestamp' => 1337,
        ));
    }

    /**
     * @param Symfony\Component\Serializer\Serializer $serializer
     * @param Bernard\Message $message
     * @param Bernard\Envelope $envelope
     */
    function it_uses_DefaulMessage_when_class_dosent_exists($serializer, $message, $envelope)
    {
        $this->setSerializer($serializer);

        $data = array('class' => 'Does:Not:Exists', 'args' => array(), 'timestamp' => 1337);

        $serializer->denormalize(array('name' => 'Exists'), 'Bernard\Message\DefaultMessage', null, array())
            ->willReturn($message);

        $envelope = $this->denormalize($data, 'Bernard\Envelope');
        $envelope->getMessage()->shouldReturn($message);
        $envelope->getClass()->shouldReturn('Does\Not\Exists');
        $envelope->getTimestamp()->shouldReturn(1337);
    }

    /**
     * @param Symfony\Component\Serializer\Serializer $serializer
     * @param Bernard\Message $message
     * @param Bernard\Envelope $envelope
     */
    function it_denormalizes_custom_class($serializer, $message, $envelope)
    {
        $this->setSerializer($serializer);

        $data = array('class' => 'stdClass', 'args' => array(), 'timestamp' => 1337);

        $serializer->denormalize(array(), 'stdClass', null, array())->willReturn($message);

        $envelope = $this->denormalize($data, 'Bernard\Envelope');
        $envelope->getMessage()->shouldReturn($message);
        $envelope->getClass()->shouldReturn('stdClass');
    }

    function it_supports_envelope_type_when_denormalizing()
    {
        $this->supportsDenormalization(array(), 'Bernard\Envelope')
            ->shouldReturn(true);
    }

    function it_supports_envelope_objects_for_normalization()
    {
        $envelope = new Envelope(new DefaultMessage('Import'), 'Bernard\Message\DefaultMessage', 1337);

        $this->supportsNormalization($envelope)->shouldReturn(true);
    }
}
