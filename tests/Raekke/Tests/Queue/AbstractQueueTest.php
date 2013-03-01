<?php

namespace Raekke\Tests\Queue;

abstract class AbstractQueueTest  extends \PHPUnit_Framework_TestCase
{
    public function testNameIsAccessible()
    {
        $this->assertEquals('send-newsletter', $this->createQueue('send-newsletter')->getName());
    }

    /**
     * @dataProvider dataClosedMethods
     */
    public function testNotAllowedWhenClosed($method, array $arguments = array())
    {
        $this->setExpectedException('Raekke\Exception\InvalidOperationexception', 'Queue "send-newsletter" is closed.');

        $queue = $this->createQueue('send-newsletter');
        $queue->close();

        call_user_func_array(array($queue, $method), $arguments);
    }

    public function dataClosedMethods()
    {
        return array(
            array('slice', array(0, 10)),
            array('count'),
            array('dequeue'),
            array('enqueue', array(
                $this->getMockBuilder('Raekke\Message\Envelope')->disableOriginalConstructor()->getMock()
            )),
        );
    }

    abstract protected function createQueue($name);
}
