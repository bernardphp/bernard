<?php

declare(strict_types=1);

namespace Bernard\Tests;

use Bernard\Envelope;
use Bernard\Message\PlainMessage;

final class EnvelopeTest extends \PHPUnit\Framework\TestCase
{
    public function testItWrapsAMessageWithMetadata(): void
    {
        $envelope = new Envelope($message = new PlainMessage('SendNewsletter'));

        $this->assertEquals(time(), $envelope->getTimestamp());
        $this->assertEquals(PlainMessage::class, $envelope->getClass());
        $this->assertEquals('SendNewsletter', $envelope->getName());
        $this->assertSame($message, $envelope->getMessage());
    }
}
