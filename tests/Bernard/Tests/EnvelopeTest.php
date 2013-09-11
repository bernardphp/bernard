<?php

namespace Bernard\Tests;

use Bernard\Message\DefaultMessage;
use Bernard\Envelope;

class EnvelopeTest extends \PHPUnit_Framework_TestCase
{
    public function testItWrapsAMesageWithMetadata()
    {
        $envelope = new Envelope($message = new DefaultMessage('SendNewsletter'));

        $this->assertEquals(time(), $envelope->getTimestamp());
        $this->assertEquals('Bernard\Message\DefaultMessage', $envelope->getClass());
        $this->assertEquals('SendNewsletter', $envelope->getName());
        $this->assertEquals(0, $envelope->getRetries());
        $this->assertSame($message, $envelope->getMessage());
    }

    public function testItIncrementsRetriesWith1()
    {
        $envelope = new Envelope(new DefaultMessage('SendNewsletter'));

        $this->assertEquals(0, $envelope->getRetries());

        $envelope->incrementRetries();
        $this->assertEquals(1, $envelope->getRetries());
    }
}
