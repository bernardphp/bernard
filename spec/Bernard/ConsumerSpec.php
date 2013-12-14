<?php

namespace spec\Bernard;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

require_once __DIR__ . '/Fixtures/ExampleReceiver.php';

class ConsumerSpec extends ObjectBehavior
{
    /**
     * @param Bernard\Router $router
     * @param Bernard\Middleware\MiddlewareBuilder $builder
     */
    function let($router, $builder)
    {
        $this->beConstructedWith($router, $builder);
    }

    function its_a_middleware()
    {
        $this->shouldHaveType('Bernard\Middleware');
    }

    /**
     * @param Bernard\Queue $queue
     * @param Bernard\Envelope $envelope
     * @param Bernard\Middleware $middleware
     */
    function it_creates_middleware_when_invoking($queue, $envelope, $middleware, $builder)
    {
        $builder->build($this)->willReturn($middleware);
        $middleware->call($envelope, $queue)->shouldBeCalled();

        $this->invoke($envelope, $queue);
    }

    /**
     * @param Bernard\Queue $queue
     * @param Bernard\Envelope $envelope
     * @param Bernard\Middleware $middleware
     */
    function it_does_not_crash_when_middleware_throws_exception($queue, $envelope, $middleware, $builder)
    {
        $builder->build($this)->willReturn($middleware);
        $middleware->call($envelope, $queue)->willThrow('InvalidArgumentException');

        $this->shouldNotThrow('InvalidArgumentException')->duringInvoke($envelope, $queue);
    }

    /**
     * @param Bernard\Envelope $envelope
     * @param Bernard\Message $message
     * @param Bernard\Queue $queue
     * @param spec\Bernard\Fixtures\ExampleReceiver $receiver
     */
    function it_routes_message_to_receiver($envelope, $message, $queue, $receiver, $router)
    {
        $receiver->import($message)->shouldBeCalled();
        $router->map($envelope)->willReturn(array($receiver, 'import'));
        $envelope->getMessage()->willReturn($message);

        $queue->acknowledge($envelope)->shouldBeCalled();

        $this->call($envelope, $queue);
    }
}
