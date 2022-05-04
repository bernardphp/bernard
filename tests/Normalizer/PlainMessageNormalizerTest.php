<?php

declare(strict_types=1);

namespace Bernard\Tests\Normalizer;

use Bernard\Message\PlainMessage;
use Bernard\Normalizer\PlainMessageNormalizer;

final class PlainMessageNormalizerTest extends \PHPUnit\Framework\TestCase
{
    public function testItNormalizesAMessage(): void
    {
        $normalizer = new PlainMessageNormalizer();

        $normalized = $normalizer->normalize(new PlainMessage('foobar'));

        $this->assertEquals(['name' => 'foobar', 'arguments' => []], $normalized);
    }

    public function testItDenormalizesANormalizedMessage(): void
    {
        $normalizer = new PlainMessageNormalizer();

        $denormalized = $normalizer->denormalize(['name' => 'foobar', 'arguments' => []], null);

        $this->assertInstanceOf(PlainMessage::class, $denormalized);
    }
}
