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

    public function testItSupportsStamps()
    {
        $envelope = new Envelope(new DefaultMessage('SendNewsletter', array()), array(
            'stamp_01' => 'this is the value for "stamp_01"',
            'stamp_02' => 'this is the value for "stamp_02"',
            'stamp_03' => 8151,
        ));

        $this->assertEquals('this is the value for "stamp_01"', $envelope->getStamp('stamp_01'));
        $this->assertEquals('this is the value for "stamp_02"', $envelope->getStamp('stamp_02'));
        $this->assertEquals(8151, $envelope->getStamp('stamp_03'));
        $this->assertEquals('default', $envelope->getStamp('undefined_stamp', 'default'));
        $this->assertNull($envelope->getStamp('undefined_stamp'));
    }

    public function testItStampsCanOnlyBeScalarTypes()
    {
        $envelope = new Envelope(new DefaultMessage('SendNewsletter', array()), array(
            'stamp_01' => array('i', 'am', 'very', 'complex'),
            'stamp_02' => new DefaultMessage('SendNewsletter', array()),
        ));

        $this->assertNull($envelope->getStamp('stamp_01'));
        $this->assertNull($envelope->getStamp('stamp_02'));
    }
}
