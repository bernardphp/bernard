<?php

namespace Raekke\Tests\Message;

use Raekke\Message\DefaultMessage;

class DefaultMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testItSupportsDynamicProperties()
    {
        $message = new DefaultMessage('SendNewsletter');

        $this->assertObjectNotHasAttribute('newsletterId', $message);

        $message = new DefaultMessage('SendNewsletter', array(
            'newsletterId' => 12,
        ));

        $this->assertObjectHasAttribute('newsletterId', $message);
        $this->assertEquals(12, $message->newsletterId);
    }

    public function testItHaveAName()
    {
        $message = new DefaultMessage('SendNewsletter');

        $this->assertObjectHasAttribute('messageName', $message);
        $this->assertEquals('SendNewsletter', $message->getName());

        $message = new DefaultMessage('SendNewsletter', array(
            'messageName' => 'NotSendNewsletter',
        ));

        $this->assertEquals('SendNewsletter', $message->getName());
    }
}
