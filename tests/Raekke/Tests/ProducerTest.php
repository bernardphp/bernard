<?php

namespace Raekke\Tests;

use Raekke\Producer;
use Raekke\Message\Envelope;

class MessagePublisherTest extends \PHPUnit_Framework_TestCase
{
    public function testItImplementsProducerInterface()
    {
        $factory = $this->getMock('Raekke\QueueFactory\QueueFactoryInterface');

        $this->assertInstanceOf('Raekke\ProducerInterface', new Producer($factory));
    }

    public function testItSendsToTestsToQueue()
    {
        $message = $this->getMock('Raekke\Message\MessageInterface');
        $message->expects($this->once())->method('getQueue')->will($this->returnValue('my-queue'));

        $queue = $this->getMockBuilder('Raekke\Queue')->disableOriginalConstructor()
            ->getMock();
        $queue->expects($this->once())->method('enqueue')->with($this->equalTo(new Envelope($message)));

        $factory = $this->getMock('Raekke\QueueFactory\QueueFactoryInterface');
        $factory->expects($this->once())->method('create')->with($this->equalTo('my-queue'))
            ->will($this->returnValue($queue));

        $publisher = new Producer($factory);
        $publisher->produce($message);
    }
}
