<?php

namespace Bernard\Tests\Middleware;

use Bernard\Middleware\LoggerFactory;

class LoggerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsLoggerMiddleware()
    {
        $factory = new LoggerFactory($this->getMock('Psr\Log\LoggerInterface'));
        $middleware = $factory($this->getMock('Bernard\Middleware'));

        $this->assertInstanceOf('Bernard\Middleware\LoggerMiddleware', $middleware);
    }
}
