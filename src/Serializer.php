<?php

namespace Bernard;

use Normalt\Normalizer\AggregateNormalizer;

/**
 * @package Bernard
 */
class Serializer implements SerializerInterface
{
    protected $aggregate;

    /**
     * @param AggregateNormalizer|null $aggregate
     */
    public function __construct(AggregateNormalizer $aggregate = null)
    {
        $this->aggregate = $aggregate ?: $this->createAggregateNormalizer();
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(Envelope $envelope)
    {
        return json_encode($this->aggregate->normalize($envelope));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($contents)
    {
        $data = json_decode($contents, true);

        return $this->aggregate->denormalize($data, Envelope::class);
    }

    /**
     * @return AggregateNormalizer
     */
    private function createAggregateNormalizer()
    {
        return new AggregateNormalizer([
            new Normalizer\EnvelopeNormalizer(),
            new Normalizer\PlainMessageNormalizer(),
        ]);
    }
}
