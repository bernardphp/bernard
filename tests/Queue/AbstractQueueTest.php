<?php

namespace Bernard\Tests\Queue;

use Bernard\Envelope;

abstract class AbstractQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataClosedMethods
     */
    public function testNotAllowedWhenClosed($method, array $arguments = array())
    {
        $this->setExpectedException('Bernard\Exception\InvalidOperationexception', 'Queue "send-newsletter" is closed.');

        $queue = $this->createQueue('send-newsletter');
        $queue->close();

        call_user_func_array(array($queue, $method), $arguments);
    }

    public function testNameAsToString()
    {
        $queue = $this->createQueue('long-name');

        $this->assertEquals('long-name', (string) $queue);
        $this->assertEquals('long-name', $queue);
    }

    public function dataClosedMethods()
    {
        return array(
            array('peek', array(0, 10)),
            array('count'),
            array('dequeue'),
            array('enqueue', array(
                new Envelope($this->getMock('Bernard\Message'))
            )),
            array('acknowledge', array(
                new Envelope($this->getMock('Bernard\Message'))
            )),
        );
    }

    abstract protected function createQueue($name);
}
