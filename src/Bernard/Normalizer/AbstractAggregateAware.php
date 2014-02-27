<?php

namespace Bernard\Normalizer;

abstract class AbstractAggregateAware implements AggregateAware
{
    protected $aggregate;

    public function setAggregate($aggregate)
    {
        $this->aggregate = $aggregate;
    }
}
