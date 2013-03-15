<?php

namespace Bernard\Tests\Message;

class AbstractMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsMessage()
    {
        $this->assertInstanceOf('Bernard\Message', $this->getMockForAbstractClass('Bernard\Message\AbstractMessage'));
    }

    public function testItUsesClassNameAsNameAndQueueNameNormalized()
    {
        $message = $this->getMockForAbstractClass('Bernard\Message\AbstractMessage', array(), 'MyCustomMessage');
        $this->assertEquals('MyCustom', $message->getName());
        $this->assertEquals('my-custom', $message->getQueue());

        $message =  new \CustomVendor\SendNewsletterMessage();
        $this->assertEquals('SendNewsletter', $message->getName());
        $this->assertEquals('send-newsletter', $message->getQueue());
    }
}

namespace CustomVendor;
class SendNewsletterMessage extends \Bernard\Message\AbstractMessage {}
