<?php

declare(strict_types=1);

namespace Bernard;

use Normalt\Normalizer\AggregateNormalizer;

class Serializer
{
    protected $aggregate;

    public function __construct(AggregateNormalizer $aggregate = null)
    {
        $this->aggregate = $aggregate ?: $this->createAggregateNormalizer();
    }

    /**
     * @return string
     */
    public function serialize(Envelope $envelope)
    {
        return json_encode($this->aggregate->normalize($envelope));
    }

    /**
     * @param string $contents
     *
     * @return Envelope
     */
    public function unserialize($contents)
    {
        $data = json_decode($contents, true);

        return $this->aggregate->denormalize($data, 'Bernard\Envelope');
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
