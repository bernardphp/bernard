<?php

namespace Bernard\Tests;

use Bernard\Message\PlainMessage;
use Bernard\Envelope;

final class EnvelopeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function it_wraps_a_message_with_metadata()
    {
        $envelope = new Envelope($message = new PlainMessage('SendNewsletter'));

        $this->assertEquals(time(), $envelope->getTimestamp());
        $this->assertEquals(PlainMessage::class, $envelope->getClass());
        $this->assertEquals('SendNewsletter', $envelope->getName());
        $this->assertSame($message, $envelope->getMessage());
    }
}
