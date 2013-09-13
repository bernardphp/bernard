<?php

namespace Bernard\Tests\Middleware;

use Bernard\Middleware\ErrorLogFactory;

class ErrorLogFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsErrorLogMiddleware()
    {
        $factory = new ErrorLogFactory($this->getMock('Bernard\QueueFactory'));
        $middleware = $factory($this->getMock('Bernard\Middleware'));

        $this->assertInstanceOf('Bernard\Middleware\ErrorLogMiddleware', $middleware);
    }
}
