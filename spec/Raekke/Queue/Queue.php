<?php

namespace spec\Raekke\Queue;

use PHPSpec2\ObjectBehavior;

class Queue extends ObjectBehavior
{
    /**
     * @param Raekke\QueueManager $manager
     */
    function let($manager)
    {
        $this->beConstructedWith('queueName', $manager);
    }

    function it_is_countable()
    {
        $this->shouldHaveType('Countable');
    }
}
