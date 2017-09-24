<?php

namespace Bernard\Tests\Message;

use Bernard\Tests\Fixtures;

class AbstractMessageTest extends \PHPUnit\Framework\TestCase
{
    public function testImplementsMessage()
    {
        $this->assertInstanceOf('Bernard\Message', new Fixtures\SendNewsletterMessage);
        $this->assertInstanceOf('Bernard\Message\AbstractMessage', new Fixtures\SendNewsletterMessage);
    }

    public function testItUsesClassNameAsNameAndQueueNameNormalized()
    {
        $message =  new Fixtures\SendNewsletterMessage();
        $this->assertEquals('SendNewsletter', $message->getName());
        $this->assertEquals('send-newsletter', $message->getQueue());
    }
}
