<?php

namespace Bernard\Tests\Spork;

use Bernard\Message\DefaultMessage;
use Bernard\Message\Envelope;
use Bernard\ServiceResolver\Invoker;
use Bernard\Spork\ServiceProxy;
use Bernard\Tests\Fixtures;
use Spork\ProcessManager;

class ServiceProxyTest extends \PHPUnit_Framework_TestCase
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

    public function testIsInvokedThroughInvoker()
    {
        $proxy = new ServiceProxy($this->spork, array($this->service, 'onCreateFile'));

        $invoker = new Invoker($proxy);
        $invoker->invoke(new Envelope(new DefaultMessage('CreateFile')));

        // This is a hack, since memory is isolated from parent and there is no direct link
        // to the fork used.
        $this->assertTrue(is_file(__DIR__ . '/../Fixtures/create_file.test'));
    }

    public function testItProxiesMethod()
    {
        $proxy = new ServiceProxy($this->spork, array($this->service, 'onCreateFile'));
        $proxy(new DefaultMessage('CreateFile'));

        // This is a hack, since memory is isolated from parent and there is no direct link
        // to the fork used.
        $this->assertTrue(is_file(__DIR__ . '/../Fixtures/create_file.test'));
    }

    public function testExceptionsAreConvertedToProcessLogicException()
    {
        $this->setExpectedException('Bernard\Spork\Exception\ProcessException');

        $proxy = new ServiceProxy($this->spork, array($this->service, 'onFailSendNewsletter'));
        $proxy(new DefaultMessage('FailSendNewsletter'));
    }
}
