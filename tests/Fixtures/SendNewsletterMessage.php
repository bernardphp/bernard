<?php

declare(strict_types=1);

namespace Bernard\Tests\Fixtures;

use Bernard\Message;
use JMS\Serializer\Annotation as JMSSerializer;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * This is a Custom implementation of a Message.
 */
class SendNewsletterMessage implements Message, NormalizableInterface, DenormalizableInterface
{
    use Message\HasName;
    use Message\HasQueue;

    /**
     * @JMSSerializer\Type("integer")
     * @JMSSerializer\SerializedName("newsletterId")
     */
    public $newsletterId = 10;

    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = [])
    {
        return get_object_vars($this);
    }

    public function denormalize(DenormalizerInterface $denormalizer, $data, $format = null, array $context = []): void
    {
        $this->newsletterId = $data['newsletterId'];
    }
}
