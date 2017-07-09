<?php

namespace Bernard\Tests\Normalizer;

use Bernard\Message\PlainMessage;
use Bernard\Normalizer\PlainMessageNormalizer;

class PlainMessageNormalizerTest extends \PHPUnit\Framework\TestCase
{
    public function testDenormalize()
    {
        $sut = new PlainMessageNormalizer();
        $normalized = $sut->denormalize(['name' => 'foobar', 'arguments' => []], null);
        $this->assertTrue($normalized instanceof PlainMessage);
    }
}
