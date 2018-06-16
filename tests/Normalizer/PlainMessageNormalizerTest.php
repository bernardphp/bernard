<?php

namespace Bernard\Tests\Normalizer;

use Bernard\Message\PlainMessage;
use Bernard\Normalizer\PlainMessageNormalizer;

final class PlainMessageNormalizerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function it_normalizes_a_message()
    {
        $normalizer = new PlainMessageNormalizer();

        $normalized = $normalizer->normalize(new PlainMessage('foobar'));

        $this->assertEquals(['name' => 'foobar', 'arguments' => []], $normalized);
    }

    /**
     * @test
     */
    public function it_denormalizes_a_normalized_message()
    {
        $normalizer = new PlainMessageNormalizer();

        $denormalized = $normalizer->denormalize(['name' => 'foobar', 'arguments' => []], null);

        $this->assertInstanceOf(PlainMessage::class, $denormalized);
    }
}
