<?php

namespace Bernard\Tests\Spork;

use Bernard\Message\Envelope;
use Bernard\Message\DefaultMessage;
use Bernard\ServiceResolver\Invoker;
use Bernard\Spork\ProcessInvoker;
use Bernard\Tests\Fixtures;
use Spork\ProcessManager;

class ProcessInvokerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->invoker = $this->getMockBuilder('Bernard\ServiceResolver\Invoker')
            ->disableOriginalConstructor()->getMock();
        $this->spork = $this->getMock('Spork\ProcessManager');
    }

    public function testItsAnInvoker()
    {
        $this->assertInstanceOf('Bernard\ServiceResolver\Invoker', new ProcessInvoker($this->spork, $this->invoker));
    }

    public function testItForksWhenInvoked()
    {
        $invoker = new ProcessInvoker($this->spork, $this->invoker);

        $fork = $this->getMockBuilder('Spork\Fork')->disableOriginalConstructor()->getMock();
        $fork->expects($this->once())->method('wait');
        $fork->expects($this->once())->method('fail')->with($this->equalTo(array($invoker, 'fail')));

        $this->spork->expects($this->once())->method('fork')
            ->with($this->equalTo(array($this->invoker, 'invoke')))->will($this->returnValue($fork));

        $invoker->invoke();
    }

    public function testExceptionsAreConvertedToProcessLogicException()
    {
        if (!function_exists('pcntl_fork')) {
            $this->markTestSkipped('The extension "PCNTL" is required for forking to work.');
        }

        $this->setExpectedException('Bernard\Spork\Exception\ProcessException');

        $service = new Fixtures\Service;
        $envelope = new Envelope(new DefaultMessage('FailSendMessage'));

        $invoker = new Invoker($service, $envelope);

        $forking = new ProcessInvoker(new ProcessManager(), $invoker);
        $forking->invoke();
    }
}
