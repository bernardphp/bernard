<?php

namespace spec\Bernard\Normalizer;

use Bernard\Normalizer\PlainMessageNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PlainMessageNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PlainMessageNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_is_adenormailzer()
    {
        $this->shouldImplement(DenormalizerInterface::class);
    }
}
