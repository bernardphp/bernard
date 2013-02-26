<?php

namespace Raekke\Tests;

use Raekke\Producer;
use Raekke\Message\MessageWrapper;

class MessagePublisherTest extends \PHPUnit_Framework_TestCase
{
    public function testItImplementsProducerInterface()
    {
        $factory = $this->getMockBuilder('Raekke\QueueFactory')->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf('Raekke\ProducerInterface', new Producer($factory));
    }

    public function testItSendsToTestsToQueue()
    {
        $message = $this->getMock('Raekke\Message\MessageInterface');
        $message->expects($this->once())->method('getQueue')->will($this->returnValue('my-queue'));

        $queue = $this->getMockBuilder('Raekke\Queue\Queue')->disableOriginalConstructor()
            ->getMock();
        $queue->expects($this->once())->method('enqueue')->with($this->equalTo(new MessageWrapper($message)));

        $factory = $this->getMockBuilder('Raekke\QueueFactory')->disableOriginalConstructor()
            ->getMock();
        $factory->expects($this->once())->method('create')->with($this->equalTo('my-queue'))
            ->will($this->returnValue($queue));

        $publisher = new Producer($factory);
        $publisher->produce($message);
    }
}
