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
        if (!function_exists('pcntl_fork')) {
            $this->markTestSkipped('The extension "PCNTL" is required for forking to work.');
        }

        $this->spork = new ProcessManager();
        $this->service = new Fixtures\Service;
    }

    public function tearDown()
    {
        @unlink(__DIR__ . '/../Fixtures/create_file.test');
    }

    public function testItsAnInvoker()
    {
        $this->assertInstanceOf('Bernard\ServiceResolver\Invoker', new ProcessInvoker($this->spork, $this->createInvoker('ImportUsers')));
    }

    public function testItInvokesDecoratedInvoker()
    {
        $invoker = new ProcessInvoker($this->spork, $this->createInvoker('CreateFile'));
        $invoker->invoke();

        // This is a hack, since memory is isolated from parent and there is no direct link
        // to the fork used.
        $this->assertTrue(is_file(__DIR__ . '/../Fixtures/create_file.test'));
    }

    public function testExceptionsAreConvertedToProcessLogicException()
    {
        $this->setExpectedException('Bernard\Spork\Exception\ProcessException');

        $forking = new ProcessInvoker($this->spork, $this->createInvoker('FailSendNewsletter'));
        $forking->invoke();
    }

    public function createInvoker($name)
    {
        return new Invoker($this->service, new Envelope(new DefaultMessage($name)));
    }
}
