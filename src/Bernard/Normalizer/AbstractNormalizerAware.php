<?php

namespace Bernard\Normalizer;

class AbstractNormalizerAware implements \Normalt\NormalizerAware
{
    protected $normalizer;

    public function setNormalizer($normalizer)
    {
        $this->normalizer = $normalizer;
    }
}
