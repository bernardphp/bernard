<?php

namespace Bernard\Normalizer;

use Normalt\Normalizer\AggregateNormalizer;
use Normalt\Normalizer\AggregateNormalizerAware;

class AbstractAggregateNormalizerAware implements AggregateNormalizerAware
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
