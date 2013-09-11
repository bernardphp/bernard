<?php

namespace Bernard\Tests\Middleware;

use Bernard\Middleware\RetryFactory;

class RetryFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsRetryMiddleware()
    {
        $factory = new RetryFactory($this->getMock('Bernard\QueueFactory'));
        $middleware = $factory($this->getMock('Bernard\Middleware'));

        $this->assertInstanceOf('Bernard\Middleware\RetryMiddleware', $middleware);
    }
}
