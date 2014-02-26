<?php

namespace Bernard\Tests\Queue;

use Bernard\Envelope;
use Bernard\Queue\PersistentQueue;

class PersistentQueueTest extends AbstractQueueTest
{
    public function setUp()
    {
        $this->driver = $this->getMock('Bernard\Driver');
        $this->encoder = $this->getMock('Bernard\Encoder');
    }

    public function testEnqueue()
    {
        $envelope = new Envelope($this->getMock('Bernard\Message'));

        $this->encoder->expects($this->once())->method('encode')->with($this->equalTo($envelope))
            ->will($this->returnValue('encoded message'));
        $this->driver->expects($this->once())->method('pushMessage')
            ->with($this->equalTo('send-newsletter'), $this->equalTo('encoded message'));

        $queue = $this->createQueue('send-newsletter');
        $queue->enqueue($envelope);
    }

    public function testAcknowledge()
    {
        $envelope = new Envelope($this->getMock('Bernard\Message'));

        $this->driver->expects($this->once())->method('acknowledgeMessage')
            ->with($this->equalTo('send-newsletter'), $this->equalTo('receipt'));

        $this->driver->expects($this->once())->method('popMessage')->with($this->equalTo('send-newsletter'))
            ->will($this->returnValue(array('message', 'receipt')));

        $this->encoder->expects($this->once())->method('decode')
            ->will($this->returnValue($envelope));

        $queue = $this->createQueue('send-newsletter');
        $envelope = $queue->dequeue();
        $queue->acknowledge($envelope);
    }

    public function testAcknowledgeOnlyIfReceipt()
    {
        $envelope = new Envelope($this->getMock('Bernard\Message'));

        $this->driver->expects($this->never())->method('acknowledgeMessage');

        $queue = $this->createQueue('send-newsletter');
        $queue->acknowledge($envelope);
    }

    public function testCount()
    {
        $this->driver->expects($this->once())->method('countMessages')->with($this->equalTo('send-newsletter'))
            ->will($this->returnValue(10));

        $queue = $this->createQueue('send-newsletter');

        $this->assertEquals(10, $queue->count());
    }

    public function testDequeue()
    {
        $messageWrapper = new Envelope($this->getMock('Bernard\Message'));

        $this->driver->expects($this->at(1))->method('popMessage')->with($this->equalTo('send-newsletter'))
            ->will($this->returnValue(array('encoded', null)));

        $this->driver->expects($this->at(2))->method('popMessage')->with($this->equalTo('send-newsletter'))
            ->will($this->returnValue(null));

        $this->encoder->expects($this->once())->method('decode')->with($this->equalTo('encoded'))
            ->will($this->returnValue($messageWrapper));

        $queue = $this->createQueue('send-newsletter');

        $this->assertSame($messageWrapper, $queue->dequeue());
        $this->assertInternalType('null', $queue->dequeue());
    }

    /**
     * @dataProvider peekDataProvider
     */
    public function testPeekDencodesMessages($index, $limit)
    {
        $this->encoder->expects($this->at(0))->method('decode')->with($this->equalTo('message1'));
        $this->encoder->expects($this->at(1))->method('decode')->with($this->equalTo('message2'));
        $this->encoder->expects($this->at(2))->method('decode')->with($this->equalTo('message3'));

        $this->driver->expects($this->once())->method('peekQueue')->with($this->equalTo('send-newsletter'), $this->equalTo($index), $this->equalTo($limit))
            ->will($this->returnValue(array('message1', 'message2', 'message3')));

        $queue = $this->createQueue('send-newsletter');
        $queue->peek($index, $limit);
    }

    public function dataClosedMethods()
    {
        $methods = parent::dataClosedMethods();
        $methods[] = array('register', array());

        return $methods;
    }

    public function peekDataProvider()
    {
        return array(
            array(0, 20),
            array(1, 10),
            array(20, 100),
        );
    }

    protected function createQueue($name)
    {
        return new PersistentQueue($name, $this->driver, $this->encoder);
    }
}
