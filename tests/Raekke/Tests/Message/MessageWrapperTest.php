<?php

namespace Raekke\Tests\Message;

use Raekke\Message\DefaultMessage;
use Raekke\Message\MessageWrapper;

class MessageWrapperTest extends \PHPUnit_Framework_TestCase
{
    public function testItWrapsAMesageWithMetadata()
    {
        $wrapper = new MessageWrapper($message = new DefaultMessage('SendNewsletter'));

        $this->assertEquals(time(), $wrapper->getTimestamp());
        $this->assertEquals('Raekke\Message\DefaultMessage', $wrapper->getClass());
        $this->assertEquals('SendNewsletter', $wrapper->getName());
        $this->assertEquals(0, $wrapper->getRetries());
        $this->assertSame($message, $wrapper->getMessage());
    }

    public function testItIncrementsRetriesWith1()
    {
        $wrapper = new MessageWrapper(new DefaultMessage('SendNewsletter'));

        $this->assertEquals(0, $wrapper->getRetries());

        $wrapper->incrementRetries();
        $this->assertEquals(1, $wrapper->getRetries());
    }
}
