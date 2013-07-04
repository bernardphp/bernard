<?php

namespace Bernard\Tests\ServiceResolver;

use Bernard\Message\DefaultMessage;
use Bernard\ServiceResolver\Invoker;

class InvokerTest extends \PHPUnit_Framework_TestCase
{
    public function testItInvokesServiceObject()
    {
        $envelope = $this->getMockBuilder('Bernard\Message\Envelope')
            ->disableOriginalConstructor()->getMock();
        $envelope->expects($this->once())->method('getName')->will($this->returnValue('SendNewsletter'));
        $envelope->expects($this->once())->method('getMessage')->will($this->returnValue('message'));

        $service = $this->getMock('stdClass', array('onSendNewsletter'));
        $service->expects($this->once())->method('onSendNewsletter')->with($this->equalTo('message'));

        $invocator = new Invoker($service, $envelope);
        $invocator->invoke();
    }
}
