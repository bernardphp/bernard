<?php

namespace Bernard\Tests\Fixtures;

use Bernard\Message\AbstractMessage;
use JMS\Serializer\Annotation as JMSSerializer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;

/**
 * This is a Custom implementation of a Message.
 */
class SendNewsletterMessage extends AbstractMessage implements NormalizableInterface, DenormalizableInterface
{
    /**
     * @JMSSerializer\Type("integer")
     * @JMSSerializer\SerializedName("newsletterId")
     */
    public $newsletterId = 10;

    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = array())
    {
        return get_object_vars($this);
    }

    public function denormalize(DenormalizerInterface $denormalizer, $data, $format = null, array $context = array())
    {
        $this->newsletterId = $data['newsletterId'];
    }
}
