<?php

namespace Bernard\Tests\Middleware;

use Bernard\Tests\Fixtures;
use Bernard\Middleware\MiddlewareChain;

class MiddlewareChainTest extends \PHPUnit_Framework_TestCase
{
    public function testChainIsCreatedAndCalled()
    {
        $result = '';

        $factory1 = function ($next) use (&$result) {
            return new Fixtures\EchoMiddleware($result, $next);
        };

        $factory2 = function ($next) use (&$result) {
            return new Fixtures\EchoMiddleware($result, $next);
        };

        $chain = new MiddlewareChain(array($factory1));
        $chain->add($factory2);

        $envelope = $this->getMockBuilder('Bernard\Message\Envelope')
            ->disableOriginalConstructor()->getMock();

        $chain->chain(new Fixtures\EchoMiddleware($result))->call($envelope);

        $this->assertEquals('beforebeforecallingafterafter', $result);

    }
}
