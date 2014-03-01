<?php

namespace Bernard\Normalizer;

use Normalt\Marshaller;

class AbstractMarshallerAware implements \Normalt\MarshallerAware
{
    protected $marshaller;

    public function setMarshaller(Marshaller $marshaller)
    {
        $this->marshaller = $marshaller;
    }
}
