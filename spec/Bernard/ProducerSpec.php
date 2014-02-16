<?php

namespace spec\Bernard;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProducerSpec extends ObjectBehavior
{
    /**
     * @param Bernard\QueueFactory $factory
     * @param Bernard\Middleware\MiddlewareBuilder $builder
     */
    function let($factory, $builder)
    {
        $this->beConstructedWith($factory, $builder);
    }

    /**
     * @param Bernard\Envelope $envelope
     * @param Bernard\Queue $queue
     */
    function its_a_middleware_that_enqueues($envelope, $queue)
    {
        $this->shouldHaveType('Bernard\Middleware');

        $queue->enqueue($envelope)->shouldBeCalled();

        $this->call($envelope, $queue);
    }

    /**
     * @param Bernard\Message $message
     * @param Bernard\Queue $queue
     * @param Bernard\Middleware $middleware
     */
    function it_guesses_queue_name_from_message($message, $middleware, $queue, $factory, $builder)
    {
        $message->getName()->willReturn('Import');

        $factory->create('import')->willReturn($queue);
        $builder->build($this)->willReturn($middleware);

        $this->produce($message);
    }

    /**
     * @param Bernard\Message $message
     * @param Bernard\Queue $queue
     * @param Bernard\Middleware $middleware
     */
    function it_force_specific_queue($message, $middleware, $queue, $factory, $builder)
    {
        $message->getName()->willReturn('Import');

        $factory->create('anything-else')->willReturn($queue);
        $builder->build($this)->willReturn($middleware);

        $this->produce($message, 'anything-else');
    }
}
