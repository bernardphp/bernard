<?php

namespace Bernard\Tests\Middleware;

use Bernard\Queue\InMemoryQueue;
use Bernard\Tests\Fixtures;
use Bernard\Middleware\MiddlewareBuilder;

class MiddlewareBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builder = new MiddlewareBuilder;
        $this->queue = new InMemoryQueue('send-newsletter');
        $this->envelope = $this->getMockBuilder('Bernard\Envelope')
            ->disableOriginalConstructor()->getMock();
    }

    public function testChainIsCreatedAndCalled()
    {
        $result = '';

        $this->builder->push($this->createMiddlewareFactory($result, 1));
        $this->builder->push($this->createMiddlewareFactory($result, 2));

        $chain = $this->builder->build(new Fixtures\TickMiddleware($result, 'middle'))
            ->call($this->envelope, $this->queue);

        $this->assertEquals('12middle21', $result);
    }

    public function testUnshift()
    {
        $result = '';

        $this->builder->push($this->createMiddlewareFactory($result, 1));
        $this->builder->unshift($this->createMiddlewareFactory($result, 2));

        $this->builder->build(new Fixtures\TickMiddleware($result, 'middle'))
            ->call($this->envelope, $this->queue);

        $this->assertEquals('21middle12', $result);
    }

    protected function createMiddlewareFactory(&$result, $tick)
    {
        return function ($next) use (&$result, $tick) {
            return new Fixtures\TickMiddleware($result, $tick, $next);
        };
    }
}
