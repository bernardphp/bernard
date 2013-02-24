<?php

namespace Raekke\Tests;

use Raekke\MessagePublisher;
use Raekke\Message\MessageWrapper;

class MessagePublisherTest extends \PHPUnit_Framework_TestCase
{
    public function testItSendsToTestsToQueue()
    {
        $message = $this->getMock('Raekke\Message\MessageInterface');
        $message->expects($this->once())->method('getQueue')->will($this->returnValue('my-queue'));

        $queue = $this->getMockBuilder('Raekke\Queue\Queue')->disableOriginalConstructor()
            ->getMock();
        $queue->expects($this->once())->method('push')->with($this->equalTo(new MessageWrapper($message)));

        $manager = $this->getMockBuilder('Raekke\QueueFactory')->disableOriginalConstructor()
            ->getMock();
        $manager->expects($this->once())->method('create')->with($this->equalTo('my-queue'))
            ->will($this->returnValue($queue));

        $publisher = new MessagePublisher($manager);
        $publisher->send($message);
    }
}
