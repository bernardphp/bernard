<?php

namespace Bernard\Tests\EventListener;

use Bernard\Event\EnvelopeEvent;
use Bernard\Event\RejectEnvelopeEvent;
use Bernard\EventListener\BatchSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcher;

class BatchSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->storage = $this->getMock('Bernard\Batch\Storage');

        $this->dispatcher = new EventDispatcher;
        $this->dispatcher->addSubscriber(new BatchSubscriber($this->storage));
    }

    public function testItRegistersOnProduce()
    {
        $this->storage->expects($this->once())->method('register')
            ->with('my-batch-name');

        $envelope = $this->getMockBuilder('Bernard\Envelope')
            ->disableOriginalConstructor()->getMock();
        $envelope->expects($this->any())->method('getStamp')->with('batch')
            ->will($this->returnValue('my-batch-name'));

        $event = new EnvelopeEvent($envelope, $this->getMock('Bernard\Queue'));

        $this->dispatcher->dispatch('bernard.produce', $event);
    }

    public function testItIncrementsOnReject()
    {
        $this->storage->expects($this->once())->method('increment')
            ->with('my-batch-name', 'failed');

        $envelope = $this->getMockBuilder('Bernard\Envelope')
            ->disableOriginalConstructor()->getMock();
        $envelope->expects($this->any())->method('getStamp')->with('batch')
            ->will($this->returnValue('my-batch-name'));

        $event = new RejectEnvelopeEvent($envelope, $this->getMock('Bernard\Queue'), new \Exception);

        $this->dispatcher->dispatch('bernard.reject', $event);
    }

    public function testItIncrementsOnAcknowledge()
    {
        $this->storage->expects($this->once())->method('increment')
            ->with('my-batch-name', 'successful');

        $envelope = $this->getMockBuilder('Bernard\Envelope')
            ->disableOriginalConstructor()->getMock();
        $envelope->expects($this->any())->method('getStamp')->with('batch')
            ->will($this->returnValue('my-batch-name'));

        $event = new EnvelopeEvent($envelope, $this->getMock('Bernard\Queue'));

        $this->dispatcher->dispatch('bernard.acknowledge', $event);
    }
}
