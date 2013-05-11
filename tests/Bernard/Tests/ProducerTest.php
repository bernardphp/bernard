<?php

namespace Bernard\Tests;

use Bernard\Producer;
use Bernard\Message\Envelope;

class ProducerTest extends \PHPUnit_Framework_TestCase
{
    public function testItDelegatesMessagesToQueue()
    {
        $message = $this->getMock('Bernard\Message');
        $message->expects($this->once())->method('getQueue')->will($this->returnValue('my-queue'));

        $queue = $this->getMock('Bernard\Queue');
        $queue->expects($this->once())->method('enqueue')->with($this->equalTo(new Envelope($message)));

        $factory = $this->getMock('Bernard\QueueFactory');
        $factory->expects($this->once())->method('create')->with($this->equalTo('my-queue'))
            ->will($this->returnValue($queue));

        $publisher = new Producer($factory);
        $publisher->produce($message);
    }
}
