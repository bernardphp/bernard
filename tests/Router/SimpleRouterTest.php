<?php

namespace Bernard\Tests;

use Bernard\Receiver;
use Bernard\Router\SimpleRouter;
use Bernard\Envelope;
use Bernard\Message\PlainMessage;

class SimpleRouterTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->router = new SimpleRouter();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionWhenReceiverIsNotSupported()
    {
        $this->router->add('SendNewsletter', 1);
    }

    /**
     * @expectedException \Bernard\Exception\ReceiverNotFoundException
     */
    public function testThrowsExceptionWhenNothingMatches()
    {
        $envelope = new Envelope(new PlainMessage('SendNewsletter'));

        $this->router->map($envelope);
    }

    public function testReceiversAreAddedThroughConstructor()
    {
        $receiver = $this->prophesize(Receiver::class)->reveal();
        $envelope = new Envelope(new PlainMessage('SendNewsletter'));

        $router = new SimpleRouter([
            'SendNewsletter' => $receiver,
        ]);

        $this->assertSame($receiver, $router->map($envelope));
    }

    /**
     * @dataProvider provideCallable
     */
    public function testItReturnsCallable($given, $expected)
    {
        $this->router->add('SendNewsletter', $given);

        $envelope = new Envelope(new PlainMessage('SendNewsletter'));

        $this->assertInstanceOf(Receiver::class, $this->router->map($envelope));
    }

    public function provideCallable()
    {
        $callable = function () {};

        return [
            ['var_dump', 'var_dump'],
            [$callable, $callable],
        ];
    }
}
