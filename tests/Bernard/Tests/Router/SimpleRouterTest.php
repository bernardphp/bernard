<?php

namespace Bernard\Tests;

use Bernard\Router\SimpleRouter;
use Bernard\Envelope;
use Bernard\Message\DefaultMessage;

class SimpleRouterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->router = new SimpleRouter();
    }

    public function testThrowsExceptionWhenReceiverIsNotSupported()
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->router->add('SendNewsletter', 1);
    }

    public function testThrowsExceptionWhenNothingMatches()
    {
        $this->setExpectedException('Bernard\Exception\ReceiverNotFoundException');

        $envelope = new Envelope(new DefaultMessage('SendNewsletter'));

        $this->router->map($envelope);
    }

    public function testReceiversAreAddedThroughConstructor()
    {
        $callable = function () {};
        $envelope = new Envelope(new DefaultMessage('SendNewsletter'));

        $router = new SimpleRouter(array(
            'SendNewsletter' => $callable,
        ));

        $this->assertSame($callable, $router->map($envelope));
    }

    /**
     * @dataProvider provideCallable
     */
    public function testItReturnsCallable($given, $expected)
    {
        $this->router->add('SendNewsletter', $given);

        $envelope = new Envelope(new DefaultMessage('SendNewsletter'));

        $this->assertEquals($expected, $this->router->map($envelope));
    }

    public function provideCallable()
    {
        $callable = function () {};

        return array(
            array('Bernard\Tests\Fixtures\Service', array('Bernard\Tests\Fixtures\Service', 'sendNewsletter')),
            array('var_dump', 'var_dump'),
            array($callable, $callable),
            array(new \stdClass, array(new \stdClass, 'sendNewsletter'))
        );
    }
}
