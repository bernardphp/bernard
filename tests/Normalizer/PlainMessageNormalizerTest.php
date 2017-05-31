<?php

namespace Bernard\Tests\Normalizer;

use Bernard\Message\PlainMessage;
use Bernard\Normalizer\PlainMessageNormalizer;
use PHPUnit_Framework_TestCase;

class PlainMessageNormalizerTest extends PHPUnit_Framework_TestCase
{
    public function testDenormalize()
    {
        $sut = new PlainMessageNormalizer();
        $normalized = $sut->denormalize(['name' => 'foobar', 'arguments' => []], null);
        $this->assertTrue($normalized instanceof PlainMessage);
    }
}
