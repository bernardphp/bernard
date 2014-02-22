<?php

namespace Bernard;

use Normalt\Normalizer\AggregateNormalizer;

class Serializer
{
    protected $aggregate;

    public function __construct(AggregateNormalizer $aggregate = null)
    {
        $this->aggregate = $aggregate ?: $this->createAggregateNormalizer();
    }

    public function serialize(Envelope $envelope)
    {
        return json_encode($this->aggregate->normalize($envelope));
    }

    public function unserialize($contents)
    {
        $data = json_decode($contents, true);

        return $this->aggregate->denormalize($data, 'Bernard\Envelope');
    }

    private function createAggregateNormalizer()
    {
        return new AggregateNormalizer(array(
            new Normalizer\EnvelopeNormalizer,
            new Normalizer\DefaultMessageNormalizer,
        ));
    }
}
