<?php

namespace Bernard\Tests;

use Bernard\Message\PlainMessage;
use Bernard\Envelope;

class EnvelopeTest extends \PHPUnit\Framework\TestCase
{
    public function testItWrapsAMessageWithMetadata()
    {
        $envelope = new Envelope($message = new PlainMessage('SendNewsletter'));

        $this->assertEquals(time(), $envelope->getTimestamp());
        $this->assertEquals('Bernard\Message\PlainMessage', $envelope->getClass());
        $this->assertEquals('SendNewsletter', $envelope->getName());
        $this->assertSame($message, $envelope->getMessage());
    }
}
