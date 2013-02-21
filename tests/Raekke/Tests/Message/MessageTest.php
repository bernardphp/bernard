<?php

namespace Raekke\Tests\Message;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testItUsesClassNameAsNameAndQueueNameNormalized()
    {
        $message = $this->getMockForAbstractClass('Raekke\Message\Message', array(), 'MyCustomMessage');
        $this->assertEquals('MyCustom', $message->getName());
        $this->assertEquals('my-custom', $message->getQueue());

        $message =  new \CustomVendor\SendNewsletterMessage();
        $this->assertEquals('SendNewsletter', $message->getName());
        $this->assertEquals('send-newsletter', $message->getQueue());
    }
}

namespace CustomVendor;
class SendNewsletterMessage extends \Raekke\Message\Message {}
