<?php

namespace Bernard\Normalizer;

use Normalt\Normalizer\AggregateNormalizer;

class AbstractAggregateNormalizerAware implements \Normalt\Normalizer\AggregateNormalizerAware
{
    /**
     * @var AggregateNormalizer
     */
    protected $aggregate;

    /**
     * @param AggregateNormalizer $aggregate
     */
    public function setAggregateNormalizer(AggregateNormalizer $aggregate)
    {
        $this->aggregate = $aggregate;
    }
}
