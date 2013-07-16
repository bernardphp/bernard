<?php

namespace Bernard\Tests\ServiceResolver;

use Bernard\Spork\ProcessDecoratingResolver;
use Bernard\Spork\ProcessInvoker;

class ProcessDecoratingResolverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->resolver = $this->getMock('Bernard\ServiceResolver');
        $this->spork = $this->getMockBuilder('Spork\ProcessManager')->disableOriginalConstructor()
            ->getMock();
    }

    public function testItIsAServiceResolver()
    {
        $this->assertInstanceOf('Bernard\ServiceResolver', new ProcessDecoratingResolver($this->spork, $this->resolver));
    }

    public function testItProxiesRegister()
    {
        $this->resolver->expects($this->once())->method('register')->with($this->equalTo('name'), $this->equalTo('service'));

        $resolver = new ProcessDecoratingResolver($this->spork, $this->resolver);
        $resolver->register('name', 'service');
    }

    public function testItWrapsInvokerWithProcessDecoratingInvoker()
    {
        $invoker = $this->getMockBuilder('Bernard\ServiceResolver\Invoker')
            ->disableOriginalConstructor()->getMock();

        $envelope = $this->getMockBuilder('Bernard\Message\Envelope')
            ->disableOriginalConstructor()->getMock();

        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo($envelope))
            ->will($this->returnValue($invoker));

        $resolver = new ProcessDecoratingResolver($this->spork, $this->resolver);
        $this->assertInstanceOf('Bernard\Spork\ServiceProxy', $resolver->resolve($envelope));

    }
}
