<?php

namespace Bernard\Tests\ServiceResolver;

use Bernard\Message\DefaultMessage;
use Bernard\ServiceResolver\Invocator;

class InvocatorTest extends \PHPUnit_Framework_TestCase
{
    public function testItInvokesServiceObject()
    {
        $message = new DefaultMessage('SendNewsletter');

        $service = $this->getMock('stdClass', array('onSendNewsletter'));
        $service->expects($this->exactly(2))->method('onSendNewsletter')->with($this->equalTo($message));

        $invocator = new Invocator($service, $message);
        $invocator->invoke();

        $invocator();
    }
}
