<?php

namespace Bernard\Tests\ServiceResolver;

use Bernard\ServiceResolver\Invocator;
use Bernard\ServiceResolver\ForkingInvocator;
use Spork\ProcessManager;

class ForkingInvocatorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->invocator = $this->getMockBuilder('Bernard\ServiceResolver\Invocator')
            ->disableOriginalConstructor()->getMock();
        $this->spork = $this->getMock('Spork\ProcessManager');
    }

    public function testItsAnInvocator()
    {
        $this->assertInstanceOf('Bernard\ServiceResolver\Invocator', new ForkingInvocator($this->spork, $this->invocator));
    }

    public function testItForksWhenInvoked()
    {
        $invocator = new ForkingInvocator($this->spork, $this->invocator);

        $fork = $this->getMockBuilder('Spork\Fork')->disableOriginalConstructor()->getMock();
        $fork->expects($this->once())->method('wait');
        $fork->expects($this->once())->method('fail')->with($this->equalTo(array($invocator, 'fail')));

        $this->spork->expects($this->once())->method('fork')
            ->with($this->equalTo($this->invocator))->will($this->returnValue($fork));

        $invocator->invoke();
    }

    public function testExceptionsAreConvertedToForkingLogicException()
    {
        if (!function_exists('pcntl_fork')) {
            $this->markTestSkipped('The extension "PCNTL" is required for forking to work.');
        }

        $this->setExpectedException('Bernard\Exception\ForkingLogicException');

        $message = $this->getMock('Bernard\Message');
        $message->expects($this->any())->method('getName')->will($this->returnValue('ImportUsers'));

        $invocator = new Invocator(new FailingService(), $message);

        $forking = new ForkingInvocator(new ProcessManager(), $invocator);
        $forking->invoke();
    }
}

class FailingService
{
    public function onImportUsers()
    {
        throw new \Exception('Something bad happended.');
    }
}
