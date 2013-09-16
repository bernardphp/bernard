<?php

namespace Bernard\Tests\Middleware;

use Bernard\Envelope;
use Bernard\Queue\InMemoryQueue;
use Bernard\Middleware\LoggerMiddleware;
use Psr\Log\NullLogger;

class LoggerMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->logger = new NullLogger;
        $this->next = $this->getMock('Bernard\Middleware');

    }

    public function testNextIsCalled()
    {
        $envelope = new Envelope($this->getMock('Bernard\Message'));

        $this->next->expects($this->once())->method('call')->with($envelope);

        $middleware = new LoggerMiddleware($this->next, $this->logger);
        $middleware->call($envelope, new InMemoryQueue('queue'));
    }
}
