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

        $factory = $this->getMock('Bernard\QueueFactory');
        $factory->expects($this->once())->method('create')->with($this->equalTo('my-queue'))
            ->will($this->returnValue($queue));

        $publisher = new Producer($factory);
        $publisher->produce($message);
    }

    public function testItLogMessages()
    {
        $message = $this->getMock('Bernard\Message');
        $message->expects($this->exactly(2))->method('getQueue')->will($this->returnValue('my-queue'));
        $message->expects($this->once())->method('getName')->will($this->returnValue('MyMessage'));

        $queue = $this->getMock('Bernard\Queue');

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger->expects($this->once())->method('info')->with(
            $this->equalTo('Enqueued message {name} to {queue}'),
            $this->equalTo(array('name' => 'MyMessage', 'queue' => 'my-queue', 'message' => $message))
        );

        $factory = $this->getMock('Bernard\QueueFactory');
        $factory->expects($this->once())->method('create')->with($this->equalTo('my-queue'))
            ->will($this->returnValue($queue));

        $publisher = new Producer($factory, $logger);
        $publisher->produce($message);
    }
}
