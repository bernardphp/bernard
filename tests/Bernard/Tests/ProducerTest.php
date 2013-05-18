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

        $envelope = new Envelope($message);

        $queue = $this->getMock('Bernard\Queue');
        $queue->expects($this->once())->method('enqueue')->with($this->equalTo($envelope));

        $factory = $this->getMock('Bernard\Broker');
        $factory->expects($this->once())->method('create')->with($this->equalTo('my-queue'))
            ->will($this->returnValue($queue));

        $publisher = new Producer($factory);
        $publisher->produce($message);
    }
}
