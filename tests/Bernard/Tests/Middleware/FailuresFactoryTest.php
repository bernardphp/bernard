<?php

namespace Bernard\Tests\Middleware;

use Bernard\Middleware\FailuresFactory;

class FailuresFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsFailuresMiddleware()
    {
        $factory = new FailuresFactory($this->getMock('Bernard\QueueFactory'));
        $middleware = $factory($this->getMock('Bernard\Middleware'));

        $this->assertInstanceOf('Bernard\Middleware\FailuresMiddleware', $middleware);
    }
}
