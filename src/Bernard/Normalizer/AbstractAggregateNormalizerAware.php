<?php

namespace Bernard\Normalizer;

use Normalt\Normalizer\AggregateNormalizer;

class AbstractAggregateNormalizerAware implements \Normalt\Normalizer\AggregateNormalizerAware
{
    protected $aggregate;

    public function setAggregateNormalizer(AggregateNormalizer $aggregate)
    {
        $this->aggregate = $aggregate;
    }
}
