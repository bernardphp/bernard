<?php

namespace Bernard\Tests\ServiceResolver;

use Bernard\ServiceResolver\Invoker;

class InvokerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsCallable()
    {
        $envelope = $this->getMockBuilder('Bernard\Envelope')
            ->disableOriginalConstructor()->getMock();
        $envelope->expects($this->once())->method('getMessage')->will($this->returnValue('message'));

        $service = $this->getMock('stdClass', array('onSendNewsletter'));
        $service->expects($this->once())->method('onSendNewsletter')->with($this->equalTo('message'));

        $invoker = new Invoker(array($service, 'onSendNewsletter'));
        $invoker->call($envelope);
    }

    public function testOnlyAllowCallbacks()
    {
        $this->setExpectedException('InvalidArgumentException');

        new Invoker('something not callabkle');
    }
}
