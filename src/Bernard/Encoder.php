<?php

namespace Bernard;

use Normalt\Marshaller;

class Encoder
{
    protected $marshaller;

    public function __construct()
    {
        $this->marshaller = new Marshaller(array(
            new Normalizer\EnvelopeNormalizer,
            new Normalizer\DefaultMessageNormalizer,
        ));
    }

    public function encode(Envelope $envelope)
    {
        return json_encode($this->marshaller->normalize($envelope));
    }

    public function decode($contents)
    {
        $data = json_decode($contents, true);

        return $this->marshaller->denormalize($data, 'Bernard\Envelope');
    }
}
