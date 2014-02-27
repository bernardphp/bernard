<?php

namespace Bernard;

use Bernard\Normalizer\AggregateNormalizer;

class Encoder
{
    protected $normalizer;

    public function __construct()
    {
        $this->normalizer = new AggregateNormalizer;
    }

    public function encode(Envelope $envelope)
    {
        return json_encode($this->normalizer->normalize($envelope));
    }

    public function decode($contents)
    {
        $data = json_decode($contents, true);

        return $this->normalizer->denormalize($data, 'Bernard\Envelope');
    }
}
