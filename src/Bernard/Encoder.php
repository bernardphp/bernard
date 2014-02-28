<?php

namespace Bernard;

use Normalt\NormalizerSet;

class Encoder
{
    protected $normalizer;

    public function __construct()
    {
        $this->normalizer = new NormalizerSet(array(
            new Normalizer\EnvelopeNormalizer,
            new Normalizer\DefaultMessageNormalizer,
        ));
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
