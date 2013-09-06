<?php

namespace Bernard\Tests\ServiceResolver;

use Bernard\ServiceResolver\Invoker;

class InvokerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsCallable()
    {

        $envelope = $this->getMockBuilder('Bernard\Message\Envelope')
            ->disableOriginalConstructor()->getMock();

        $called = false;
        $function = function () use (&$called) {
            $called = true;
        };

        $invoker = new Invoker($function);
        $invoker($envelope);

        $this->assertTrue($called);
    }

    public function testItInvokesServiceObject()
    {
        $envelope = $this->getMockBuilder('Bernard\Message\Envelope')
            ->disableOriginalConstructor()->getMock();
        $envelope->expects($this->once())->method('getMessage')->will($this->returnValue('message'));

        $service = $this->getMock('stdClass', array('onSendNewsletter'));
        $service->expects($this->once())->method('onSendNewsletter')->with($this->equalTo('message'));

        $invoker = new Invoker(array($service, 'onSendNewsletter'));
        $invoker->invoke($envelope);
    }

    public function testOnlyAllowCallbacks()
    {
        $this->setExpectedException('InvalidArgumentException', 'Expected argument of type "callable" but got "string".');

        new Invoker('something not callabkle');
    }
}
