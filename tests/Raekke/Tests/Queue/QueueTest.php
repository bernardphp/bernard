<?php

namespace Raekke\Tests\Queue;

use Raekke\Message\MessageWrapper;
use Raekke\Queue\Queue;

class QueueTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->connection = $this->getMockBuilder('Raekke\Connection')->disableOriginalConstructor()->getMock();
        $this->serializer = $this->getMock('Raekke\Serializer\SerializerInterface');
    }

    public function testKeyIsPrefixedWithQueue()
    {
        $queue = new Queue('send-newsletter', $this->connection, $this->serializer);
        $this->assertEquals('queue:send-newsletter', $queue->getKey());
    }

    /**
     * @dataProvider dataClosedMethods
     */
    public function testNotAllowedWhenClosed($method, array $arguments = array())
    {
        $this->setExpectedException('Raekke\Exception\QueueClosedException');

        $queue = new Queue('send-newsletter', $this->connection, $this->serializer);
        $queue->close();

        call_user_func_array(array($queue, $method), $arguments);
    }

    public function dataClosedMethods()
    {
        return array(
            array('slice', array(0, 10)),
            array('register'),
            array('count'),
            array('enqueue', array(new MessageWrapper($this->getMock('Raekke\Message\MessageInterface')))),
        );
    }
}
