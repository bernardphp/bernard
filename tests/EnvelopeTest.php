<?php

namespace Bernard\Tests;

use Bernard\Message\DefaultMessage;
use Bernard\Envelope;

class EnvelopeTest extends \PHPUnit_Framework_TestCase
{
    public function testItWrapsAMessageWithMetadata()
    {
        $envelope = new Envelope($message = new DefaultMessage('SendNewsletter'));

        $this->assertEquals(time(), $envelope->getTimestamp());
        $this->assertEquals('Bernard\Message\DefaultMessage', $envelope->getClass());
        $this->assertEquals('SendNewsletter', $envelope->getName());
        $this->assertSame($message, $envelope->getMessage());
    }
}
