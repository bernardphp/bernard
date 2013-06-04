<?php

namespace Bernard\Tests\ServiceResolver;

use Bernard\ServiceResolver\ForkingResolver;
use Bernard\ServiceResolver\ForkingInvocator;

class ForkingResolverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->resolver = $this->getMock('Bernard\ServiceResolver');
        $this->spork = $this->getMockBuilder('Spork\ProcessManager')->disableOriginalConstructor()
            ->getMock();
    }

    public function testItIsAServiceResolver()
    {
        $this->assertInstanceOf('Bernard\ServiceResolver', new ForkingResolver($this->spork, $this->resolver));
    }

    public function testItProxiesRegister()
    {
        $this->resolver->expects($this->once())->method('register')->with($this->equalTo('name'), $this->equalTo('service'));

        $resolver = new ForkingResolver($this->spork, $this->resolver);
        $resolver->register('name', 'service');
    }

    public function testItWrapsInvocatorWithForkingInvocator()
    {
        $invocator = $this->getMockBuilder('Bernard\ServiceResolver\Invocator')->disableOriginalConstructor()
            ->getMock();

        $message = $this->getMock('Bernard\Message');

        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo($message))
            ->will($this->returnValue($invocator));

        $resolver = new ForkingResolver($this->spork, $this->resolver);

        $this->assertEquals(new ForkingInvocator($this->spork, $invocator), $resolver->resolve($message));
    }
}
